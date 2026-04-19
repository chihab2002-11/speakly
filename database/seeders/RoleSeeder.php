<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'teacher', 'student', 'parent', 'secretary'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolePermissions = [
            'admin' => PermissionSeeder::permissions(),
            'secretary' => [
                'approvals.approve.standard',
                'approvals.reject.standard',
                'timetables.explore',
                'registrations.manage',
                'payments.manage',
                'groups.manage',
                'accounts.manage',
                'announcements.publish',
            ],
            'teacher' => [],
            'student' => [],
            'parent' => [],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            Role::findByName($roleName, 'web')->syncPermissions($permissions);
        }
    }
}
