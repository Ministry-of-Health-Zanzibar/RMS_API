<?php

namespace App\Http\Controllers\API\BillFiles;

use App\Http\Controllers\Controller;
use App\Models\BillFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BillFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View BillFile|Create BillFile|Update BillFile|Delete BillFile', ['only' => ['index','store','show','update','destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View BillFile')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billFiles = BillFile::with(['created_by'])->latest()->get();

        return response()->json([
            'data' => $billFiles,
            'statusCode' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create BillFile')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validated = $request->validate([
            'bill_file_title' => ['required','string','max:255'],
            'bill_file' => ['required','file','mimes:pdf,jpg,jpeg,png','max:2048'],
            'bill_file_amount' => ['required','string'],
        ]);

        $path = $request->file('bill_file')->store('bill_files', 'public');

        $validated['bill_file'] = $path;
        $validated['created_by'] = Auth::id();

        $billFile = BillFile::create($validated);

        return response()->json([
            'message' => 'Bill file created successfully',
            'data' => $billFile,
            'statusCode' => 201
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View BillFile')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billFile = BillFile::with(['created_by'])->find($id);

        if (!$billFile) {
            return response()->json([
                'message' => 'BillFile not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'data' => $billFile,
            'statusCode' => 200
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!($user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) && $user->can('Update BillFile'))) {
            return response()->json(['message' => 'Forbidden','statusCode' => 403], 403);
        }

        $billFile = BillFile::find($id);
        if (!$billFile) {
            return response()->json(['message' => 'BillFile not found','statusCode' => 404], 404);
        }

        $validated = $request->validate([
            'bill_file_title' => ['sometimes','string','max:255'],
            'bill_file' => ['sometimes','file','mimes:pdf,jpg,jpeg,png','max:2048'],
            'bill_file_amount' => ['sometimes','string'],
        ]);

        if ($request->hasFile('bill_file')) {
            if ($billFile->bill_file && Storage::disk('public')->exists($billFile->bill_file)) {
                Storage::disk('public')->delete($billFile->bill_file);
            }
            $validated['bill_file'] = $request->file('bill_file')->store('bill_files', 'public');
        }

        $billFile->update($validated);
        $billFile->refresh(); // ensure latest data

        return response()->json([
            'message' => 'BillFile updated successfully',
            'data' => $billFile,
            'statusCode' => 200
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Delete BillFile')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billFile = BillFile::find($id);

        if (!$billFile) {
            return response()->json([
                'message' => 'BillFile not found',
                'statusCode' => 404
            ], 404);
        }

        $billFile->delete();

        return response()->json([
            'message' => 'BillFile deleted successfully',
            'statusCode' => 200
        ]);
    }
}
