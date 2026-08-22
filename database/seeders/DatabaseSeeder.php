<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // User::factory(10)->create();

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@vidanexus.ai'],
            [
                'name' => 'VidaNexus Admin',
                'password' => Hash::make('admin_secret_2026'),
                'role' => 'admin',
                'subscription_tier' => 'agency',
            ]
        );

        // Create Admin Wallet
        if (! $admin->wallet()->exists()) {
            Wallet::create([
                'id' => (string) Str::uuid(),
                'user_id' => $admin->id,
                'balance_credits' => 1000000.00,
            ]);
        }

        if (! $admin->hasAnyRole(['admin', 'super_admin'])) {
            $admin->assignRole('admin');
        }

        $this->call(GrantSuperAdminToAdminUserSeeder::class);
    }
}
