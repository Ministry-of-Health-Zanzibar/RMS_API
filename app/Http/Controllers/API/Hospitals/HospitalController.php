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
    /**
     * @OA\Get(
     *     path="/api/hospitals",
     *     summary="Get all hospitals",
     *     tags={"hospitals"},
     *     @OA\Response(
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
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="hospital_id", type="integer"),
     *                     @OA\Property(property="hospital_code", type="string"),
     *                     @OA\Property(property="hospital_name", type="string"),
     *                     @OA\Property(property="hospital_address", type="string"),
     *                     @OA\Property(property="contact_number", type="string"),
     *                     @OA\Property(property="hospital_email", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF','ROLE DG OFFICER']) || !$user->can('View Hospital')) {
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
                'statusCode' => 200,
            ], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/hospitals",
     *     summary="Create hospitals",
     *     tags={"hospitals"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="hospital_name", type="string"),
     *                     @OA\Property(property="hospital_address", type="string"),
     *                     @OA\Property(property="contact_number", type="string"),
     *                     @OA\Property(property="hospital_email", type="string"),
     *         )
     *     ),
     *     @OA\Response(
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
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create Hospital')) {
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
                'statusCode' => 201,
            ], status: 201);
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
    /**
     * @OA\Get(
     *     path="/api/hospitals/{id}",
     *     summary="Find hospital by ID",
     *     tags={"hospitals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="hospital_id", type="integer", example=1),
     *                 @OA\Property(property="hospital_code", type="string", example="HOSP001"),
     *                 @OA\Property(property="hospital_name", type="string", example="LUMUMBA"),
     *                 @OA\Property(property="hospital_address", type="string", example="Zanzibar"),
     *                 @OA\Property(property="contact_number", type="string", example="000 000 000"),
     *                 @OA\Property(property="hospital_email", type="string", example="hospital@gmail.com"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-10T10:44:31.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-10T10:44:31.000000Z"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, example=null)
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF','ROLE DG OFFICER']) || !$user->can('View Hospital')) {
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
    /**
     * @OA\Put(
     *     path="/api/hospitals/{id}",
     *     summary="Update hospital",
     *     tags={"hospitals"},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *      ),
     *     @OA\Response(
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
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="hospital_name", type="string"),
     *                 @OA\Property(property="hospital_address", type="string" ),
     *                 @OA\Property(property="contact_number", type="string"),
     *                 @OA\Property(property="hospital_email", type="string"),
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Update Hospital')) {
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
    /**
     * @OA\Delete(
     *     path="/api/hospitals/{id}",
     *     summary="Delete hospital",
     *     tags={"hospitals"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
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
    public function destroy(int $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Delete Hospital')) {
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
     *     path="/api/hospitals/unBlock/{id}",
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
            'message' => 'Hospital unblocked successfully',
            'statusCode' => 200,
        ], 200);
    }
}
