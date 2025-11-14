<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@admin.com')->first();

if ($user) {
    $user->role = 'admin';
    $user->save();
    
    echo "✅ Admin role updated successfully!\n";
    echo "Email: {$user->email}\n";
    echo "Role: {$user->role}\n";
    echo "Name: {$user->name}\n";
} else {
    echo "❌ User not found!\n";
    echo "Creating admin user...\n";
    
    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin@admin.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
        'aadhar_number' => '123456789012',
        'pan_number' => 'ABCDE1234F',
        'phone_number' => '1234567890',
        'age' => 30,
    ]);
    
    echo "✅ Admin user created!\n";
    echo "Email: {$user->email}\n";
    echo "Password: password\n";
    echo "Role: {$user->role}\n";
}

