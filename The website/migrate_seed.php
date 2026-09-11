<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->call('migrate', ['--force' => true]);
echo "Migrate exit code: " . $status . "\n";
echo $kernel->output() . "\n";

$status2 = $kernel->call('db:seed', ['--force' => true]);
echo "Seed exit code: " . $status2 . "\n";
echo $kernel->output() . "\n";
