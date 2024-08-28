<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\IdentificationsImport;
use App\Models\Identifications;
use Exception;
use Validator;
use DB;

class IdentificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Identification|Create Identification|Update Identification|Update Identification|Delete Identification', ['only' => ['index','create','store','update','destroy']]);
    }

    /**
     * @OA\Get(
     *     path="/api/identifications",
     *     summary="Get a list of identificationss",
     *     tags={"identifications"},
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
    *                     @OA\Property(property="identification_id", type="integer", example=2),
    *                     @OA\Property(property="identification_name", type="string", example="ROLE NATIONAL"),
    *                     @OA\Property(property="first_name", type="string", example="Mohammed"),
    *                     @OA\Property(property="middle_name", type="string", example="Abdalla"),
    *                     @OA\Property(property="last_name", type="string", example="Bakar"),
    *                     @OA\Property(property="created_by", type="integer", example=1),
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
            $identifications = DB::table('identifications')
                                ->join('users', 'users.id', '=', 'identifications.created_by')
                                ->select('identifications.*','users.first_name','users.middle_name','users.last_name','users.id')
                                ->get();

            $respose =[
                'data' => $identifications,
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
     *     path="/api/identifications",
     *     summary="Store a new identifications",
     *     tags={"identifications"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="identification_name", type="string")
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
                $data = Excel::import(new IdentificationsImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Identification Inserted Successfully',
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
        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Identification'))
        {
            $user_id = auth()->user()->id;
    
            $check_value = DB::select("SELECT identification_name FROM identifications WHERE LOWER(identification_name) = LOWER('$request->identification_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Identification Name Alraedy Exists',
                    'statusCode'=> 400
                ];
    
                return response()->json($respose);       
            }

            try{
                $Identifications = Identifications::create([ 
                    'identification_name' => $request->identification_name,
                    'created_by' => $user_id
                ]);
        
                $respose =[
                    'message' =>'Identification Inserted Successfully',
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
     *     path="/api/identifications/{identification_id}",
     *     summary="Get a specific identifications",
     *     tags={"identifications"},
     *     @OA\Parameter(
     *         name="identification_id",
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
    *                     @OA\Property(property="identification_id", type="integer", example=2),
    *                     @OA\Property(property="identification_name", type="string", example="ROLE NATIONAL"),
    *                     @OA\Property(property="first_name", type="string", example="Mohammed"),
    *                     @OA\Property(property="middle_name", type="string", example="Abdalla"),
    *                     @OA\Property(property="last_name", type="string", example="Bakar"),
    *                     @OA\Property(property="created_by", type="integer", example=1),
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
    public function show(string $identification_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $identifications = DB::table('identifications')
                                ->select('identifications.*')
                                ->where('identifications.identification_id', '=',$identification_id)
                                ->get();

            if (sizeof($identifications) > 0) 
            {
                $respose =[
                    'data' => $identifications,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Identification Found','statusCode'=> 400]);
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
     *     path="/api/identifications/{identification_id}",
     *     summary="Update a identifications",
     *     tags={"identifications"},
     *     @OA\Parameter(
     *         name="identification_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="identification_name", type="string")
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
    public function update(Request $request, string $identification_id)
    {
        $check_value = DB::select("SELECT identification_name FROM identifications WHERE LOWER(identification_name) = LOWER('$request->identification_name') and identification_id != $identification_id");

        if(sizeof($check_value) != 0)
        {
            $respose =[
                'message' =>'Identification Name Alraedy Exists',
                'statusCode'=> 400
            ];

            return response()->json($respose);       
        }
        
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Identification'))
        {
            $user_id = auth()->user()->id;
            try{
                $Identifications = Identifications::find($identification_id);
                $Identifications->identification_name  = $request->identification_name;
                $Identifications->created_by = $user_id;
                $Identifications->update();

                $respose =[
                    'message' =>'Identification Updated Successfully',
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
     * @OA\Delete(
     *     path="/api/identifications/{identification_id}",
     *     summary="Delete a identifications",
     *     tags={"identifications"},
     *     @OA\Parameter(
     *         name="identification_id",
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
    *             @OA\Property(property="message", type="string"),
    *             @OA\Property(property="statusCode", type="integer")
    *         )
    *     )
    * )
    */
    public function destroy(string $identification_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Upload Types'))
        {
            $delete = Identifications::find($identification_id);
            if ($delete != null) {
                $delete->delete();
                
                $respose =[
                    'message'=> 'Identification Blocked Successfuly',
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
