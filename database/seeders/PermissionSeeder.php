<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * @return list<string>
     */
    public static function permissions(): array
    {
        return [
            'approvals.approve.standard',
            'approvals.reject.standard',
            'approvals.approve.office',
            'approvals.reject.office',
            'language-programs.manage',
            'employees.manage',
            'courses.manage',
            'classrooms.manage',
            'schedules.manage',
            'timetables.explore',
            'registrations.manage',
            'payments.manage',
            'groups.manage',
            'accounts.manage',
            'announcements.publish',
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::permissions() as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }
    }
}
