<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private const PERMISSIONS = [
        'View Audit Logs',
        'Undo Patient History Workflow',
        'Block User',
        'Unblock User',
    ];

    public function up(): void
    {
        $permissions = collect(self::PERMISSIONS)->mapWithKeys(function (string $name): array {
            return [$name => Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ])];
        });

        Role::query()
            ->whereIn('name', ['ROLE ADMIN', 'ROLE SUPER ADMIN', 'ROLE SUPERADMIN'])
            ->get()
            ->each(function (Role $role) use ($permissions): void {
                $role->givePermissionTo($permissions->values()->all());
            });
    }

    public function down(): void
    {
        Permission::query()
            ->whereIn('name', self::PERMISSIONS)
            ->where('guard_name', 'web')
            ->get()
            ->each(fn (Permission $permission) => $permission->delete());
    }
};
