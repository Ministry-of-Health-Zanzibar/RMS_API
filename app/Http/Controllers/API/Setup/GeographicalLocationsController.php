<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\GeographicalLocationsImport;
use App\Models\GeographicalLocations;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;


class GeographicalLocationsController extends Controller
{ 
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Location|Create Location|Update Location|Update Location|Delete Location', ['only' => ['index','create','store','update','destroy']]);

        // $validate_batch_year = new GeneralController();
        // $validate_batch_year->batch_year_configuration();
    }

    /**
     * @OA\Get(
     *     path="/api/locations",
     *     summary="Get a list of locations",
     *     tags={"locations"},
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
    *                     @OA\Property(property="location_id", type="integer", example=2),
    *                     @OA\Property(property="location_name", type="string", example="ROLE NATIONAL"),
    *                     @OA\Property(property="parent_id", type="string", example="web"),
    *                     @OA\Property(property="label", type="string", example="Kaskazini Unguja REGION,Unguja ISLAND"),
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
        // if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        // {
            $geographicalLocations = DB::table('geographical_locations')
                                ->join('users', 'users.id', '=', 'geographical_locations.created_by')
                                ->select('geographical_locations.*','users.first_name','users.middle_name','users.last_name','users.id')
                                // ->whereNull('geographical_locations.deleted_at')
                                ->get();

            $respose =[
                'data' => $geographicalLocations,
                'statusCode'=> 200
            ];

            return response()->json($respose);
        // }
        // else{
        //     return response()
        //         ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        // }
    }

    /**
     * @OA\Post(
     *     path="/api/locations",
     *     summary="Store a new Geographical Location",
     *     tags={"locations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="location_name", type="string"),
     *             @OA\Property(property="parent_id", type="string"),
     *             @OA\Property(property="label", type="string"),
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
        $auto_id = random_int(100000, 999999).time();
        if((auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL')) && $request->upload_excel){

            $data = Validator::make($request->all(),[
                'upload_excel' => 'mimes:xls,xlsx,csv'
            ]);

            if($data->fails()){
                return response()->json($data->errors());
            }

            try{
                $path = $request->file('upload_excel')->getRealPath();
                $data = Excel::import(new GeographicalLocationsImport,request()->file('upload_excel'));
                $respose =[
                    'message'=> 'Geographical Location Inserted Successfully',
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
        else if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Location'))
        {
            $user_id = auth()->user()->id;

            $check_value = DB::select("SELECT location_name FROM geographical_locations WHERE LOWER(location_name) = LOWER('$request->location_name')");

            if(sizeof($check_value) != 0)
            {
                $respose =[
                    'message' =>'Location Name Alraedy Exists',
                    'statusCode'=> 400
                ];

                return response()->json($respose);
            }

            try{
                $GeographicalLocations = GeographicalLocations::create([
                    'location_id' => $auto_id,
                    'uuid' => Str::uuid(),
                    'location_name' => $request->location_name,
                    'parent_id' => $request->parent_id,
                    'label' => $request->label,
                    'created_by' => $user_id
                ]);

                $respose =[
                    'message' =>'Geographical Location Inserted Successfully',
                    'statusCode'=> 201
                ];

                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 500]);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/locations/{location_id}",
     *     summary="Get a specific Geographical Location",
     *     tags={"locations"},
     *     @OA\Parameter(
     *         name="location_id",
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
    *                     @OA\Property(property="location_id", type="integer", example=2),
    *                     @OA\Property(property="location_name", type="string", example="ROLE NATIONAL"),
    *                     @OA\Property(property="parent_id", type="string", example="web"),
    *                     @OA\Property(property="label", type="string", example="Kaskazini Unguja REGION,Unguja ISLAND"),
    *                     @OA\Property(property="user_id", type="integer", example=1),
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
    public function show(string $location_id)
    {
        // if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        // {
            $geographicalLocations = DB::table('geographical_locations')
                                ->select('geographical_locations.*')
                                ->where('geographical_locations.location_id', '=',$location_id)
                                ->whereNull('geographical_locations.deleted_at')
                                ->get();

            if (sizeof($geographicalLocations) > 0)
            {
                $respose =[
                    'data' => $geographicalLocations,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Geographical Location Found','statusCode'=> 400]);
            }
        // }
        // else{
        //     return response()
        //         ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        // }
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
     *     path="/api/locations/{location_id}",
     *     summary="Update a Geographical Location",
     *     tags={"locations"},
     *     @OA\Parameter(
     *         name="location_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="location_name", type="string"),
     *             @OA\Property(property="parent_id", type="string"),
     *             @OA\Property(property="label", type="string")
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
    public function update(Request $request, string $location_id)
    {
        $check_value = DB::select("SELECT location_name FROM geographical_locations WHERE LOWER(location_name) = LOWER('$request->location_name') and location_id != $location_id");

        if(sizeof($check_value) != 0)
        {
            $respose =[
                'message' =>'Location Name Alraedy Exists',
                'statusCode'=> 400
            ];

            return response()->json($respose);
        }

        // if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Location'))
        // {
            $user_id = auth()->user()->id;
            try{
                $GeographicalLocations = GeographicalLocations::find($location_id);
                $GeographicalLocations->location_name  = $request->location_name;
                $GeographicalLocations->parent_id = $request->parent_id;
                $GeographicalLocations->label = $request->label;
                $GeographicalLocations->created_by = $user_id;
                $GeographicalLocations->update();

                $respose =[
                    'message' =>'Geographical Location Updated Successfully',
                    'statusCode'=> 201
                ];
                return response()->json($respose);
            }
            catch (Exception $e)
            {
                return response()
                    ->json(['message' => $e->getMessage(),'statusCode'=> 500]);
            }
        // }
        // else{
        //     return response()
        //         ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        // }
    }

     /**
     * @OA\Delete(
     *     path="/api/locations/{location_id}",
     *     summary="Delete a Geographical Location",
     *     tags={"locations"},
     *     @OA\Parameter(
     *         name="location_id",
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
    public function destroy(string $location_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Location'))
        {
            $delete = GeographicalLocations::find($location_id);
            if ($delete != null) {
                $delete->delete();

                $respose =[
                    'message'=> 'Geographical Location Blocked Successfully',
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
