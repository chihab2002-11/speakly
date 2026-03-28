<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = ['admin', 'teacher', 'student', 'parent', 'secretary'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Attach permissions to roles
        Role::findByName('admin', 'web')->givePermissionTo([
            'approve.staff',
            'approve.users',
        ]);

        Role::findByName('secretary', 'web')->givePermissionTo([
            'approve.users',
        ]);
    }
}
