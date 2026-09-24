<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\RejectBlockedUsers;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RejectBlockedUsersTest extends TestCase
{
    public function test_blocked_authenticated_users_receive_a_clear_response(): void
    {
        $user = (new User())->forceFill(['is_blocked' => true]);
        $request = Request::create('/api/protected', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new RejectBlockedUsers())->handle($request, fn () => new Response('allowed'));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('ACCOUNT_BLOCKED', $response->getData(true)['code']);
    }

    public function test_active_authenticated_users_continue_to_the_protected_route(): void
    {
        $user = (new User())->forceFill(['is_blocked' => false]);
        $request = Request::create('/api/protected', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = (new RejectBlockedUsers())->handle($request, fn () => new Response('allowed'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('allowed', $response->getContent());
    }
}
