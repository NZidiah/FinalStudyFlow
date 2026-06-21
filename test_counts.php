<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "FocusSessions Count: " . \App\Models\FocusSession::count() . "\n";
echo "Tasks Count: " . \App\Models\Task::count() . "\n";
echo "Focus Sessions raw data:\n";
print_r(\App\Models\FocusSession::latest()->limit(5)->get()->toArray());
