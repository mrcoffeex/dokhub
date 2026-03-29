<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$d = App\Models\Doctor::find(3);
if (! $d) {
    echo "Doctor id=3 not found\n";
    exit(1);
}

$d->update(['insurance' => ['PhilHealth']]);
$refreshed = $d->fresh();
echo "updated: " . json_encode($refreshed->insurance) . "\n";
