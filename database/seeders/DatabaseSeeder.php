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
        // Seed roles + permissions first

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
        ]);
        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@speakly.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'), // hashed even if User casts are not set
                'email_verified_at' => now(),
            ]
        );

        // Ensure admin role is set (idempotent)
        $admin->syncRoles(['admin']);
    }
}
