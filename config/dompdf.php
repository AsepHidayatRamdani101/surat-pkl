<?php

return [
    'public_path' => env('DOMPDF_PUBLIC_PATH', public_path()),
    'convert_entities' => true,
    'callbacks' => [
        'resolve_relative_path' => null,
        'find_relative_file' => null,
    ],
    'pdf_backend' => 'CPDF',
    'php_memory_limit' => '512M',
    'enable_javascript' => false,
    'enable_remote' => true,
    'font_subsetting' => true,
    'icc_profile_path' => env('DOMPDF_TEMP_DIR', storage_path('app')),
];
