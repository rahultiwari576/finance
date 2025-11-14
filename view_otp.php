<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Otp;
use Carbon\Carbon;

echo "=== Recent OTPs ===\n\n";

$otps = Otp::with('user')
    ->whereNull('verified_at')
    ->where('expires_at', '>', Carbon::now())
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($otps->isEmpty()) {
    echo "No active OTPs found.\n";
    echo "\nTo generate an OTP:\n";
    echo "1. Go to login page\n";
    echo "2. Use Aadhar login\n";
    echo "3. Check this script again or check storage/logs/laravel.log\n";
} else {
    foreach ($otps as $otp) {
        echo "User: {$otp->user->name} ({$otp->user->email})\n";
        echo "OTP Code: {$otp->code}\n";
        echo "Expires: {$otp->expires_at->format('Y-m-d H:i:s')}\n";
        echo "Created: {$otp->created_at->format('Y-m-d H:i:s')}\n";
        echo str_repeat("-", 50) . "\n\n";
    }
}

echo "\n=== All Recent OTPs (Last 10) ===\n\n";

$allOtps = Otp::with('user')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

foreach ($allOtps as $otp) {
    $status = $otp->verified_at ? '✅ Verified' : ($otp->expires_at->isPast() ? '❌ Expired' : '⏳ Active');
    echo "[{$status}] {$otp->user->email} - OTP: {$otp->code} - Created: {$otp->created_at->format('H:i:s')}\n";
}

