<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GrantSuperAdminToAdminUserSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'admin@vidanexus.ai';

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $user = User::query()->where('email', self::ADMIN_EMAIL)->first();

        if (! $user) {
            if ($this->command) {
                $this->command->warn('No user found with email '.self::ADMIN_EMAIL.' — skip super_admin assignment.');
            }

            return;
        }

        Role::findOrCreate('super_admin', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user->syncRoles(['super_admin']);

        if ($user->role !== 'admin') {
            $user->forceFill(['role' => 'admin'])->save();
        }

        if ($this->command) {
            $this->command->info('Assigned role super_admin to '.self::ADMIN_EMAIL.'.');
        }
    }
}
