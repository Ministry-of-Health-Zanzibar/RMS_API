<?php

namespace Tests\Unit\Support;

use App\Support\AuditService;
use PHPUnit\Framework\TestCase;

class AuditServiceTest extends TestCase
{
    public function test_sensitive_values_are_removed_recursively(): void
    {
        $result = AuditService::scrub([
            'email' => 'person@example.com',
            'password' => 'do-not-store',
            'nested' => [
                'access_token' => 'do-not-store',
                'safe_value' => 'kept',
            ],
        ]);

        $this->assertSame([
            'email' => 'person@example.com',
            'nested' => [
                'safe_value' => 'kept',
            ],
        ], $result);
    }
}
