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
     *     path="/api/ReferralType/unblock/{id}",
     *     summary="Unblock ReferralType",
     *     tags={"referral_types"},
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
