@php
    // Thresholds: green < 1 MB, yellow < 10 MB, red >= 10 MB
    $mb = $size / 1048576;
    $pct = min(100, round(($mb / 10) * 100));
    $color = $mb < 1 ? 'success' : ($mb < 10 ? 'warning' : 'danger');
@endphp
<div class="progress" style="height: 8px;" title="{{ fmtSize($size) }}">
    <div class="progress-bar bg-{{ $color }}" style="width: {{ $pct }}%"></div>
</div>
<small class="text-muted">{{ fmtSize($size) }} / 10 MB referensi</small>
