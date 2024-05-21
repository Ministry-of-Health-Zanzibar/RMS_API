<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Setup\GeneralController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Validator;
use DB;

class UserProfileCotroller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        //$this->middleware('permission:Setup Management|Create Upload Types|Create Upload Types|Update Upload Types|Update Upload Types|Delete Upload Types', ['only' => ['index','create','store','update','destroy']]);

        $validate_batch_year = new GeneralController();
        $validate_batch_year->batch_year_configuration();
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $login_details = $this->check_login();
        if(sizeof($login_details) == 0)
        {
            $successResponse = [
                'message'=>'Password already Changed',
                'status' => '201'
            ];

            return response()->json($successResponse);
        }
        else
        {
            $errorResponse = [
                'message'=>'Password not Changed, Please change the Password',
                'status' => '400'
            ];
            return response()->json($errorResponse);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function check_login() 
    {
        $user_id = auth()->user()->id;

        $staffs = DB::table('users')
                  ->select('users.*')
                  ->where('users.login_status','!=',1)
                  ->where('users.id','=',$user_id)
                  ->get();
  
        return $staffs; //turn the array into a string
    }

    /**
     * Store a newly created resource in storage.
     */
    public function change_password(Request $request)
    {
        $id = auth()->user()->id;
        $new_password = $request->new_password;
        $password_confirmation = $request->password_confirmation;
        try
        {
            $users = User::find($id);
            if(Hash::check($request->old_password, $users->password))
            {
                if ($password_confirmation == $new_password){
                    //$users = User::find($id);
                    $users->password = bcrypt($new_password);
                    $users->login_status = 1;
                    $users->update();
    
                    $successResponse = [
                        'message'=>'Change Password Successfuly',
                        'statusCode'=>201
                    ];
    
                    return response()->json($successResponse); 
                }else
                {
                    $errorResponse = [
                        'message'=>'New Password and Confirm Password not Match',
                        'statusCode'=>400
                    ];
        
                    return response()->json($errorResponse);
                } 
            }
            else{
                $errorResponse = [
                    'message'=>'Invalid Old Password',
                    'statusCode'=>400
                ];
    
                return response()->json($errorResponse);  
            }
        }
        catch (Exception $e){
            $errorResponse = [
                'message'=>'Internal Server Error',
                'error'=>$e->getMessage(),
                'statusCode'=>500
            ];

            return response()->json($errorResponse);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function reset_password(Request $request)
    {
        $id = $request->user_id;
        $new_password = random_int(10000000, 99999999);
        if(auth()->user()->hasRole('ROLE ADMIN'))
        {
            try{
                $users = User::find($id);
                $users->password = bcrypt($new_password);
                $users->login_status = 0;
                $users->update();

                $successResponse = [
                    'message'=>'Change Password Successfuly',
                    'new_password'=>$new_password,
                    'statusCode'=>201
                ];

                return response($successResponse); 
            }
            catch (Exception $e){
                $errorResponse = [
                    'message'=>'Internal Server Error',
                    'error'=>$e->getMessage(),
                    'statusCode'=>500
                ];

                return response()->json($errorResponse);
            }
        }
        else{
            return response()
                ->json(['message' => 'Unauthorized','statusCode'=> 401]);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function logs_function() 
    {
        if (!auth()->user()->hasrole('ROLE ADMIN')) {
            $errorResponse = [
                'message'=>'Permission Denied',
                'statusCode'=>400
            ];

            return response()->json($errorResponse);
        }
        else{

            $activity =  DB::select("SELECT users.phone_no,users.email,users.first_name,users.middle_name,users.last_name,properties, activity_log.created_at, description,activity_log.subject_type as subject,activity_log.id FROM activity_log INNER JOIN users ON users.id=activity_log.causer_id WHERE users.id != 1  ORDER BY activity_log.id DESC LIMIT 1500");

            $successResponse = [
                'activity'=>$activity,
                'statusCode'=>200,
            ];

            return response()->json($successResponse);
        }
    }
}
