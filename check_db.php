<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SQLite Database Viewer ===\n\n";

// Get all tables
$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

echo "Tables in database:\n";
foreach ($tables as $table) {
    echo "  - {$table->name}\n";
}

echo "\n=== Table Contents ===\n\n";

foreach ($tables as $table) {
    $tableName = $table->name;
    $count = DB::table($tableName)->count();
    
    echo "Table: {$tableName} ({$count} rows)\n";
    echo str_repeat("-", 50) . "\n";
    
    if ($count > 0) {
        $rows = DB::table($tableName)->limit(10)->get();
        foreach ($rows as $row) {
            echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
        }
        if ($count > 10) {
            echo "... and " . ($count - 10) . " more rows\n";
        }
    } else {
        echo "(empty)\n";
    }
    echo "\n";
}

