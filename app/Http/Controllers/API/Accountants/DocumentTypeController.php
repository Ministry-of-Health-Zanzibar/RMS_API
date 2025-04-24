<?php

namespace App\Http\Controllers\API\Accountants;

use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DocumentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Document Type|Create Document Type|View Document Type|Update Document Type|Delete Document Type', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/documentTypes",
     *     summary="Get all document types",
     *     tags={"documentTypes"},
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
     *                     @OA\Property(property="document_type_id", type="integer"),
     *                     @OA\Property(property="document_type_code", type="string"),
     *                     @OA\Property(property="document_type_name", type="string"),
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Document Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $documentTypes = DocumentType::withTrashed()->get();

        if ($documentTypes) {
            return response([
                'data' => $documentTypes,
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
     *     path="/api/documentTypes",
     *     summary="Create document type",
     *     tags={"documentTypes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *                      @OA\Property(property="document_type_name", type="string"),
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('Create Document Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'document_type_name' => ['required', 'string'],
        ]);


        // Create
        $documentType = DocumentType::create([
            'document_type_name' => $data['document_type_name'],
            'created_by' => Auth::id(),
        ]);

        if ($documentType) {
            return response([
                'data' => $documentType,
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
     *     path="/api/documentTypes/{documentTypeId}",
     *     summary="Find document type",
     *     tags={"documentTypes"},
     *     @OA\Parameter(
     *         name="documentTypeId",
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
     *                 @OA\Property(property="document_type_id", type="integer", example=1),
     *                 @OA\Property(property="document_type_code", type="string"),
     *                 @OA\Property(property="document_type_name", type="string"),
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('View Document Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $documentType = DocumentType::withTrashed()->find($id);

        if (!$documentType) {
            return response([
                'message' => 'DocumentType not found',
                'statusCode' => 404,
            ]);
        } else {
            return response([
                'data' => $documentType,
                'statusCode' => 200,
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/documentTypes/{documentTypeId}",
     *     summary="Update document type",
     *     tags={"documentTypes"},
     *      @OA\Parameter(
     *         name="documentTypeId",
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
     *                     @OA\Property(property="document_type_name", type="string"),
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('Update Document Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'document_type_name' => ['required', 'string'],
        ]);

        $documentType = DocumentType::find($id);

        if (!$documentType) {
            return response([
                'message' => 'DocumentType not found',
                'statusCode' => 404,
            ]);
        }


        $documentType->update([
            'document_type_name' => $data['document_type_name'],
            'created_by' => Auth::id(),
        ]);

        if ($documentType) {
            return response([
                'data' => $documentType,
                'message' => 'DocumentType updated successfully',
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
     *     path="/api/documentTypes/{documentTypeId}",
     *     summary="Delete document type",
     *     tags={"documentTypes"},
     *     @OA\Parameter(
     *         name="documentTypeId",
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
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('Delete Document Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $documentType = DocumentType::withTrashed()->find($id);

        if (!$documentType) {
            return response([
                'message' => 'DocumentType not found',
                'statusCode' => 404,
            ]);
        }

        $documentType->delete();

        return response([
            'message' => 'DocumentType blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/documentTypes/unBlock/{documentTypeId}",
     *     summary="Unblock document type",
     *     tags={"documentTypes"},
     *     @OA\Parameter(
     *         name="documentTypeId",
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
     *         ),
     *     ),
     * ),
     */
    public function unBlockDocumentType(int $id)
    {

        $documentType = DocumentType::withTrashed()->find($id);

        if (!$documentType) {
            return response([
                'message' => 'DocumentType not found',
                'statusCode' => 404,
            ], 404);
        }

        $documentType->restore($id);

        return response([
            'message' => 'DocumentType unblocked successfully',
            'statusCode' => 200,
        ], 200);
    }
}