<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view_ledger',
            'manage_coupons',
            'use_ai_tool',
            'manage_payments',
            'process_payment_webhooks',
            'view_system_health',
            'manage_users',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        Role::findOrCreate('user', 'web');

        $superAdmin->syncPermissions(Permission::all());
        $admin->syncPermissions([
            'view_ledger',
            'manage_coupons',
            'use_ai_tool',
            'manage_payments',
            'process_payment_webhooks',
            'view_system_health',
            'manage_users',
        ]);

        $adminEmails = ['admin@vidanexus.ai', 'm.ali@vidanexus.net', 'ahmed@vidanexus.ai', 'mohamedibrahemsaid@gmail.com'];
        User::query()->whereIn('email', $adminEmails)->orWhere('role', 'admin')->get()->each(function (User $user): void {
            $user->role = 'admin';
            $user->save();
            $user->assignRole('super_admin');
        });
    }
}
