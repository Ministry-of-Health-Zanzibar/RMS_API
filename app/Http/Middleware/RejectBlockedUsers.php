<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectBlockedUsers
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->is_blocked || $user->trashed())) {
            return response()->json([
                'message' => 'This account is blocked. Contact an administrator.',
                'code' => 'ACCOUNT_BLOCKED',
                'statusCode' => 403,
            ], 403);
        }

        return $next($request);
    }
}
