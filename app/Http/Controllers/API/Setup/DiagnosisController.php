<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Models\Diagnosis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Auth;


/**
 * @OA\Tag(
 *     name="Diagnosis",
 *     description="API Endpoints for managing diagnoses"
 * )
 */
/**
 * @OA\Schema(
 *     schema="Diagnosis",
 *     type="object",
 *     title="Diagnosis",
 *     required={"diagnosis_name","diagnosis_code"},
 *     @OA\Property(property="diagnosis_id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", example="c0a80123-45ab-67cd-89ef-1234567890ab"),
 *     @OA\Property(property="diagnosis_name", type="string", example="Diabetes"),
 *     @OA\Property(property="diagnosis_code", type="string", example="E11"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */

class DiagnosisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * @OA\Get(
     *     path="/api/diagnoses",
     *     tags={"Diagnosis"},
     *     summary="Get all diagnoses",
     *     @OA\Response(
     *         response=200,
     *         description="List of diagnoses",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Diagnosis")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('View Diagnoses')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $diagnoses = Diagnosis::withTrashed()->latest()->get();

        return response()->json([
            'message' => 'Diagnoses retrieved successfully',
            'data' => $diagnoses,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/diagnoses",
     *     tags={"Diagnosis"},
     *     summary="Create a new diagnosis",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Diagnosis")
     *     ),
     *     @OA\Response(response=201, description="Diagnosis created successfully"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Diagnoses')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'diagnosis_name' => 'required|string|max:255',
            'diagnosis_code' => 'required|string|max:50|unique:diagnoses,diagnosis_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Validation failed'),
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        $diagnosis = Diagnosis::create([
            'uuid' => (string) Str::uuid(),
            'diagnosis_name' => $request->diagnosis_name,
            'diagnosis_code' => $request->diagnosis_code,
        ]);

        return response()->json([
            'message' => __('Diagnosis created successfully'),
            'data' => $diagnosis,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/diagnoses/{uuid}",
     *     tags={"Diagnosis"},
     *     summary="Get diagnosis by UUID",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Diagnosis found"),
     *     @OA\Response(response=404, description="Diagnosis not found")
     * )
     */
    public function show($uuid)
    {
        $user = auth()->user();
        if (!$user->can('View Diagnoses')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $diagnosis = Diagnosis::where('uuid', $uuid)->first();

        if (!$diagnosis) {
            return response()->json([
                'message' => __('Diagnosis not found'),
                'statusCode' => 404,
            ], 404);
        }

        return response()->json([
            'message' => __('Diagnosis retrieved successfully'),
            'data' => $diagnosis,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/diagnoses/{uuid}",
     *     tags={"Diagnosis"},
     *     summary="Update an existing diagnosis",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Diagnosis")
     *     ),
     *     @OA\Response(response=200, description="Diagnosis updated successfully"),
     *     @OA\Response(response=404, description="Diagnosis not found"),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function update(Request $request, $uuid)
    {
        $user = auth()->user();
        if (!$user->can('Update Diagnoses')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $diagnosis = Diagnosis::where('uuid', $uuid)->first();

        if (!$diagnosis) {
            return response()->json([
                'message' => __('Diagnosis not found'),
                'statusCode' => 404,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'diagnosis_name' => 'required|string|max:255',
            'diagnosis_code' => 'required|string|max:50|unique:diagnoses,diagnosis_code,' . $diagnosis->diagnosis_id . ',diagnosis_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Validation failed'),
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        $diagnosis->update($request->only(['diagnosis_name', 'diagnosis_code']));

        return response()->json([
            'message' => __('Diagnosis updated successfully'),
            'data' => $diagnosis,
            'statusCode' => 200,
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/diagnoses/{uuid}",
     *     tags={"Diagnosis"},
     *     summary="Delete a diagnosis",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Diagnosis deleted successfully"),
     *     @OA\Response(response=404, description="Diagnosis not found")
     * )
     */
    public function destroy($uuid)
    {
        $user = auth()->user();
        if (!$user->can('Delete Diagnoses')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $diagnosis = Diagnosis::where('uuid', $uuid)->first();

        if (!$diagnosis) {
            return response()->json([
                'message' => __('Diagnosis not found'),
                'statusCode' => 404,
            ], 404);
        }

        $diagnosis->delete();

        return response()->json([
            'message' => __('Diagnosis deleted successfully'),
            'statusCode' => 200,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/diagnoses/restore/{uuid}",
     *     tags={"Diagnosis"},
     *     summary="Restore a soft-deleted diagnosis",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Diagnosis restored successfully"),
     *     @OA\Response(response=404, description="Diagnosis not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function restore($uuid)
    {
        // $user = auth()->user();
        // if (!$user->can('Restore Diagnoses')) {
        //     return response()->json([
        //         'message' => 'Forbidden',
        //         'statusCode' => 403
        //     ], 403);
        // }

        // Fetch the trashed record
        $diagnosis = Diagnosis::onlyTrashed()->where('uuid', $uuid)->first();

        if (!$diagnosis) {
            return response()->json([
                'message' => __('Diagnosis not found or not deleted'),
                'statusCode' => 404,
            ], 404);
        }

        // Restore the diagnosis
        $diagnosis->restore();

        return response()->json([
            'message' => __('Diagnosis restored successfully'),
            'statusCode' => 200,
            'data' => $diagnosis
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/diagnoses/import",
     *     tags={"Diagnosis"},
     *     summary="Bulk import diagnoses from Excel file",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Excel file with diagnosis_name and diagnosis_code columns"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Diagnoses imported successfully"),
     *     @OA\Response(response=400, description="Invalid file")
     * )
     */
    public function importExcel(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('Create Diagnoses')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Validate file type
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('Invalid file format. Allowed: xlsx, xls, csv'),
                'errors' => $validator->errors(),
                'statusCode' => 400,
            ], 400);
        }

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();

            // Auto-detect file type (Excel or CSV)
            $spreadsheet = IOFactory::load($path);
            $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $inserted = 0;

            foreach ($data as $index => $row) {
                // Skip header row
                if ($index == 1) continue;

                $diagnosis_name = isset($row['A']) ? trim($row['A']) : null;
                $diagnosis_code = isset($row['B']) ? trim($row['B']) : null;

                // Skip empty rows
                if (!$diagnosis_name || !$diagnosis_code) continue;

                // Skip duplicates
                if (Diagnosis::where('diagnosis_code', $diagnosis_code)->exists()) continue;

                Diagnosis::create([
                    'uuid' => (string) Str::uuid(),
                    'diagnosis_name' => $diagnosis_name,
                    'diagnosis_code' => $diagnosis_code,
                ]);

                $inserted++;
            }

            return response()->json([
                'message' => __('Diagnoses imported successfully'),
                'inserted' => $inserted,
                'statusCode' => 200,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Diagnosis import failed: ' . $e->getMessage());
            return response()->json([
                'message' => __('Failed to import diagnoses'),
                'error' => $e->getMessage(),
                'statusCode' => 500,
            ], 500);
        }
    }
}
