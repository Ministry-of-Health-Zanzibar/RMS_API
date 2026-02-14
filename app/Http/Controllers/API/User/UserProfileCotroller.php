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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


class UserProfileCotroller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except([
            'forgotPassword',
            'forgotPasswordReset'
        ]);
        //$this->middleware('permission:Setup Management|Create Upload Types|Create Upload Types|Update Upload Types|Update Upload Types|Delete Upload Types', ['only' => ['index','create','store','update','destroy']]);
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
     * @OA\Post(
     *     path="/api/changePassword",
     *     summary="change Password",
     *     tags={"userProfile"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="new_password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
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
     * @OA\Post(
     *     path="/api/resetPassword",
     *     summary="Reset a user's password (Admin only)",
     *     tags={"User Management"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user to reset password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Change Password Successfully"),
     *             @OA\Property(property="new_password", type="string", example="12345678"),
     *             @OA\Property(property="statusCode", type="integer", example=201)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="statusCode", type="integer", example=401)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Internal Server Error"),
     *             @OA\Property(property="error", type="string", example="Error details"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $id = $request->user_id;
        $new_password = random_int(10000000, 99999999);

        if(auth()->user()->hasRole('ROLE ADMIN'))
        {
            try {
                $user = User::find($id);

                if (!$user) {
                    return response()->json([
                        'message' => 'User not found',
                        'statusCode' => 404
                    ]);
                }

                // Update password
                $user->password = bcrypt($new_password);
                $user->login_status = 0;
                $user->save();

                // Send email using the HTML template
                Mail::send('emails.password_reset', [
                    'first_name'   => $user->first_name,
                    'email'        => $user->email,
                    'new_password' => $new_password
                ], function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Your Password Has Been Reset');
                });

                return response()->json([
                    'message' => 'Password reset successfully and sent to user email',
                    'statusCode' => 201
                ]);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'error' => $e->getMessage(),
                    'statusCode' => 500
                ]);
            }
        }
        else {
            return response()->json([
                'message' => 'Unauthorized',
                'statusCode'=> 401
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Request a password reset link (Public)",
     *     tags={"User Management"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="user@example.com", description="Registered user email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Password reset link sent to your email"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Internal Server Error"),
     *             @OA\Property(property="error", type="string", example="Error details"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $token = Str::random(64);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now(),
                ]
            );

            $resetLink = url("/reset-password?email={$request->email}&token={$token}");

            // Send email
            Mail::raw(
                "Click this link to reset your password: {$resetLink}",
                function ($message) use ($request) {
                    $message->to($request->email)
                            ->subject('Reset Your Password');
                }
            );

            return response()->json([
                'message' => 'Password reset link sent to your email',
                'statusCode' => 200
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'statusCode' => 500
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/reset-forgot-password",
     *     summary="Reset password using token (Public)",
     *     tags={"User Management"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="abcdef123456"),
     *             @OA\Property(property="new_password", type="string", example="newpassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Password reset successfully"),
     *             @OA\Property(property="statusCode", type="integer", example=200)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired token",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid or expired token"),
     *             @OA\Property(property="statusCode", type="integer", example=400)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Internal Server Error"),
     *             @OA\Property(property="error", type="string", example="Error details"),
     *             @OA\Property(property="statusCode", type="integer", example=500)
     *         )
     *     )
     * )
     */
    public function forgotPasswordReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        try {
            $record = DB::table('password_resets')
                ->where('email', $request->email)
                ->first();

            if (!$record) {
                return response()->json([
                    'message' => 'Invalid reset request',
                    'statusCode' => 400
                ]);
            }

            if (!Hash::check($request->token, $record->token)) {
                return response()->json([
                    'message' => 'Invalid or expired token',
                    'statusCode' => 400
                ]);
            }

            $user = User::where('email', $request->email)->first();

            $user->password = bcrypt($request->new_password);
            $user->login_status = 1;
            $user->save();

            DB::table('password_resets')->where('email', $request->email)->delete();

            return response()->json([
                'message' => 'Password reset successfully',
                'statusCode' => 200
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'statusCode' => 500
            ]);
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
