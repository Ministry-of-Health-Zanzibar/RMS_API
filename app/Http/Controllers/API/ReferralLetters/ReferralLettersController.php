<?php

namespace App\Http\Controllers\API\ReferralLetters;

use App\Models\Referral;
use App\Models\ReferralType;
use Illuminate\Http\Request;
use App\Models\ReferralLetter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReferralLettersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View ReferralLetter|Create ReferralLetter|View ReferralLetter|Update ReferralLetter|Delete ReferralLetter', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/referralLetters",
     *     summary="Get all referralLetters",
     *     tags={"referralLetters"},
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
     *                     @OA\Property(property="referral_letter_id", type="integer"),
     *                     @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="referral_letter_code", type="string"),
     *                     @OA\Property(property="letter_text", type="string"),
     *                     @OA\Property(property="is_printed", type="boolean"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('View ReferralLetter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $Referral_letter = ReferralLetter::withTrashed()->get();

        if ($Referral_letter) {
            return response([
                'data' => $Referral_letter,
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
     *     path="/api/referralLetters",
     *     summary="Create referralLetters",
     *     tags={"referralLetters"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *            @OA\Property(property="referral_id", type="integer"),
     *            @OA\Property(property="letter_text", type="string"),
     *            @OA\Property(property="start_date", type="string", format="date-time"),
     *            @OA\Property(property="end_date", type="string", format="date-time"),
     *            @OA\Property(property="status", type="string"),
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
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="statusCode", type="integer")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create ReferralLetter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_id' => ['required', 'numeric'],
            'letter_text' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
            'status' => ['required', 'string'],
        ]);

        $referral = Referral::findOrFail($data['referral_id']);


        // Create referral Type
        $ReferralType = ReferralLetter::create([
            'referral_id' => $data['referral_id'],
            'letter_text' => $data['letter_text'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'created_by' => Auth::id(),
        ]);

        if ($ReferralType) {
            $referral->update([
                'status' => $data['status'],
            ]);

            if ($referral) {
                return response([
                    'data' => $ReferralType,
                    'message' => "Referral type created successfully.",
                    'statusCode' => 201,
                ], status: 201);
            } else {
                return response([
                    'message' => 'Failed to update referral status.',
                    'statusCode' => 500,
                ], 500);
            }
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
     *     path="/api/referralLetters/{referralLetters_id}",
     *     summary="Find referral Letters by ID",
     *     tags={"referralLetters"},
     *     @OA\Parameter(
     *         name="referralLetters_id",
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
     *                 @OA\Property(property="referral_letter_id", type="integer"),
     *                     @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="referral_letter_code", type="string"),
     *                     @OA\Property(property="letter_text", type="string"),
     *                     @OA\Property(property="is_printed", type="boolean"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */

    public function show(string $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('View ReferralLetter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $Referral_letter = ReferralLetter::withTrashed()->find($id);

        if (!$Referral_letter) {
            return response([
                'message' => 'Referral letter not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $Referral_letter,
                'statusCode' => 200,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/referralLetters/{referralLetters_id}",
     *     summary="Update referralLetters",
     *     tags={"referralLetters"},
     *      @OA\Parameter(
     *         name="referralLetters_id",
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
     *                    @OA\Property(property="referral_id", type="integer"),
     *                     @OA\Property(property="letter_text", type="string"),
     *                     @OA\Property(property="is_printed", type="boolean"),
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
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Create ReferralLetter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'referral_id' => ['required', 'numeric'],
            'letter_text' => ['required', 'text'],
            'is_printed' => ['required'],
        ]);


        // Update referral letter
        $Referral_letter = ReferralLetter::findOrFail($id);
        $Referral_letter->update([
            'referral_id' => $data['referral_id'],
            'letter_text' => $data['letter_text'],
            'is_printed' => $data['is_printed'],
            'created_by' => Auth::id(),
        ]);

        if ($Referral_letter) {
            return response([
                'data' => $Referral_letter,
                'message' => 'Referral_letter updated successfully',
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
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/referralLetters/{referralLetters_id}",
     *     summary="Delete referralLetters",
     *     tags={"referralLetters"},
     *     @OA\Parameter(
     *         name="referralLetters_id",
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

    public function destroy(string $id)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['ROLE ADMIN', 'ROLE NATIONAL','ROLE STAFF']) || !$user->can('Delete ReferralLetter')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $Referral_letter = ReferralLetter::withTrashed()->find($id);

        if (!$Referral_letter) {
            return response([
                'message' => 'Referral letter not found',
                'statusCode' => 404,
            ]);
        }

        $Referral_letter->delete();

        return response([
            'message' => 'Referral_letter blocked successfully',
            'statusCode' => 200,
        ], 200);
    }

    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/referralLetters/unBlock/{Referral_letter_id}",
     *     summary="Unblock referralLetters",
     *     tags={"referralLetters"},
     *     @OA\Parameter(
     *         name="referralLetters_id",
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

    public function unBlockReferralLetter(int $id)
    {

        $Referral_letter = ReferralLetter::withTrashed()->find($id);

        if (!$Referral_letter) {
            return response([
                'message' => 'Referral letter not found',
                'statusCode' => 404,
            ], 404);
        }

        $Referral_letter->restore($id);

        return response([
            'message' => 'Referral_letter unblocked successfully',
            'statusCode' => 200,
        ], 200);
    }
}