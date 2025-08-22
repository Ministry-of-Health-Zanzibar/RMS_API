<?php

namespace App\Http\Controllers\API\Reasons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reason;
use Illuminate\Support\Facades\Auth;

class ReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Reason|Create Reason|View Reason|Update Reason|Delete Reason', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/reasons",
     *     summary="Get all reasons",
     *     tags={"reasons"},
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
     *                     @OA\Property(property="reason_id", type="integer"),
     *                     @OA\Property(property="referral_reason_name", type="string"),
     *                     @OA\Property(property="reason_descriptions", type="string"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF','ROLE DG OFFICER']) || !$user->can('View Reason')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $Reasons = Reason::withTrashed()->get();

        if ($Reasons) {
            return response([
                'data' => $Reasons,
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
    /**
     * @OA\Post(
     *     path="/api/reasons",
     *     summary="Create reasons",
     *     tags={"reasons"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="referral_reason_name", type="string"),
     *                     @OA\Property(property="reason_descriptions", type="string"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create Reason')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_reason_name' => ['required', 'string'],
            'reason_descriptions' => ['nullable', 'string'],
        ]);


        // Create Reason
        $Reason = Reason::create([
            'referral_reason_name' => $data['referral_reason_name'],
            'reason_descriptions' => $data['reason_descriptions'],
            'created_by' => Auth::id(),
            // 'created_by' => auth()->id(),
        ]);

        if ($Reason) {
            return response([
                'data' => $Reason,
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
    /**
     * @OA\Get(
     *     path="/api/reasons/{id}",
     *     summary="Find reason by ID",
     *     tags={"reasons"},
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
     *                 @OA\Property(property="reason_id", type="integer", example=1),
     *                 @OA\Property(property="referral_reason_name", type="string"),
     *                 @OA\Property(property="reason_descriptions", type="string"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create Reason')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $Reason = Reason::withTrashed()->find($id);

        if (!$Reason) {
            return response([
                'message' => 'Reason not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $Reason,
                'statusCode' => 200,
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/reasons/{id}",
     *     summary="Update reason",
     *     tags={"reasons"},
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
     *                     @OA\Property(property="referral_reason_name", type="string"),
     *                     @OA\Property(property="reason_descriptions", type="string" ),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Update Reason')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_reason_name' => ['required', 'string'],
            'reason_descriptions' => ['nullable', 'string'],
        ]);

        $Reason = Reason::find($id);

        if (!$Reason) {
            return response([
                'message' => 'Reason not found',
                'statusCode' => 404,
            ]);
        }


        $Reason->update([
            'referral_reason_name' => $data['referral_reason_name'],
            'reason_descriptions' => $data['reason_descriptions'],
            'created_by' => Auth::id(),
        ]);

        if ($Reason) {
            return response([
                'data' => $Reason,
                'message' => 'Reason updated successfully',
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
     *     path="/api/reasons/{id}",
     *     summary="Delete reason",
     *     tags={"reasons"},
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Delete Reason')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $Reason = Reason::withTrashed()->find($id);

        if (!$Reason) {
            return response([
                'message' => 'Reason not found',
                'statusCode' => 404,
            ]);
        }

        $Reason->delete();

        return response([
            'message' => 'Reason blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/reasons/unblock/{id}",
     *     summary="Unblock Reason",
     *     tags={"reasons"},
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
    public function unBlockReason(int $id)
    {

        $Reason = Reason::withTrashed()->find($id);

        if (!$Reason) {
            return response([
                'message' => 'Reason not found',
                'statusCode' => 404,
            ], 404);
        }

        $Reason->restore($id);

        return response([
            'message' => 'Reason unbocked successfully',
            'statusCode' => 200,
        ], 200);
    }
}
