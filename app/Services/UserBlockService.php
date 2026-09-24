<?php

namespace App\Services;

use App\Models\User;
use App\Support\AuditService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserBlockService
{
    public function block(int $targetId, User $actor, ?string $reason = null): User
    {
        return DB::transaction(function () use ($targetId, $actor, $reason): User {
            $target = User::withTrashed()->lockForUpdate()->find($targetId);
            if (! $target) {
                throw new RuntimeException('User not found.');
            }

            $this->assertCanChange($target, $actor);
            $oldValues = [
                'is_blocked' => (bool) $target->is_blocked,
                'deleted_at' => $target->deleted_at,
            ];

            $target->forceFill([
                'is_blocked' => true,
                'blocked_at' => now(),
                'blocked_by' => $actor->id,
                'blocked_reason' => $reason,
            ])->saveQuietly();

            $target->tokens()->delete();

            AuditService::record(
                'user_blocked',
                'users',
                $target,
                $oldValues,
                [
                    'is_blocked' => true,
                    'blocked_at' => $target->blocked_at,
                    'blocked_by' => $actor->id,
                    'blocked_reason' => $reason,
                ],
                'User account blocked',
                ['user_id' => $target->id, 'reason' => $reason],
            );

            return $target->fresh();
        });
    }

    public function unblock(int $targetId, User $actor): User
    {
        return DB::transaction(function () use ($targetId, $actor): User {
            $target = User::withTrashed()->lockForUpdate()->find($targetId);
            if (! $target) {
                throw new RuntimeException('User not found.');
            }

            $this->assertCanChange($target, $actor, false);
            $oldValues = [
                'is_blocked' => (bool) $target->is_blocked,
                'deleted_at' => $target->deleted_at,
                'blocked_reason' => $target->blocked_reason,
            ];

            $target->forceFill([
                'is_blocked' => false,
                'blocked_at' => null,
                'blocked_by' => null,
                'blocked_reason' => null,
            ])->saveQuietly();

            if ($target->trashed()) {
                $target->restore();
            }

            AuditService::record(
                'user_unblocked',
                'users',
                $target,
                $oldValues,
                ['is_blocked' => false, 'deleted_at' => null],
                'User account unblocked',
                ['user_id' => $target->id],
            );

            return $target->fresh();
        });
    }

    private function assertCanChange(User $target, User $actor, bool $blocking = true): void
    {
        if ($target->id === $actor->id) {
            throw new RuntimeException('You cannot block or unblock your own account.');
        }

        if (! $blocking || ! $target->hasAnyRole(['ROLE ADMIN', 'ROLE SUPER ADMIN', 'ROLE SUPERADMIN'])) {
            return;
        }

        $activeSuperAdmins = User::query()
            ->where('is_blocked', false)
            ->whereNull('deleted_at')
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', ['ROLE ADMIN', 'ROLE SUPER ADMIN', 'ROLE SUPERADMIN']);
            })
            ->count();

        if ($activeSuperAdmins <= 1) {
            throw new RuntimeException('The last active Super Admin cannot be blocked.');
        }
    }
}
