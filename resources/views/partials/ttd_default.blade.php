@php
    $ttdTanggal = $ttdTanggal ?? now()->translatedFormat('d F Y');
    $ttdLabel = $ttdLabel ?? 'Kepala Sekolah';
    $ttdNama = $ttdNama ?? 'Kepala Sekolah';
    $ttdNip = $ttdNip ?? '-';
    $ttdImage = $ttdImage ?? null;
    $ttdAlign = $ttdAlign ?? 'right';
    $ttdContainerStyle = $ttdContainerStyle ?? 'margin-top: 10px;';

    if (!empty($ttdImage) && !str_starts_with((string) $ttdImage, 'data:image')) {
        $resolvedTtdPath = null;

        if (str_starts_with((string) $ttdImage, 'file:///')) {
            $resolvedTtdPath = preg_replace('/^file:\/\//', '', (string) $ttdImage);
        } elseif (str_starts_with((string) $ttdImage, 'http://') || str_starts_with((string) $ttdImage, 'https://')) {
            $resolvedTtdPath = null;
        } else {
            $relativePath = ltrim((string) $ttdImage, '/');

            $candidatePaths = [
                public_path($relativePath),
                storage_path('app/public/' . preg_replace('/^storage\//', '', $relativePath)),
                storage_path('app/' . $relativePath),
            ];

            foreach ($candidatePaths as $candidatePath) {
                if (file_exists($candidatePath)) {
                    $resolvedTtdPath = $candidatePath;
                    break;
                }
            }
        }

        if (!empty($resolvedTtdPath) && file_exists($resolvedTtdPath)) {
            $extension = strtolower(pathinfo($resolvedTtdPath, PATHINFO_EXTENSION));
            $mimeMap = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
            ];

            $mimeType = $mimeMap[$extension] ?? 'image/png';
            $ttdImage = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($resolvedTtdPath));
        }
    }
@endphp

<div style="{{ $ttdContainerStyle }} text-align: {{ $ttdAlign }};">
    <div>{{ $ttdTanggal }}</div>
    <div>{{ $ttdLabel }},</div>
    <div style="height: 130px; margin: -30px 0px -26px -30px ; position: relative; z-index: 2;">
        @if (!empty($ttdImage))
            <img src="{{ $ttdImage }}" alt="ttd" style="max-height: 130px; max-width: 320px;">
        @endif
    </div>
    <div style="font-weight: bold; text-decoration: underline;">{{ $ttdNama }}</div>
    <div>NIP. {{ $ttdNip }}</div>
</div>
