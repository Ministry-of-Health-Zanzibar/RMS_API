<?php

namespace App\Http\Controllers\API\Letters;

use App\Http\Controllers\Controller;
use App\Services\Letters\LetterBrandingService;
use Illuminate\Http\Request;

class LetterBrandingController extends Controller
{
    public function __construct(private readonly LetterBrandingService $branding)
    {
        $this->middleware('auth:sanctum');
    }

    public function show()
    {
        if (!$this->canManage()) {
            return $this->forbidden();
        }

        return response()->json([
            'data' => $this->branding->summary(),
            'statusCode' => 200,
        ]);
    }

    public function update(Request $request)
    {
        if (!$this->canManage()) {
            return $this->forbidden();
        }

        $request->validate([
            'signature' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'stamp' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ]);

        if (!$request->hasFile('signature') && !$request->hasFile('stamp')) {
            return response()->json([
                'message' => 'Choose a signature or stamp image before saving.',
                'statusCode' => 422,
            ], 422);
        }

        return response()->json([
            'message' => 'Letter signature and stamp settings updated successfully.',
            'data' => $this->branding->update(
                $request->file('signature'),
                $request->file('stamp'),
                auth()->user(),
            ),
            'statusCode' => 200,
        ]);
    }

    public function reset()
    {
        if (!$this->canManage()) {
            return $this->forbidden();
        }

        return response()->json([
            'message' => 'Letter branding restored to the system defaults.',
            'data' => $this->branding->reset(auth()->user()),
            'statusCode' => 200,
        ]);
    }

    private function canManage(): bool
    {
        $user = auth()->user();

        return $user && $user->hasAnyRole([
            'ROLE ADMIN',
            'ROLE DG',
            'ROLE DIRECTOR GENERAL',
            'ROLE SUPER ADMIN',
            'ROLE SUPERADMIN',
        ]);
    }

    private function forbidden()
    {
        return response()->json(['message' => 'Only the Director General or Super Admin can manage letter branding.', 'statusCode' => 403], 403);
    }
}
