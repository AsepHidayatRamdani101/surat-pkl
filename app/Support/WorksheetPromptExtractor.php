<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class WorksheetPromptExtractor
{
    private ?bool $tesseractAvailable = null;

    public function extractFromTaskSources(array $soalFiles = [], array $fallbackEssay = []): array
    {
        $fromFiles = $this->extractFromFiles($soalFiles);
        if (!empty($fromFiles)) {
            return $fromFiles;
        }

        return $this->normalizePrompts($fallbackEssay);
    }

    public function extractFromFiles(array $soalFiles): array
    {
        $prompts = [];

        foreach ($soalFiles as $path) {
            $path = trim((string) $path);
            if ($path === '') {
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($extension === 'pdf') {
                $prompts = array_merge($prompts, $this->extractPromptsFromPdf($path));
                continue;
            }

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $prompts = array_merge($prompts, $this->extractPromptsFromImage($path));
            }
        }

        return $this->normalizePrompts($prompts);
    }

    private function extractPromptsFromPdf(string $path): array
    {
        try {
            $absolutePath = Storage::disk('public')->path($path);
            if (!is_file($absolutePath)) {
                return [];
            }

            $pdf = app(PdfParser::class)->parseFile($absolutePath);
            $text = (string) ($pdf->getText() ?? '');
            return $this->extractPromptsFromRawText($text);
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    private function extractPromptsFromImage(string $path): array
    {
        if (!$this->isTesseractAvailable()) {
            return [];
        }

        try {
            $absolutePath = Storage::disk('public')->path($path);
            if (!is_file($absolutePath)) {
                return [];
            }

            $tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . bin2hex(random_bytes(8));
            $tmpTextFile = $tmpBase . '.txt';
            $lang = env('TESSERACT_LANG', 'ind+eng');

            $cmd = sprintf(
                'tesseract %s %s -l %s --psm 6 quiet 2>&1',
                escapeshellarg($absolutePath),
                escapeshellarg($tmpBase),
                escapeshellarg($lang)
            );

            exec($cmd, $output, $exitCode);
            if ($exitCode !== 0 || !is_file($tmpTextFile)) {
                return [];
            }

            $text = (string) file_get_contents($tmpTextFile);
            @unlink($tmpTextFile);

            return $this->extractPromptsFromRawText($text);
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    private function isTesseractAvailable(): bool
    {
        if ($this->tesseractAvailable !== null) {
            return $this->tesseractAvailable;
        }

        $checkCommand = PHP_OS_FAMILY === 'Windows' ? 'where tesseract' : 'command -v tesseract';
        exec($checkCommand . ' 2>&1', $output, $exitCode);

        $this->tesseractAvailable = $exitCode === 0;
        return $this->tesseractAvailable;
    }

    private function extractPromptsFromRawText(string $text): array
    {
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', ' ', $text) ?? '';
        $text = preg_replace('/\R/u', "\n", $text) ?? '';
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? '';

        $lines = collect(explode("\n", $text))
            ->map(fn($line) => trim((string) $line))
            ->filter(fn($line) => $line !== '' && mb_strlen($line) >= 4)
            ->values();

        if ($lines->isEmpty()) {
            return [];
        }

        $prompts = [];
        $current = '';

        foreach ($lines as $line) {
            if ($this->shouldIgnoreLine($line)) {
                continue;
            }

            if ($this->isPromptStart($line)) {
                if ($current !== '') {
                    $prompts[] = $current;
                }

                $current = $this->stripPromptMarker($line);
                continue;
            }

            if ($current !== '') {
                if ($this->isContinuationLine($line)) {
                    $current .= ' ' . $line;
                    continue;
                }

                $prompts[] = $current;
                $current = '';
            }

            if ($this->looksLikePromptContent($line)) {
                $current = $line;
            }
        }

        if ($current !== '') {
            $prompts[] = $current;
        }

        if (empty($prompts)) {
            $prompts = $lines
                ->filter(fn($line) => $this->looksLikePromptContent($line))
                ->take(12)
                ->values()
                ->all();
        }

        if (empty($prompts)) {
            $prompts = $lines->take(8)->all();
        }

        return $this->normalizePrompts($prompts);
    }

    private function normalizePrompts(array $prompts): array
    {
        $seen = [];
        $result = [];

        foreach ($prompts as $prompt) {
            $clean = trim((string) $prompt);
            $clean = preg_replace('/\s+/u', ' ', $clean) ?? $clean;
            $clean = preg_replace('/^([\-•*]+\s*)+/u', '', $clean) ?? $clean;

            if ($clean === '' || mb_strlen($clean) < 4) {
                continue;
            }

            $dedupeKey = mb_strtolower(preg_replace('/[^\pL\pN]+/u', '', $clean) ?? $clean);
            if ($dedupeKey === '' || isset($seen[$dedupeKey])) {
                continue;
            }

            $seen[$dedupeKey] = true;
            $result[] = $clean;

            if (count($result) >= 15) {
                break;
            }
        }

        return $result;
    }

    private function shouldIgnoreLine(string $line): bool
    {
        if (mb_strlen($line) < 4) {
            return true;
        }

        return preg_match('/^(halaman|page)\s+\d+|^(nama|kelas|nis|tanggal)\s*:\s*$/iu', $line) === 1;
    }

    private function isPromptStart(string $line): bool
    {
        return preg_match('/^(?:(?:soal|pertanyaan)\s*)?(?:\d{1,3}|[ivxlcdm]{1,8}|[A-Z])[\.)]\s+/iu', $line) === 1
            || preg_match('/^[-*•]\s+/u', $line) === 1;
    }

    private function stripPromptMarker(string $line): string
    {
        $stripped = preg_replace('/^(?:(?:soal|pertanyaan)\s*)?(?:\d{1,3}|[ivxlcdm]{1,8}|[A-Z])[\.)]\s+/iu', '', $line) ?? $line;
        $stripped = preg_replace('/^[-*•]\s+/u', '', $stripped) ?? $stripped;
        return trim($stripped);
    }

    private function isContinuationLine(string $line): bool
    {
        if ($this->isPromptStart($line)) {
            return false;
        }

        if (preg_match('/^[A-Z\s]{6,}$/u', $line) === 1) {
            return false;
        }

        return true;
    }

    private function looksLikePromptContent(string $line): bool
    {
        if (preg_match('/\?$/u', $line) === 1) {
            return true;
        }

        return preg_match('/\b(jelaskan|uraikan|sebutkan|buatlah|lengkapi|isi|tuliskan|kerjakan|identitas|deklara|analisis)\b/iu', $line) === 1;
    }
}
