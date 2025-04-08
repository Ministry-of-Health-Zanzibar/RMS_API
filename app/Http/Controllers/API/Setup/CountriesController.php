<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use App\Models\Countries;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;


class CountriesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Delete Country', ['only' => ['index','destroy']]);
    }

    /**
     * @OA\Get(
     *     path="/api/countries",
     *     summary="Get a list of countriess",
     *     tags={"countries"},
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
    *                     @OA\Property(property="country_id", type="integer", example=2),
    *                     @OA\Property(property="country_code", type="string", example="ROLE NATIONAL"),
    *                     @OA\Property(property="country_name", type="string", example="Mohammed"),
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
        //
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {
            $countries = DB::table('countries')
                                ->join('users', 'users.id', '=', 'countries.created_by')
                                ->select('countries.*','users.first_name','users.middle_name','users.last_name','users.id')
                                ->get();

            $respose =[
                'data' => $countries,
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


    public function store(Request $request)
    {
        //
    }


    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }

     /**
     * @OA\Delete(
     *     path="/api/countries/{country_id}",
     *     summary="Delete a countries",
     *     tags={"countries"},
     *     @OA\Parameter(
     *         name="country_id",
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
    public function destroy(string $country_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->can('Delete Country'))
        {
            $delete = Countries::find($country_id);
            if ($delete != null) {
                $delete->delete();

                $respose =[
                    'message'=>'Data deleted successfuly',
                    'statusCode'=>'200'
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
