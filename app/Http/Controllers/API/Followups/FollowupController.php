<?php

namespace App\Http\Controllers\API\Followups;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Followup|Create Followup|Update Followup|Delete Followup', ['only' => ['index','store','show','update','destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View Followup')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $followups = FollowUp::with(['hospitalLetter','createdBy'])->get();

        return response()->json([
            'data' => $followups,
            'statusCode' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create Followup')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validated = $request->validate([
            'letter_id' => ['required','exists:hospital_letters,letter_id'],
            'followup_date' => ['required','date'],
            'notes' => ['nullable','string'],
        ]);

        $validated['created_by'] = Auth::id();

        $followup = FollowUp::create($validated);

        return response()->json([
            'message' => 'FollowUp created successfully',
            'data' => $followup,
            'statusCode' => 201
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View Followup')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $followup = FollowUp::with(['hospitalLetter','createdBy'])->find($id);

        if (!$followup) {
            return response()->json([
                'message' => 'FollowUp not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $followup,
            'statusCode' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Update Followup')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $followup = FollowUp::find($id);

        if (!$followup) {
            return response()->json([
                'message' => 'FollowUp not found',
                'statusCode' => 404
            ], 404);
        }

        $validated = $request->validate([
            'letter_id' => ['sometimes','exists:hospital_letters,letter_id'],
            'followup_date' => ['sometimes','date'],
            'notes' => ['nullable','string'],
        ]);

        $followup->update($validated);

        return response()->json([
            'message' => 'FollowUp updated successfully',
            'data' => $followup,
            'statusCode' => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Delete Followup')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $followup = FollowUp::find($id);

        if (!$followup) {
            return response()->json([
                'message' => 'FollowUp not found',
                'statusCode' => 404
            ], 404);
        }

        $followup->delete();

        return response()->json([
            'message' => 'FollowUp deleted successfully',
            'statusCode' => 200
        ]);
    }
}
