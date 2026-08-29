<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\MonitoringController;

$publicDir = __DIR__ . '/../storage/app/public';
$before = array_map('basename', glob($publicDir . '/*'));

$controller = new MonitoringController();

try {
    $resp = $controller->exportExcel();
    echo "Export invoked.\n";
} catch (Throwable $e) {
    echo "Error invoking export: " . $e->getMessage() . "\n";
    exit(1);
}

$after = array_map('basename', glob($publicDir . '/*'));

$new = array_values(array_diff($after, $before));

if (!empty($new)) {
    echo "New files created:\n";
    foreach ($new as $f) {
        echo "- " . $f . "\n";
    }
} else {
    echo "No new file detected in storage/app/public.\n";
}
