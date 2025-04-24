<?php

namespace App\Http\Controllers\API\Accountants;

use App\Models\DocumentForm;
use App\Models\DocumentType;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DocumentTypeFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:View Document Form|Create Document Form|View Document Form|Update Document Form|Delete Document Form', ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/documentForms",
     *     summary="Get all document forms",
     *     tags={"documentForms"},
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
     *                     @OA\Property(property="document_form_id", type="integer"),
     *                     @OA\Property(property="document_form_code", type="string"),
     *                     @OA\Property(property="payee_name", type="string"),
     *                     @OA\Property(property="amount", type="integer"),
     *                     @OA\Property(property="tin_number", type="string"),
     *                     @OA\Property(property="year", type="string"),
     *                     @OA\Property(property="document", type="string"),
     *                     @OA\Property(property="documentFileUrl", type="string"),
     *                     @OA\Property(property="source_type_id", type="integer"),
     *                     @OA\Property(property="category_id", type="integer"),
     *                     @OA\Property(property="document_type_id", type="integer"),
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

        // $documentForms = DocumentForm::withTrashed()->get();
        $documentForms = DB::table('document_forms')
            ->join('source_types', 'source_types.source_type_id', '=', 'document_forms.source_type_id')
            ->join('sources', 'sources.source_id', '=', 'source_types.source_id')
            ->join('categories', 'categories.category_id', '=', 'document_forms.category_id')
            ->join('document_types', 'document_types.document_type_id', '=', 'document_forms.document_type_id')
            ->select(
                'document_forms.*',

                'sources.source_id',
                'sources.source_name',
                'sources.source_code',

                'source_types.source_type_name',
                'source_types.source_type_code',

                'categories.category_id',
                'categories.category_name',
                'categories.category_code',

                'document_types.document_type_id',
                'document_types.document_type_name',
                'document_types.document_type_code',
            )
            ->get();

        if ($documentForms) {

            // Append full doc URL
            $documentForms = $documentForms->map(function ($document) {
                $document->documentFileUrl = $document->document_file
                    ? asset('storage/' . $document->document_file)
                    : null;
                return $document;
            });


            return response([
                'data' => $documentForms,
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
     *     path="/api/documentForms",
     *     summary="Create document form",
     *     tags={"documentForms"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *                      @OA\Property(property="payee_name", type="string"),
     *                     @OA\Property(property="amount", type="integer"),
     *                     @OA\Property(property="tin_number", type="string"),
     *                     @OA\Property(property="year", type="string"),
     *                     @OA\Property(property="document_file", type="string"),
     *                     @OA\Property(property="source_type_id", type="integer"),
     *                     @OA\Property(property="category_id", type="integer"),
     *                     @OA\Property(property="document_type_id", type="integer"),
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
            'payee_name' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'tin_number' => ['required', 'string'],
            'year' => ['required', 'string'],
            'source_type_id' => ['required', 'numeric'],
            'category_id' => ['required', 'numeric'],
            'document_type_id' => ['required', 'numeric'],
            'document_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:5120'],
        ]);


        // Only handle the file after validation passes
        $path = null;
        if (isset($data['document_file'])) {
            $path = $data['document_file']->store('accountant_documents', 'public');
        }


        // Create
        $documentForm = DocumentForm::create([
            'payee_name' => $data['payee_name'],
            'amount' => $data['amount'],
            'tin_number' => $data['tin_number'],
            'year' => $data['year'],
            'document_file' => $path,
            'source_type_id' => $data['source_type_id'],
            'category_id' => $data['category_id'],
            'document_type_id' => $data['document_type_id'],
            'created_by' => Auth::id(),
        ]);

        if ($documentForm) {
            return response([
                'data' => $documentForm,
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
     *     path="/api/documentForms/{documentFormId}",
     *     summary="Find document form",
     *     tags={"documentForms"},
     *     @OA\Parameter(
     *         name="documentFormId",
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
     *                 @OA\Property(property="document_form_id", type="integer"),
     *                     @OA\Property(property="document_form_code", type="string"),
     *                     @OA\Property(property="payee_name", type="string"),
     *                     @OA\Property(property="amount", type="integer"),
     *                     @OA\Property(property="tin_number", type="string"),
     *                     @OA\Property(property="year", type="string"),
     *                     @OA\Property(property="documentFileUrl", type="string"),
     *                     @OA\Property(property="source_type_id", type="integer"),
     *                     @OA\Property(property="category_id", type="integer"),
     *                     @OA\Property(property="document_type_id", type="integer"),
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

        // $documentForm = DocumentForm::withTrashed()->find($id);
        $documentForm = DB::table('document_forms')
            ->join('source_types', 'source_types.source_type_id', '=', 'document_forms.source_type_id')
            ->join('sources', 'sources.source_id', '=', 'source_types.source_id')
            ->join('categories', 'categories.category_id', '=', 'document_forms.category_id')
            ->join('document_types', 'document_types.document_type_id', '=', 'document_forms.document_type_id')
            ->select(
                'document_forms.*',

                'sources.source_id',
                'sources.source_name',
                'sources.source_code',

                'source_types.source_type_name',
                'source_types.source_type_code',

                'categories.category_id',
                'categories.category_name',
                'categories.category_code',

                'document_types.document_type_id',
                'document_types.document_type_name',
                'document_types.document_type_code',
            )
            ->where('document_forms.document_form_id', '=', $id)
            ->first();


        if (!$documentForm) {
            return response([
                'message' => 'DocumentForm not found',
                'statusCode' => 404,
            ]);
        } else {

            // Append full doc URL
            if ($documentForm->document_file) {
                $documentForm->documentFileUrl = asset('storage/' . $documentForm->document_file);
            } else {
                $documentForm->documentFileUrl = null;
            }
            return response([
                'data' => $documentForm,
                'statusCode' => 200,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/documentForms/update/{documentFormId}",
     *     summary="Update document form",
     *     tags={"documentForms"},
     *      @OA\Parameter(
     *         name="documentFormId",
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
     *                     @OA\Property(property="payee_name", type="string"),
     *                     @OA\Property(property="amount", type="integer"),
     *                     @OA\Property(property="tin_number", type="string"),
     *                     @OA\Property(property="year", type="string"),
     *                     @OA\Property(property="document_file", type="string"),
     *                     @OA\Property(property="source_type_id", type="integer"),
     *                     @OA\Property(property="category_id", type="integer"),
     *                     @OA\Property(property="document_type_id", type="integer"),
     *                 ),
     *             ),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     )
     * )
     */
    public function updateDocumentForm(Request $request, string $id)
    {
        $user = auth()->user();
        if (!$user->hasRole('ROLE ACCOUNTANT') || !$user->can('Update Document Type')) {
            return response([
                'message' => 'Forbidden',
                'statusCode' => 403
            ], 403);
        }

        $data = $request->validate([
            'payee_name' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'tin_number' => ['required', 'string'],
            'year' => ['required', 'string'],
            'document_file' => ['nullable', 'string'],
            'source_type_id' => ['required', 'numeric'],
            'category_id' => ['required', 'numeric'],
            'document_type_id' => ['required', 'numeric'],
        ]);

        $documentForm = DocumentForm::find($id);

        if (!$documentForm) {
            return response([
                'message' => 'DocumentType not found',
                'statusCode' => 404,
            ]);
        }



        // Handle file upload if provided
        if ($request->hasFile('document_file')) {
            $path = $request->file('document_file')->store('accountant_documents', 'public');
            $data['document_file'] = $path;
        } else {
            unset($data['document_file']);
        }

        $data['created_by'] = Auth::id();

        $documentForm->update($data);

        if ($documentForm) {
            return response([
                'data' => $documentForm,
                'message' => 'DocumentForm updated successfully',
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
     *     path="/api/documentForms/{documentFormId}",
     *     summary="Delete document form",
     *     tags={"documentForms"},
     *     @OA\Parameter(
     *         name="documentFormId",
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
     *         ),
     *     ),
     * ),
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

        $documentForm = DocumentForm::withTrashed()->find($id);

        if (!$documentForm) {
            return response([
                'message' => 'DocumentForm not found',
                'statusCode' => 404,
            ]);
        }

        $documentForm->delete();

        return response([
            'message' => 'DocumentForm blocked successfully',
            'statusCode' => 200,
        ], 200);

    }


    /**
     * Unblock
     */
    /**
     * @OA\Patch(
     *     path="/api/documentForms/unBlock/{documentFormId}",
     *     summary="Unblock document form",
     *     tags={"documentForms"},
     *     @OA\Parameter(
     *         name="documentFormId",
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
    public function unBlockDocumentForm(int $id)
    {

        $documentForm = DocumentForm::withTrashed()->find($id);

        if (!$documentForm) {
            return response([
                'message' => 'DocumentForm not found',
                'statusCode' => 404,
            ], 404);
        }

        $documentForm->restore($id);

        return response([
            'message' => 'DocumentForm unblocked successfully',
            'statusCode' => 200,
        ], 200);
    }
}