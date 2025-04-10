<?php

namespace App\Http\Controllers\API\Hospitals;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HospitalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Hospital|Create Hospital|View Hospital|Update Hospital|Delete Hospital', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }



    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('View Hospital')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $hospitals = Hospital::withTrashed()->get();

        if ($hospitals) {
            return response([
                'data' => $hospitals,
                'statusCode' => 200,
            ], 200);
        } else {
            return response([
                'message' => 'No data found',
                'statusCode' => 500,
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('Create Hospital')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'hospital_name' => ['required', 'string'],
            'hospital_address' => ['nullable', 'string'],
            'contact_number' => ['nullable', 'string'],
            'hospital_email' => ['nullable', 'email'],
        ]);


        // Create hospital
        $hospital = Hospital::create([
            'hospital_name' => $data['hospital_name'],
            'hospital_address' => $data['hospital_address'],
            'contact_number' => $data['contact_number'],
            'hospital_email' => $data['hospital_email'],
            'created_by' => Auth::id(),
            // 'created_by' => auth()->id(),
        ]);

        if ($hospital) {
            return response([
                'data' => $hospital,
                'statusCode' => 200,
            ], 201);
        } else {
            return response([
                'message' => 'Internal server error',
                'statusCode' => 500,
            ], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        if (!auth()->user()->can('Create Hospital')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $hospital = Hospital::withTrashed()->find($id);

        if (!$hospital) {
            return response([
                'message' => 'Hospital not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $hospital,
                'statusCode' => 200,
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}