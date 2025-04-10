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
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Create Hospital')) {
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
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Create Hospital')) {
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
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Update Hospital')) {
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

        $hospital = Hospital::find($id);

        if (!$hospital) {
            return response([
                'message' => 'Hospital not found',
                'statusCode' => 404,
            ]);
        }


        $hospital->update([
            'hospital_name' => $data['hospital_name'],
            'hospital_address' => $data['hospital_address'],
            'contact_number' => $data['contact_number'],
            'hospital_email' => $data['hospital_email'],
            'created_by' => Auth::id(),
        ]);

        if ($hospital) {
            return response([
                'data' => $hospital,
                'message' => 'Hospital updated successfully',
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
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Delete Hospital')) {
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
        }

        $hospital->delete();

        return response([
            'message' => 'Hospital blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/hospitals/unblock/{id}",
     *     summary="Unblock hospital",
     *     tags={"hospitals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\Header(
     *             header="Cache-Control",
     *             description="Cache control header",
     *             @OA\Schema(type="string", example="no-cache, private")
     *         ),
     *         @OA\Header(
     *             header="Content-Type",
     *             description="Content type header",
     *             @OA\Schema(type="string", example="application/json; charset=UTF-8")
     *         ),
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function unBlockHospital(int $id)
    {

        $hospital = Hospital::withTrashed()->find($id);

        if (!$hospital) {
            return response([
                'message' => 'Hospital not found',
                'statusCode' => 404,
            ], 404);
        }

        $hospital->restore($id);

        return response([
            'message' => 'Hospital restored successfully',
            'statusCode' => 200,
        ], 200);
    }
}