<?php

namespace App\Http\Controllers\API\ReferralType;

use App\Http\Controllers\Controller;
use App\Models\ReferralType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View ReferralType|Create ReferralType|Update ReferralType|Delete ReferralType', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    /** 
     * @OA\Get(
     *     path="/api/referralTypes",
     *     summary="Get all referral types",
     *     tags={"referralTypes"},
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
     *                     @OA\Property(property="referral_type_id", type="integer"),
     *                     @OA\Property(property="referral_type_code", type="string"),
     *                     @OA\Property(property="referral_type_name", type="string"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('View ReferralType')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }else{

        $ReferralType = ReferralType::withTrashed()->get();
        if ($ReferralType) {
            return response([
                'data' => $ReferralType,
                'statusCode' => 200,
            ], 200);
        } else {
            return response([
                'message' => 'No data found',
                'statusCode' => 500,
            ], 500);
        }
    }
}
    
    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/referralTypes",
     *     summary="Create referral type",
     *     tags={"referralTypes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="referral_type_name", type="string"),
     *         ),
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
     *             @OA\Property(property="message", type="string", example="Referral type created successfully."),
     *             @OA\Property(property="statusCode", type="integer", example="201")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Create ReferralType')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_type_name' => ['required', 'string'],
        ]);


        // Create referral Type
        $ReferralType = ReferralType::create([
            'referral_type_name' => $data['referral_type_name'],
            'created_by' => Auth::id(),
            // 'created_by' => auth()->id(),
        ]);

        if ($ReferralType) {
            return response([
                'data' => $ReferralType,
                'message' => "Referral type created successfully.",
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
     *     path="/api/referralTypes/{id}",
     *     summary="Find Referal type by ID",
     *     tags={"referralTypes"},
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
     *                 @OA\Property(property="referral_type_id", type="integer", example=1),
     *                 @OA\Property(property="referral_type_code", type="string", example="REFTYPE1"),
     *                 @OA\Property(property="referral_type_name", type="string", example="MAINLAND"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('View ReferralType')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $ReferralType = ReferralType::withTrashed()->find($id);

        if (!$ReferralType) {
            return response([
                'message' => 'Referral Type not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $ReferralType,
                'statusCode' => 200,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/referralTypes/{id}",
     *     summary="Update referral type",
     *     tags={"referralTypes"},
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
     *                     @OA\Property(property="referral_type_name", type="string"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Update ReferralType')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_type_name' => ['required', 'string'],
        ]);

        $ReferralType = ReferralType::find($id);

        if (!$ReferralType) {
            return response([
                'message' => 'Referral Type not found',
                'statusCode' => 404,
            ]);
        }


        $ReferralType->update([
            'referral_type_name' => $data['referral_type_name'],
            'created_by' => Auth::id(),
        ]);

        if ($ReferralType) {
            return response([
                'data' => $ReferralType,
                'message' => 'Referral Type updated successfully',
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
     *     path="/api/referralTypes/{id}",
     *     summary="Delete Referral type",
     *     tags={"referralTypes"},
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL']) || !$user->can('Delete ReferralType')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $ReferralType = ReferralType::withTrashed()->find($id);

        if (!$ReferralType) {
            return response([
                'message' => 'Referral Type not found',
                'statusCode' => 404,
            ]);
        }else{

        $ReferralType->delete();
        return response([
            'message' => 'Referral Type Delete successfully',
            'statusCode' => 200,
        ], 200);
    }
}

    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/referralTypes/unblock/{referralTypeId}",
     *     summary="Unblock ReferralType",
     *     tags={"referralTypes"},
     *     @OA\Parameter(
     *         name="referralTypeId",
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
    public function unBlockReferralType(int $id)
    {

        $ReferralType = ReferralType::withTrashed()->find($id);

        if (!$ReferralType) {
            return response([
                'message' => 'ReferralType not found',
                'statusCode' => 404,
            ], 404);
        }

        $ReferralType->restore($id);

        return response([
            'message' => 'ReferralType unbocked successfully',
            'statusCode' => 200,
        ], 200);
    }
}
