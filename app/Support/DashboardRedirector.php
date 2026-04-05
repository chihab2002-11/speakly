<?php

namespace App\Support;

use App\Models\User;

class DashboardRedirector
{
    public static function roleFor(User $user): string
    {
        return match (true) {
            $user->hasRole('admin') => 'admin',
            $user->hasRole('secretary') => 'secretary',
            $user->hasRole('teacher') => 'teacher',
            $user->hasRole('parent') => 'parent',
            $user->hasRole('student') => 'student',
            default => 'student',
        };
    }

    public static function routeNameFor(User $user): string
    {
        return 'role.dashboard';
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    public static function routeParametersFor(User $user, array $parameters = []): array
    {
        return ['role' => self::roleFor($user), ...$parameters];
    }
}
