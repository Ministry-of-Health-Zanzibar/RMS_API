<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UploadTypesImport;
use App\Models\UploadTypes;
use Exception;
use Validator;
use DB;

class UploadTypesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Upload Types|Create Upload Types|Update Upload Types|Update Upload Types|Delete Upload Types', ['only' => ['index','create','store','update','destroy']]);
    }

    /**
     * @OA\Get(
     *     path="/api/uploadTypes",
     *     summary="Get a list of uploadTypess",
     *     tags={"uploadTypes"},
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
    *                     @OA\Property(property="upload_type_id", type="integer", example=2),
    *                     @OA\Property(property="upload_name", type="string", example="ROLE NATIONAL"),
    *                     @OA\Property(property="created_by", type="integer", example=1),
    *                     @OA\Property(property="first_name", type="string", example="Mohammed"),
    *                     @OA\Property(property="middle_name", type="string", example="Abdalla"),
    *                     @OA\Property(property="last_name", type="string", example="Bakar"),
    *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
    *                 )
    *             ),
    *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $uploadType = DB::table('upload_types')
                                ->join('users', 'users.id', '=', 'upload_types.created_by')
                                ->select('upload_types.*','users.first_name','users.middle_name','users.last_name','users.id')
                                ->get();

            $uploadTypes = [];
            foreach($uploadType as $item){
                array_push($uploadTypes, array(
                    'upload_type_id' => $item->upload_type_id,
                    'upload_name' => $item->upload_name,
                    'created_by' => $item->created_by,
                    'deleted_at' => $item->deleted_at,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'isSelected' => false
                ));
            }

            $respose =[
                'data' => $uploadTypes,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * @OA\Post(
     *     path="/api/uploadTypes",
     *     summary="Store a new uploadTypes",
     *     tags={"uploadTypes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="upload_name", type="string"),
     *             @OA\Property(property="upload_excel", type="string", format="binary")
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
        $auto_id = random_int(10000, 99999).time();
        if((auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL')) && $request->upload_excel){

            // $data = Validator::make($request->all(),[
            //     'upload_excel' => 'mimes:xls,xlsx,csv'
            // ]);

            // if($data->fails()){
            //     return response()->json($data->errors());       
            // }
            
            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new UploadTypesImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Upload Type Inserted Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->failures(),'statusCode'=> 401]);
            }
            
        }
        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Upload Types'))
        {
            $user_id = auth()->user()->id;
    
            // $data = Validator::make($request->all(),[
            //     'upload_name' => 'required|unique:upload_types',
            // ]);

            // if($data->fails()){
            //     return response()->json($data->errors());       
            // }

            try{
                $UploadTypes = UploadTypes::create([ 
                    'upload_name' => $request->upload_name,
                    'created_by' => $user_id
                ]);
        
                $respose =[
                    'message' =>'Upload Type Inserted Successfully',
                    'statusCode'=> 201
                ];
        
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 401]);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

   /**
     * @OA\Get(
     *     path="/api/uploadTypes/{upload_type_id}",
     *     summary="Get a specific uploadTypes",
     *     tags={"uploadTypes"},
     *     @OA\Parameter(
     *         name="upload_type_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
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
    *             @OA\Property(
    *                 property="data",
    *                 type="array",
    *                 @OA\Items(
    *                     type="object",
    *                     @OA\Property(property="upload_type_id", type="integer", example=2),
    *                     @OA\Property(property="upload_name", type="string", example="ROLE NATIONAL"),
    *                     @OA\Property(property="created_by", type="integer", example=1),
    *                     @OA\Property(property="first_name", type="string", example="Mohammed"),
    *                     @OA\Property(property="middle_name", type="string", example="Abdalla"),
    *                     @OA\Property(property="last_name", type="string", example="Bakar"),
    *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="deleted_at", type="string", format="date-time", example="2024-08-28 11:30:25"),
    *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-08-28 11:30:25")
    *                 )
    *             ),
    *             @OA\Property(property="statusCode", type="integer", example=200)
    *         )
    *     )
    * )
    */
    public function show(string $upload_type_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $uploadTypes = DB::table('upload_types')
                                ->select('upload_types.*')
                                ->where('upload_types.upload_type_id', '=',$upload_type_id)
                                ->get();

            if (sizeof($uploadTypes) > 0) 
            {
                $respose =[
                    'data' => $uploadTypes,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Upload Type Found','statusCode'=> 400]);
            }
                
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

/**
     * @OA\Put(
     *     path="/api/uploadTypes/{upload_type_id}",
     *     summary="Update a uploadTypes",
     *     tags={"uploadTypes"},
     *     @OA\Parameter(
     *         name="upload_type_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="upload_name", type="string")
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
    public function update(Request $request, string $upload_type_id)
    {
        // $data = Validator::make($request->all(),[
        //     'upload_name' => 'required|unique:upload_types',
        // ]);

        // if($data->fails()){
        //     return response()->json($data->errors());       
        // }
        
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Upload Types'))
        {
            $user_id = auth()->user()->id;
            try{
                $UploadTypes = UploadTypes::find($upload_type_id);
                $UploadTypes->upload_name  = $request->upload_name;
                $UploadTypes->created_by = $user_id;
                $UploadTypes->update();

                $respose =[
                    'message' =>'Upload Type Updated Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 401]);
            }
        }  
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        } 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $upload_type_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Upload Types'))
        {
            $delete = UploadTypes::find($upload_type_id);
            if ($delete != null) {
                $delete->delete();
                
                $respose =[
                    'message'=> 'Upload Type Blocked Successfuly',
                    'statusCode'=> 201
                ];
                return response()->json($respose); 
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }


}
