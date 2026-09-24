<?php

namespace App\Support;

use App\Models\User;

final class SuperAdminAccess
{
    private const ROLE_NAMES = [
        'ROLE ADMIN',
        // Legacy national administrator role with system-wide permissions.
        'ROLE NATIONAL',
        'ROLE SUPER ADMIN',
        'ROLE SUPERADMIN',
    ];

    private function __construct()
    {
    }

    public static function allowed(User $user, ?string $permission = null): bool
    {
        if ($user->hasAnyRole(self::ROLE_NAMES)) {
            return true;
        }

        return $permission !== null && $user->can($permission);
    }
}
