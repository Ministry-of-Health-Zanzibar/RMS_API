<?php

namespace App\Http\Controllers\API\BillFiles;

use App\Http\Controllers\Controller;
use App\Models\BillFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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

        // Check user role and permission
        if (!$user->hasAnyRole(['ROLE ADMIN','ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create BillFile')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'bill_file'        => ['required','file','mimes:pdf,jpg,jpeg,png','max:2048'],
            'bill_file_amount' => ['required','string'],
            'bill_start'       => ['nullable','string'],
            'bill_end'         => ['nullable','string'],
            'hospital_id'      => ['required', 'numeric']
        ]);

        if ($request->hasFile('bill_file')) {

            // Get the uploaded file
            $file = $request->file('bill_file');

            // Extract the file extension (pdf, jpg, etc.)
            $extension = $file->getClientOriginalExtension();

            // Generate a custom file name: bill_file_1694791234.pdf
            $newFileName = 'bill_file_' . time() . '.' . $extension;

            // Move the file to public/uploads/billFiles/
            $file->move(public_path('uploads/billFiles/'), $newFileName);

            $validated['bill_file'] = $newFileName;
        }

        // Add created_by field
        $validated['created_by'] = $user->id;

        // Create the BillFile record
        $billFile = BillFile::create($validated);

        return response()->json([
            'message'    => 'Bill file created successfully',
            'data'       => $billFile,
            'statusCode' => 201
        ]);
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
            'bill_file' => ['sometimes','file','mimes:pdf,jpg,jpeg,png','max:2048'],
            'bill_file_amount' => ['sometimes','string'],
            'bill_start' => ['nullable','string'],
            'bill_end' => ['nullable','string'],
            'hospital_id' => ['required', 'numeric']
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

        // $billFiles = DB::table('bill_files as bf')
        // ->leftJoin('hospitals as h', 'bf.hospital_id', '=', 'h.hospital_id')
        // ->leftJoin('bills as b', 'bf.bill_file_id', '=', 'b.bill_file_id')
        // ->leftJoin('bill_payments as bp', 'b.bill_id', '=', 'bp.bill_id')
        // ->leftJoin('payments as p', 'bp.payment_id', '=', 'p.payment_id')
        // ->select(
        //     'bf.bill_file_id',
        //     'h.hospital_name',
        //     'bf.bill_file',
        //     'bf.bill_start',
        //     'bf.bill_end',
        //     DB::raw('CAST(bf.bill_file_amount AS DECIMAL(15,2)) as bill_file_amount'),
        //     DB::raw('COALESCE(SUM(bp.allocated_amount), 0) as paid_amount'),
        //     DB::raw('(CAST(bf.bill_file_amount AS DECIMAL(15,2)) - COALESCE(SUM(bp.allocated_amount), 0)) as balance'),
        //     DB::raw("
        //         CASE 
        //             WHEN COALESCE(SUM(bp.allocated_amount), 0) >= CAST(bf.bill_file_amount AS DECIMAL(15,2)) THEN 'Paid'
        //             ELSE 'Pending'
        //         END as status
        //     ")
        // )
        // ->groupBy(
        //     'bf.bill_file_id',
        //     'h.hospital_name',
        //     'bf.bill_file',
        //     'bf.bill_file_amount'
        // )
        // ->havingRaw('CAST(bf.bill_file_amount AS DECIMAL(15,2)) = COALESCE(SUM(b.total_amount), 0)')
        // ->get();
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

}
