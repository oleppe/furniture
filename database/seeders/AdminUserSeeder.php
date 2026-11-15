<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ap.sy.uk@gmail.com'],
            [
                'name' => 'Main Administrator',
                'password' => Hash::make('Ff123123@'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
