<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create Admin User
        $admin = User::create([
            'name' => 'VidaNexus Admin',
            'email' => 'admin@vidanexus.ai',
            'password' => \Illuminate\Support\Facades\Hash::make('admin_secret_2026'),
            'role' => 'admin',
            'subscription_tier' => 'agency',
        ]);

        // Create Admin Wallet
        \App\Models\Wallet::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $admin->id,
            'balance_credits' => 1000000.00,
        ]);
    }
}
