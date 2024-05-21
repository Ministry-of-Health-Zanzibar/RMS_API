<?php

namespace App\Http\Controllers\API\Setup;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Http\Request;
use App\Models\ParentUploads;
use App\Models\ParentUploadTypes;
use Exception;
use Validator;
use DB;

class ParentUploadsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('permission:Setup Management|Create Parent Upload Type|Create Parent Upload Type|Update Parent Upload Type|Update Parent Upload Type|Delete Parent Upload Type', ['only' => ['index','create','store','update','destroy']]);

        $validate_batch_year = new GeneralController();
        $validate_batch_year->batch_year_configuration();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Setup Management'))
        {

            $parent_uploads = DB::table('parent_uploads')
                                ->select('parent_uploads.*')
                                ->get();


            $respose =[
                'data' => $parent_uploads,
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $auto_id = random_int(10000, 99999).time();

        $check_value = DB::select("SELECT parent_upload_name FROM parent_uploads t WHERE LOWER(parent_upload_name) = LOWER('$request->parent_upload_name')");


        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Create Parent Upload Type'))
        {

            if(sizeof($check_value) == 0)
            {

                $user_id = auth()->user()->id;

                try{

                    $ParentUploads = ParentUploads::create([
                        'parent_upload_name' => $request->parent_upload_name,
                        'created_by' => $user_id
                    ]);

                    foreach ($request->parent_upload_types as $parent_upload_type) {
                        $ParentUploadTypes = ParentUploadTypes::create([
                            'parent_upload_id' => $ParentUploads->parent_upload_id,
                            'upload_type_id' => $parent_upload_type['upload_type_id'],
                            'created_by' => $user_id
                        ]);
                    }

                    $respose =[
                        'message' =>'Parent Upload Type Inserted Successfully',
                        'statusCode'=> 201
                    ];

                    return response()->json($respose);
                }
                catch (Exception $e)
                {
                    return response()
                        ->json(['message' => $e->getMessage(),'statusCode'=> 401]);
                }

            }else
            {
                $errorResponse = [
                    'message'=>'Parent Upload Name Already Exist',
                    'statusCode'=> 400
                ];

                return response()->json($errorResponse);
            }
        }
        else{
            return response()
                ->json(['message' => 'unAuthenticated','statusCode'=> 401]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $parent_upload_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->can('Setup Management'))
        {
            $parent_uploads = DB::table('parent_uploads')
                                ->select('parent_uploads.*')
                                ->where('parent_upload_id', '=',$parent_upload_id)
                                ->get();

            if (sizeof($parent_uploads) > 0)
            {

                $parent_upload_type = DB::table('parent_upload_types')
                                            ->join('upload_types','upload_types.upload_type_id','=','parent_upload_types.upload_type_id')
                                            ->select('upload_types.upload_type_id','upload_types.upload_name')
                                            ->where('parent_upload_types.parent_upload_id',$parent_upload_id)
                                            ->whereNull('parent_upload_types.deleted_at')
                                            ->get();


                $parent_upload_types = [];
                foreach($parent_upload_type as $item){
                    array_push($parent_upload_types, array(
                        'upload_type_id' => $item->upload_type_id,
                        'upload_name' => $item->upload_name,
                        'isSelected' => true
                    ));
                }

                $respose =[
                    'data' => $parent_uploads,
                    'parent_upload_types' => $parent_upload_types,
                    'statusCode'=> 200
                ];

                return response()->json($respose);

            }else{
                return response()
                ->json(['message' => 'No Parent Upload Type Found','statusCode'=> 400]);
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $parent_upload_id)
    {

        $check_value = DB::select("SELECT parent_upload_name FROM parent_uploads t WHERE LOWER(parent_upload_name) = LOWER('$request->parent_upload_name') and parent_upload_id != $parent_upload_id");

        if(sizeof($check_value) != 0)
        {
            $respose =[
                'message' =>'Parent Upload Type Name Alraedy Exists',
                'statusCode'=> 400
            ];

            return response()->json($respose);       
        }


        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Update Parent Upload Type'))
        {
            $user_id = auth()->user()->id;
            try{
                $ParentUploads = ParentUploads::find($parent_upload_id);
                $ParentUploads->parent_upload_name  = $request->parent_upload_name;
                $ParentUploads->created_by = $user_id;
                $ParentUploads->update();

                $ParentUploadTypes = ParentUploadTypes::withTrashed()->where('parent_upload_id',$parent_upload_id)->get();

                $existingParentUploadTypes = $ParentUploadTypes->pluck('upload_type_id')->toArray();

                $newParentUploadTypes = $request->parent_upload_types;

                for($x = 0; $x < count($newParentUploadTypes); $x++) {
                    
                    if (in_array($newParentUploadTypes[$x]['upload_type_id'], $existingParentUploadTypes)) {
                        ParentUploadTypes::withTrashed()->where('parent_upload_id', $parent_upload_id)->where('upload_type_id', $newParentUploadTypes[$x]['upload_type_id'])->update(['deleted_at' => null]);
                    } else {

                        $ParentUploadTypes = ParentUploadTypes::create([
                            'parent_upload_id' => $parent_upload_id,
                            'upload_type_id' => $newParentUploadTypes[$x]['upload_type_id'],
                            'created_by' => $user_id
                        ]);
                    }
                    
                }

                $newExistingParentUploadTypes = [];

                foreach($newParentUploadTypes as $new_item){
                    $newExistingParentUploadTypes[] = $new_item['upload_type_id'];
                }

                for($x = 0; $x < count($existingParentUploadTypes); $x++) {
                    
                    if (in_array($existingParentUploadTypes[$x], $newExistingParentUploadTypes)) {
                        //skip
                    } else {
                        ParentUploadTypes::where('parent_upload_id', $parent_upload_id)->where('upload_type_id', $existingParentUploadTypes[$x])->update(['deleted_at' => now()]);
                    }
                    
                }
            

                $respose =[
                    'message' =>'Parent Upload Type Updated Successfully',
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
    public function destroy(string $parent_upload_id)
    {
        if(auth()->user()->hasRole('ROLE ADMIN') || auth()->user()->hasRole('ROLE NATIONAL') || auth()->user()->can('Delete Parent Upload Type'))
        {
            $delete = ParentUploads::find($parent_upload_id);
            if ($delete != null) {
                $delete->delete();

                $respose =[
                    'message'=> 'Parent Upload Type Blocked Successfuly',
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
