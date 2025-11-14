<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@admin.com')->first();

if ($user) {
    $user->password = Hash::make('password');
    $user->save();
    
    echo "✅ Password reset successfully!\n";
    echo "Email: {$user->email}\n";
    echo "Password: password\n";
    echo "Role: {$user->role}\n";
} else {
    echo "❌ User not found!\n";
}

