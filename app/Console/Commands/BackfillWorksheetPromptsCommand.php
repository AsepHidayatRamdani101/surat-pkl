<?php

namespace App\Console\Commands;

use App\Models\TugasPembekalan;
use App\Support\WorksheetPromptExtractor;
use Illuminate\Console\Command;

class BackfillWorksheetPromptsCommand extends Command
{
    protected $signature = 'worksheet:backfill-prompts {--chunk=50 : Number of rows processed per chunk}';

    protected $description = 'Backfill parsed worksheet prompts from uploaded soal files and essay fallback.';

    public function handle(WorksheetPromptExtractor $extractor): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $updated = 0;

        TugasPembekalan::query()
            ->orderBy('id')
            ->chunk($chunkSize, function ($rows) use ($extractor, &$updated) {
                foreach ($rows as $tugas) {
                    $prompts = $extractor->extractFromTaskSources(
                        (array) ($tugas->soal_files ?? []),
                        (array) ($tugas->soal_essay ?? [])
                    );

                    $tugas->soal_parsed_prompts = !empty($prompts) ? $prompts : null;
                    $tugas->soal_parsed_at = !empty($prompts) ? now() : null;
                    $tugas->save();

                    $updated++;
                }
            });

        $this->info("Worksheet prompts updated for {$updated} tugas.");
        return self::SUCCESS;
    }
}
