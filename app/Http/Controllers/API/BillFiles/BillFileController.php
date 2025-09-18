<?php

namespace App\Http\Controllers\API\BillFiles;

use App\Http\Controllers\Controller;
use App\Models\BillFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

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
    /**
     * @OA\Get(
     *     path="/api/bill-files",
     *     summary="Get all bill files",
     *     tags={"Bill Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Bill files retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="bill_file_id", type="integer", example=1),
     *                     @OA\Property(property="bill_file", type="string", example="bill_files/file.pdf"),
     *                     @OA\Property(property="bill_file_amount", type="string", example="50,000,000"),
     *                     @OA\Property(property="created_by", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     )
     * )
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

        $billFiles = BillFile::with([
            'hospital',
            'created_by'
            ])->latest()->get();

        return response()->json([
            'data' => $billFiles,
            'statusCode' => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/bill-files",
     *     summary="Create a bill file",
     *     tags={"Bill Files"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"bill_file","bill_file_amount"},
     *                 @OA\Property(property="bill_file", type="string", format="binary"),
     *                 @OA\Property(property="bill_file_amount", type="string", example="50,000,000")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bill file created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="bill_file_id", type="integer", example=1),
     *                 @OA\Property(property="bill_file", type="string", example="bill_files/file.pdf"),
     *                 @OA\Property(property="bill_file_amount", type="string", example="50,000,000"),
     *                 @OA\Property(property="hospital_id", type="integer", example="1"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="message", type="string", example="Bill file created successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="statusCode", type="integer", example=403)
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Authorization
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create BillFile')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'bill_file'        => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'bill_file_amount' => ['required', 'numeric', 'min:0'],
            'bill_start'       => ['nullable', 'string'],
            'bill_end'         => ['nullable', 'string', 'after_or_equal:bill_start'],
            'hospital_id'      => ['required', 'numeric', 'exists:hospitals,hospital_id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        $validated = $validator->validated();

        // Handle file upload
        if ($request->hasFile('bill_file')) {
            $file = $request->file('bill_file');
            $extension = $file->getClientOriginalExtension();

            // Unique filename
            $newFileName = 'bill_file_' . date('h-i-s_a_d-m-Y') . '.' . $extension;

            // Move file to public/uploads/billFiles/
            $file->move(public_path('uploads/billFiles/'), $newFileName);

            $validated['bill_file'] = 'uploads/billFiles/' . $newFileName;
        }

        // Add created_by
        $validated['created_by'] = $user->id;

        // Create BillFile
        $billFile = BillFile::create($validated);

        return response()->json([
            'message'    => 'Bill file created successfully',
            'data'       => $billFile,
            'statusCode' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/bill-files/{id}",
     *     summary="Get a specific bill file",
     *     tags={"Bill Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill file retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="bill_file_id", type="integer", example=1),
     *                 @OA\Property(property="bill_file", type="string", example="bill_files/file.pdf"),
     *                 @OA\Property(property="bill_file_amount", type="string", example="50,000,000"),
     *                 @OA\Property(property="created_by", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="BillFile not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="BillFile not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
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

        $id = (int) $id;

        if (!is_numeric($id)) {
            return response()->json([
                'message' => 'Invalid BillFile ID',
                'statusCode' => 400
            ], 400);
        }

        $billFile = BillFile::with([
            'hospital',
            'created_by'
        ])->find($id);

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
    /**
     * @OA\Put(
     *     path="/api/bill-files/{id}",
     *     summary="Update a bill file",
     *     tags={"Bill Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="bill_file", type="string", format="binary"),
     *                 @OA\Property(property="bill_file_amount", type="string", example="55,000,000"),
     *                 @OA\Property(property="hospital_id", type="integer", example="1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill file updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="BillFile updated successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="BillFile not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="BillFile not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
     */
    public function updateBillFile(Request $request,int $id)
    {
        $user = auth()->user();

        // Authorization
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Update BillFile')) {
            return response()->json([
                'message'    => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billFile = BillFile::find($id);

        if (!$billFile) {
            return response()->json([
                'message'    => 'BillFile not found',
                'statusCode' => 404
            ], 404);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'bill_file'        => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'bill_file_amount' => ['sometimes', 'numeric', 'min:0'],
            'bill_start'       => ['nullable', 'string'],
            'bill_end'         => ['nullable', 'string', 'after_or_equal:bill_start'],
            'hospital_id'      => ['required', 'numeric', 'exists:hospitals,hospital_id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'     => 'error',
                'errors'     => $validator->errors(),
                'statusCode' => 422,
            ], 422);
        }

        $validated = $validator->validated();

        // Handle file upload
        if ($request->hasFile('bill_file')) {
            // Delete old file if exists
            if ($billFile->bill_file && File::exists(public_path($billFile->bill_file))) {
                File::delete(public_path($billFile->bill_file));
            }

            $file = $request->file('bill_file');
            $extension = $file->getClientOriginalExtension();

            // Unique filename (safe format)
            $newFileName = 'bill_file_' . date('h-i-s_a_d-m-Y') . '.' . $extension;

            // Move file to public/uploads/billFiles/
            $file->move(public_path('uploads/billFiles/'), $newFileName);

            $validated['bill_file'] = 'uploads/billFiles/' . $newFileName;
        }

        // Track updater
        $validated['updated_by'] = $user->id;

        // Update BillFile
        $billFile->update($validated);
        $billFile->refresh();

        return response()->json([
            'message'    => 'Bill file updated successfully',
            'data'       => $billFile,
            'statusCode' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/bill-files/{id}",
     *     summary="Delete a bill file",
     *     tags={"Bill Files"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bill file deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="BillFile deleted successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="BillFile not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="BillFile not found"),
     *             @OA\Property(property="statusCode", type="integer", example=404)
     *         )
     *     )
     * )
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

    public function getBillFilesForPayment()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View BillFile')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billFiles = DB::table('bill_files as bf')
        ->leftJoin('hospitals as h', 'bf.hospital_id', '=', 'h.hospital_id')
        ->leftJoin('bills as b', 'bf.bill_file_id', '=', 'b.bill_file_id')
        ->leftJoin('bill_payments as bp', 'b.bill_id', '=', 'bp.bill_id')
        ->leftJoin('payments as p', 'bp.payment_id', '=', 'p.payment_id')
        ->select(
            'bf.bill_file_id',
            'h.hospital_name',
            'bf.bill_file',
            'bf.bill_start',
            'bf.bill_end',
            DB::raw('CAST(bf.bill_file_amount AS DECIMAL(15,2)) as bill_file_amount'),
            DB::raw('COALESCE(SUM(bp.allocated_amount), 0) as paid_amount'),
            DB::raw('(CAST(bf.bill_file_amount AS DECIMAL(15,2)) - COALESCE(SUM(bp.allocated_amount), 0)) as balance'),
            DB::raw("
                CASE 
                    WHEN COALESCE(SUM(bp.allocated_amount), 0) = 0 THEN 'Pending'
                    WHEN COALESCE(SUM(bp.allocated_amount), 0) < CAST(bf.bill_file_amount AS DECIMAL(15,2)) THEN 'Partially Paid'
                    WHEN COALESCE(SUM(bp.allocated_amount), 0) >= CAST(bf.bill_file_amount AS DECIMAL(15,2)) THEN 'Paid'
                END as status
            ")
        )
        ->groupBy(
            'bf.bill_file_id',
            'h.hospital_name',
            'bf.bill_file',
            'bf.bill_file_amount',
            'bf.bill_start',
            'bf.bill_end'
        )
        ->havingRaw('CAST(bf.bill_file_amount AS DECIMAL(15,2)) = COALESCE(SUM(b.total_amount), 0)')
        ->get();

        return response()->json([
            'data' => $billFiles,
            'statusCode' => 200
        ]);
    }

    public function getBillFilesByHospitalId(int $hospitalId)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View BillFile')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $hospitalId = (int) $hospitalId;

        // Fetch hospital info first
        $hospital = DB::table('hospitals')
            ->where('hospital_id', $hospitalId)
            ->first();

        $hospitalName = $hospital->hospital_name ?? null;

        // Fetch bill_files with bills, only where total bills match bill_file_amount
        $billFiles = DB::table('bill_files as bf')
            ->leftJoin('bills as b', 'bf.bill_file_id', '=', 'b.bill_file_id')
            ->leftJoin('bill_payments as bp', 'b.bill_id', '=', 'bp.bill_id')
            ->select(
                'bf.bill_file_id',
                'bf.bill_file',
                'bf.bill_start',
                'bf.bill_end',
                DB::raw('CAST(bf.bill_file_amount AS DECIMAL(15,2)) as bill_file_amount'),
                DB::raw('COALESCE(SUM(bp.allocated_amount), 0) as paid_amount'),
                DB::raw('(CAST(bf.bill_file_amount AS DECIMAL(15,2)) - COALESCE(SUM(bp.allocated_amount), 0)) as balance'),
                DB::raw('COALESCE(SUM(b.total_amount), 0) as total_bill_amount'),
                DB::raw("
                    CASE
                        WHEN COALESCE(SUM(bp.allocated_amount), 0) = 0 THEN 'Pending'
                        WHEN COALESCE(SUM(bp.allocated_amount), 0) < CAST(bf.bill_file_amount AS DECIMAL(15,2)) THEN 'Partially Paid'
                        WHEN COALESCE(SUM(bp.allocated_amount), 0) >= CAST(bf.bill_file_amount AS DECIMAL(15,2)) THEN 'Paid'
                    END as status
                ")
            )
            ->where('bf.hospital_id', $hospitalId)
            ->groupBy(
                'bf.bill_file_id',
                'bf.bill_file',
                'bf.bill_file_amount',
                'bf.bill_start',
                'bf.bill_end'
            )
            // ->havingRaw('CAST(bf.bill_file_amount AS DECIMAL(15,2)) = COALESCE(SUM(b.total_amount), 0)')
            ->get();

        $billFilesArray = $billFiles->map(function($item) {
            return (array) $item;
        })->toArray();

        // Calculate totals for included bill_files
        $totals = [
            'bill_file_amount' => array_sum(array_column($billFilesArray, 'bill_file_amount')),
            'paid_amount' => array_sum(array_column($billFilesArray, 'paid_amount')),
            'balance' => array_sum(array_column($billFilesArray, 'balance')),
            'status' => $billFilesArray ? 'Mixed' : 'No Bills filled in this Bill file'
        ];

        // Wrap result
        $result = [
            'hospital_id' => $hospitalId,
            'hospital_name' => $hospitalName,
            'bill_files' => $billFilesArray,
            'totals' => $totals
        ];

        return response()->json([
            'data' => $result,
            'statusCode' => 200
        ]);
    }

    public function getBillFilesGroupByHospitals()
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View BillFile')) {
            return response()->json([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $billFiles = DB::table('bill_files as bf')
            ->leftJoin('hospitals as h', 'bf.hospital_id', '=', 'h.hospital_id')
            ->leftJoin('bills as b', 'bf.bill_file_id', '=', 'b.bill_file_id')
            ->leftJoin('bill_payments as bp', 'b.bill_id', '=', 'bp.bill_id')
            ->select(
                'h.hospital_name',
                'h.hospital_id',
                DB::raw('SUM(COALESCE(CAST(bf.bill_file_amount AS DECIMAL(15,2)), 0)) as total_bill_file_amount'),
                DB::raw('COALESCE(SUM(bp.allocated_amount), 0) as total_allocated_amount'),
                DB::raw('(SUM(COALESCE(CAST(bf.bill_file_amount AS DECIMAL(15,2)),0)) - COALESCE(SUM(bp.allocated_amount), 0)) as total_balance'),
                DB::raw("
                    CASE 
                        WHEN COALESCE(SUM(bp.allocated_amount), 0) = 0 THEN 'Pending'
                        WHEN COALESCE(SUM(bp.allocated_amount), 0) < SUM(COALESCE(CAST(bf.bill_file_amount AS DECIMAL(15,2)),0)) THEN 'Partially Paid'
                        WHEN COALESCE(SUM(bp.allocated_amount), 0) >= SUM(COALESCE(CAST(bf.bill_file_amount AS DECIMAL(15,2)),0)) THEN 'Paid'
                    END as status
                ")
            )
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('bills as bb')
                    ->whereRaw('bb.bill_file_id = bf.bill_file_id'); // only include bill_files linked in bills
            })
            ->groupBy('h.hospital_name', 'h.hospital_id')
            ->get();

        $billFilesArray = $billFiles->map(function ($item) {
            return (array) $item;
        })->toArray();

        // Calculate grand totals for all hospitals
        $grandTotals = [
            'hospital_name'           => 'ALL HOSPITALS',
            'hospital_id'             => null,
            'total_bill_file_amount'  => array_sum(array_column($billFilesArray, 'total_bill_file_amount')),
            'total_allocated_amount'  => array_sum(array_column($billFilesArray, 'total_allocated_amount')),
            'total_balance'           => array_sum(array_column($billFilesArray, 'total_balance')),
            'status'                  => 'Summary'
        ];

        $result = [
            'hospitals' => $billFilesArray,
            'totals'    => $grandTotals
        ];

        return response()->json([
            'data'       => $result,
            'statusCode' => 200
        ]);
    }



    // public function getBillFilesGroupByHospitals()
    // {
    //     $user = auth()->user();
    //     if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('View BillFile')) {
    //         return response()->json([
    //             'message' => 'Forbidden',
    //             'statusCode' => 403
    //         ], 403);
    //     }

    //     $billFiles = DB::table('bill_files as bf')
    //         ->leftJoin('hospitals as h', 'bf.hospital_id', '=', 'h.hospital_id')
    //         ->leftJoin('bills as b', 'bf.bill_file_id', '=', 'b.bill_file_id')
    //         ->leftJoin('bill_payments as bp', 'b.bill_id', '=', 'bp.bill_id')
    //         ->select(
    //             'h.hospital_name',
    //             'h.hospital_id',
    //             DB::raw('SUM(COALESCE(CAST(bf.bill_file_amount AS DECIMAL(15,2)), 0)) as total_bill_file_amount'),
    //             DB::raw('COALESCE(SUM(bp.allocated_amount), 0) as total_allocated_amount'),
    //             DB::raw('(SUM(COALESCE(CAST(bf.bill_file_amount AS DECIMAL(15,2)),0)) - COALESCE(SUM(bp.allocated_amount), 0)) as total_balance'),
    //             DB::raw("
    //                 CASE 
    //                     WHEN COALESCE(SUM(bp.allocated_amount), 0) = 0 THEN 'Pending'
    //                     WHEN COALESCE(SUM(bp.allocated_amount), 0) < SUM(COALESCE(CAST(bf.bill_file_amount AS DECIMAL(15,2)),0)) THEN 'Partially Paid'
    //                     WHEN COALESCE(SUM(bp.allocated_amount), 0) >= SUM(COALESCE(CAST(bf.bill_file_amount AS DECIMAL(15,2)),0)) THEN 'Paid'
    //                 END as status
    //             ")
    //         )
    //         ->whereExists(function ($query) {
    //             $query->select(DB::raw(1))
    //                 ->from('bills as bb')
    //                 ->whereRaw('bb.bill_file_id = bf.bill_file_id'); 
    //         })
    //         ->groupBy('h.hospital_name', 'h.hospital_id')
    //         // ->havingRaw('SUM(CAST(bf.bill_file_amount AS DECIMAL(15,2))) = COALESCE(SUM(b.total_amount), 0)')
    //         ->get();

    //     $billFilesArray = $billFiles->map(fn($item) => (array) $item)->toArray();

    //     $grandTotals = [
    //         'hospital_name'           => 'ALL HOSPITALS',
    //         'hospital_id'             => null,
    //         'total_bill_file_amount'  => array_sum(array_column($billFilesArray, 'total_bill_file_amount')),
    //         'total_allocated_amount'  => array_sum(array_column($billFilesArray, 'total_allocated_amount')),
    //         'total_balance'           => array_sum(array_column($billFilesArray, 'total_balance')),
    //         'status'                  => 'Summary'
    //     ];

    //     return response()->json([
    //         'data'       => [
    //             'hospitals' => $billFilesArray,
    //             'totals'    => $grandTotals
    //         ],
    //         'statusCode' => 200
    //     ]);
    // }
}
