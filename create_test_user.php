<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Test User ===\n\n";

$user = User::firstOrCreate(
    ['email' => 'user@test.com'],
    [
        'name' => 'Test User',
        'email' => 'user@test.com',
        'password' => Hash::make('password'),
        'role' => 'user',
        'aadhar_number' => '987654321098',
        'pan_number' => 'TESTE1234T',
        'phone_number' => '9876543210',
        'age' => 25,
    ]
);

if ($user->wasRecentlyCreated) {
    echo "✅ Test user created successfully!\n\n";
} else {
    echo "ℹ️  Test user already exists (updating password and role)...\n\n";
    $user->password = Hash::make('password');
    $user->role = 'user';
    $user->save();
}

echo "=== Test User Credentials ===\n";
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Password: password\n";
echo "Role: {$user->role}\n";
echo "Aadhar: {$user->aadhar_number}\n";
echo "PAN: {$user->pan_number}\n";
echo "Phone: {$user->phone_number}\n";
echo "Age: {$user->age}\n";
echo "\n=== Login Methods ===\n";
echo "1. Email & Password:\n";
echo "   Email: {$user->email}\n";
echo "   Password: password\n\n";
echo "2. Aadhar & OTP:\n";
echo "   Aadhar: {$user->aadhar_number}\n";
echo "   (OTP will be sent to: {$user->email})\n";
echo "   (Run 'php view_otp.php' to see the OTP code)\n";

