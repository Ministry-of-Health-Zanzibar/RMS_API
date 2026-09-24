<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Mockery;
use Tests\TestCase;

class SensitiveAdminAccessTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function deniedUser(): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('hasAnyRole')->andReturn(false);
        $user->shouldReceive('can')->andReturn(false);
        $user->forceFill(['is_blocked' => false]);

        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_audit_logs_are_not_available_to_an_ordinary_user(): void
    {
        $this->deniedUser();

        $this->getJson('/api/audit-logs')->assertForbidden();
    }

    public function test_workflow_undo_requires_the_sensitive_admin_permission(): void
    {
        $this->deniedUser();

        $this->getJson('/api/patient-histories/10/workflow-events')->assertForbidden();
        $this->postJson('/api/patient-history-workflow-events/10/undo', [
            'reason' => 'Incorrect workflow transition',
        ])->assertForbidden();
    }

    public function test_user_blocking_is_not_available_to_an_ordinary_user(): void
    {
        $this->deniedUser();

        $this->deleteJson('/api/userAccounts/10')->assertUnauthorized();
        $this->getJson('/api/unBlockUser/10')->assertUnauthorized();
    }
}
