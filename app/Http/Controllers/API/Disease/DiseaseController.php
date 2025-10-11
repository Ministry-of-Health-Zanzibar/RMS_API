<?php

namespace App\Http\Controllers\API\Disease;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Disease;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @group Diseases Management
 * APIs for managing diseases/diagnoses
 */
class DiseaseController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the diseases.
     *
     * @response 200 {
     *  "success": true,
     *  "data": [
     *      {
     *          "disease_id": 1,
     *          "disease_name": "Glomus Tympanojugulare Tumour",
     *          "disease_code": "GT123",
     *          "created_by": 2,
     *          "created_at": "2025-10-07T10:00:00.000000Z",
     *          "updated_at": "2025-10-07T10:00:00.000000Z"
     *      }
     *  ],
     *  "statusCode": 200
     * }
     */
    public function index()
    {
        if (!auth()->user()->can('View Disease')) {
            return response()->json([
                'success' => false,
                'message' => 'no permission view',
                'statusCode' => 403,
            ], 403);
        }

        $diseases = Disease::all();

        return response()->json([
            'success' => true,
            'data' => $diseases,
            'statusCode' => 200,
        ]);
    }

    /**
     * Store a newly created disease.
     *
     * @bodyParam disease_name string required Name of the disease. Example: Glomus Tympanojugulare Tumour
     * @bodyParam disease_code string optional Unique code for the disease. Example: GT123
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Disease created successfully",
     *  "data": {
     *      "disease_id": 1,
     *      "disease_name": "Glomus Tympanojugulare Tumour",
     *      "disease_code": "GT123",
     *      "created_by": 2,
     *      "created_at": "2025-10-07T10:00:00.000000Z",
     *      "updated_at": "2025-10-07T10:00:00.000000Z"
     *  },
     *  "statusCode": 201
     * }
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('Create Disease')) {
            return response()->json([
                'success' => false,
                'message' => 'no permission create',
                'statusCode' => 403,
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'disease_name' => ['required', 'string', 'max:255'],
            'disease_code' => ['nullable', 'string', 'max:100', 'unique:diseases,disease_code'],
            'disease_description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $disease = Disease::create([
                'disease_name' => $request->disease_name,
                'disease_code' => $request->disease_code,
                'disease_description' => $request->disease_description,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Disease created successfully',
                'data' => $disease,
                'statusCode' => 201,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'statusCode' => 500,
            ], 500);
        }
    }

    /**
     * Display the specified disease.
     *
     * @urlParam id int required The ID of the disease. Example: 1
     *
     * @response 200 {
     *  "success": true,
     *  "data": {
     *      "disease_id": 1,
     *      "disease_name": "Glomus Tympanojugulare Tumour",
     *      "disease_code": "GT123",
     *      "created_by": 2,
     *      "created_at": "2025-10-07T10:00:00.000000Z",
     *      "updated_at": "2025-10-07T10:00:00.000000Z"
     *  },
     *  "statusCode": 200
     * }
     */
    public function show($id)
    {
        if (!auth()->user()->can('View Disease')) {
            return response()->json([
                'success' => false,
                'message' => 'no permission view',
                'statusCode' => 403,
            ], 403);
        }

        $disease = Disease::find($id);

        if (!$disease) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found',
                'statusCode' => 404,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $disease,
            'statusCode' => 200,
        ]);
    }

    /**
     * Update the specified disease.
     *
     * @urlParam id int required The ID of the disease. Example: 1
     * @bodyParam disease_name string required Name of the disease. Example: Glomus Tympanojugulare Tumour
     * @bodyParam disease_code string optional Unique code for the disease. Example: GT123
     *
     * @response 200 {
     *  "success": true,
     *  "message": "Disease updated successfully",
     *  "data": {
     *      "disease_id": 1,
     *      "disease_name": "Glomus Tympanojugulare Tumour",
     *      "disease_code": "GT123",
     *      "created_by": 2,
     *      "created_at": "2025-10-07T10:00:00.000000Z",
     *      "updated_at": "2025-10-07T11:00:00.000000Z"
     *  },
     *  "statusCode": 200
     * }
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('Edit Disease')) {
            return response()->json([
                'success' => false,
                'message' => 'no permission edit',
                'statusCode' => 403,
            ], 403);
        }

        $disease = Disease::find($id);

        if (!$disease) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found',
                'statusCode' => 404,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'disease_name' => ['required', 'string', 'max:255'],
            'disease_code' => ['nullable', 'string', 'max:100', 'unique:diseases,disease_code,' . $id . ',disease_id'],
            'disease_description' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $disease->update([
                'disease_name' => $request->disease_name,
                'disease_code' => $request->disease_code,
                'disease_description' => $request->disease_description,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Disease updated successfully',
                'data' => $disease,
                'statusCode' => 200,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'statusCode' => 500,
            ], 500);
        }
    }

    /**
     * Remove the specified disease.
     *
     * @urlParam id int required The ID of the disease. Example: 1
     *
     * @response 200 {
     *  "success": true,
     *  "message": "Disease deleted successfully",
     *  "statusCode": 200
     * }
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('Delete Disease')) {
            return response()->json([
                'success' => false,
                'message' => 'no permission delete',
                'statusCode' => 403,
            ], 403);
        }

        $disease = Disease::find($id);

        if (!$disease) {
            return response()->json([
                'success' => false,
                'message' => 'Disease not found',
                'statusCode' => 404,
            ], 404);
        }

        $disease->delete();

        return response()->json([
            'success' => true,
            'message' => 'Disease deleted successfully',
            'statusCode' => 200,
        ]);
    }
}
