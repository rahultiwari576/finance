<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'aadhar_number' => '123456789012',
                'pan_number' => 'ABCDE1234F',
                'phone_number' => '1234567890',
                'age' => 30,
            ]
        );

        $this->command->info('Admin user created!');
        $this->command->info('Email: admin@admin.com');
        $this->command->info('Password: password');
    }
}
