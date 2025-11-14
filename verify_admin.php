<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@admin.com')->first();

if ($user) {
    echo "=== Admin User Verification ===\n\n";
    echo "✅ User Found!\n";
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "Is Admin: " . ($user->isAdmin() ? 'Yes ✅' : 'No ❌') . "\n";
    
    // Test password
    $passwordTest = Hash::check('password', $user->password);
    echo "Password (password): " . ($passwordTest ? 'Correct ✅' : 'Incorrect ❌') . "\n";
    
    echo "\n=== Login Credentials ===\n";
    echo "Email: admin@admin.com\n";
    echo "Password: password\n";
    echo "Role: {$user->role}\n";
} else {
    echo "❌ User not found!\n";
}

