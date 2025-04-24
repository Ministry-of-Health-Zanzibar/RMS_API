<?php

namespace App\Http\Controllers\API\Accountants;

use App\Models\Hospital;
use App\Models\SourceType;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use function Laravel\Prompts\select;

class SourceTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Source Type|Create Source Type|View Source Type|Update Source Type|Delete Source Type', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/sourceTypes",
     *     summary="Get all source types",
     *     tags={"sourceTypes"},
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
     *                     @OA\Property(property="source_type_id", type="integer"),
     *                     @OA\Property(property="source_type_code", type="string"),
     *                     @OA\Property(property="source_type_name", type="string"),
     *                     @OA\Property(property="source_id", type="integer"),
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Source Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // $sourceTypes = SourceType::withTrashed()->get();
        $sourceTypes = DB::table('source_types')
            ->join('sources', 'source_types.source_id', '=', 'sources.source_id')
            ->select(
                'source_types.*',
                'sources.source_name'
            )
            ->get();


        if ($sourceTypes) {
            return response([
                'data' => $sourceTypes,
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
     *     path="/api/sourceTypes",
     *     summary="Create source type",
     *     tags={"sourceTypes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *                      @OA\Property(property="source_type_name", type="string"),
     *                     @OA\Property(property="source_id", type="integer"),
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('Create Source Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'source_type_name' => ['required', 'string'],
            'source_id' => ['required', 'numeric'],
        ]);


        // Create
        $sourceType = SourceType::create([
            'source_type_name' => $data['source_type_name'],
            'source_id' => $data['source_id'],
            'created_by' => Auth::id(),
        ]);

        if ($sourceType) {
            return response([
                'data' => $sourceType,
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
     *     path="/api/sourceTypes/{sourceTypeId}",
     *     summary="Find source type",
     *     tags={"sourceTypes"},
     *     @OA\Parameter(
     *         name="sourceTypeId",
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
     *                 @OA\Property(property="source_type_id", type="integer", example=1),
     *                 @OA\Property(property="source_type_code", type="string"),
     *                 @OA\Property(property="source_type_name", type="string"),
     *                 @OA\Property(property="source_id", type="integer"),
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Source Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        // $sourceType = SourceType::withTrashed()->find($id);
        $sourceType = DB::table('source_types')
            ->join('sources', 'source_types.source_id', '=', 'sources.source_id')
            ->select(
                'source_types.*',
                'sources.source_name'
            )
            ->first();

        if (!$sourceType) {
            return response([
                'message' => 'SourceType not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $sourceType,
                'statusCode' => 200,
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/sourceTypes/{id}",
     *     summary="Update source type",
     *     tags={"sourceTypes"},
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
     *                 @OA\Property(property="source_type_name", type="string"),
     *                 @OA\Property(property="source_id", type="integer" ),
     *                 ),
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('Update Source Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'source_type_name' => ['required', 'string'],
            'source_id' => ['nullable', 'numeric'],
        ]);

        $sourceType = SourceType::find($id);

        if (!$sourceType) {
            return response([
                'message' => 'SourceType not found',
                'statusCode' => 404,
            ]);
        }


        $sourceType->update([
            'source_type_name' => $data['source_type_name'],
            'source_id' => $data['source_id'],
            'created_by' => Auth::id(),
        ]);

        if ($sourceType) {
            return response([
                'data' => $sourceType,
                'message' => 'Source Type updated successfully',
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
     *     path="/api/sourceTypes/{sourceTypeId}",
     *     summary="Delete source type",
     *     tags={"sourceTypes"},
     *     @OA\Parameter(
     *         name="sourceTypeId",
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('Delete Source Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $sourceType = SourceType::withTrashed()->find($id);

        if (!$sourceType) {
            return response([
                'message' => 'SourceType not found',
                'statusCode' => 404,
            ]);
        }

        $sourceType->delete();

        return response([
            'message' => 'SourceType blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/sourceTypes/unBlock/{sourceTypeId}",
     *     summary="Unblock source type",
     *     tags={"sourceTypes"},
     *     @OA\Parameter(
     *         name="sourceTypeId",
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
    public function unBlockSourceType(int $id)
    {

        $sourceType = SourceType::withTrashed()->find($id);

        if (!$sourceType) {
            return response([
                'message' => 'Source type not found',
                'statusCode' => 404,
            ], 404);
        }

        $sourceType->restore($id);

        return response([
            'message' => 'Source type unblocked successfully',
            'statusCode' => 200,
        ], 200);
    }
}