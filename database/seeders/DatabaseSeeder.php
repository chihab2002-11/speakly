<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@speakly.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $admin->forceFill([
            'approved_at' => now(),
            'approved_by' => null,
            'requested_role' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ])->save();

        $admin->syncRoles(['admin']);

        if (app()->environment(['local', 'development'])) {
            $this->call([
                TeacherWorkflowSeeder::class,
                LanguageProgramSeeder::class,
            ]);
        }
    }
}
