<?php

namespace App\Http\Controllers\API\HospitalLetters;

use App\Http\Controllers\Controller;
use App\Models\HospitalLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class HospitalLetterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Hospital Letter|Create Hospital Letter|Update Hospital Letter|Delete Hospital Letter', ['only' => ['index','store','show','update','destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $letters = HospitalLetter::with(['referral','followups'])->get();

        return response()->json([
            'data' => $letters,
            'statusCode' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validated = $request->validate([
            'referral_id' => ['required','exists:referrals,referral_id'],
            'received_date' => ['required','date'],
            'content_summary' => ['nullable','string'],
            'next_appointment_date' => ['nullable','date'],
            'letter_file' => ['nullable','file','mimes:pdf,doc,docx','max:2048'],
            'outcome' => ['required','in:Follow-up,Finished,Transferred,Death'],
        ]);

        if ($request->hasFile('letter_file')) {
            $path = $request->file('letter_file')->store('hospital_letters','public');
            $validated['letter_file'] = $path;
        }

        $validated['created_by'] = Auth::id();

        $letter = HospitalLetter::create($validated);

        return response()->json([
            'message' => 'Hospital Letter created successfully',
            'data' => $letter,
            'statusCode' => 201
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $letter = HospitalLetter::with(['referral','followups'])->find($id);

        if (!$letter) {
            return response()->json([
                'message' => 'Hospital Letter not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $letter,
            'statusCode' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Update Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $letter = HospitalLetter::find($id);

        if (!$letter) {
            return response()->json([
                'message' => 'Hospital Letter not found',
                'statusCode' => 404
            ], 404);
        }

        $validated = $request->validate([
            'referral_id' => ['sometimes','exists:referrals,referral_id'],
            'received_date' => ['sometimes','date'],
            'content_summary' => ['nullable','string'],
            'next_appointment_date' => ['nullable','date'],
            'letter_file' => ['nullable','file','mimes:pdf,doc,docx','max:2048'],
            'outcome' => ['sometimes','in:Follow-up,Finished,Transferred,Death'],
        ]);

        if ($request->hasFile('letter_file')) {
            if ($letter->letter_file) {
                Storage::disk('public')->delete($letter->letter_file);
            }
            $path = $request->file('letter_file')->store('hospital_letters','public');
            $validated['letter_file'] = $path;
        }

        $letter->update($validated);

        return response()->json([
            'message' => 'Hospital Letter updated successfully',
            'data' => $letter,
            'statusCode' => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Delete Hospital Letter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $letter = HospitalLetter::find($id);

        if (!$letter) {
            return response()->json([
                'message' => 'Hospital Letter not found',
                'statusCode' => 404
            ], 404);
        }

        if ($letter->letter_file) {
            Storage::disk('public')->delete($letter->letter_file);
        }

        $letter->delete();

        return response()->json([
            'message' => 'Hospital Letter deleted successfully',
            'statusCode' => 200
        ]);
    }
}
