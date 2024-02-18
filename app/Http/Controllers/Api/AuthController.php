<?php

namespace App\Http\Controllers\Api;

use App\User;
use Carbon\Carbon;
use App\Model\MsSql;
use App\Model\Employee;
use App\Components\Common;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\EmployeeRequest;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'logout', 'register', 'migrate', 'sample', 'forgetPassword', 'changePassword', 'payroll_list']]);
    }

    public function login(Request $request)
    {

        $credentials = ['user_name' => $request->user_name, 'password' => $request->password];

        if ($token = JWTAuth::attempt($credentials)) {

            $userStatus = Auth::user()->status;

            if ($userStatus == UserStatus::$ACTIVE) {

                $employee = Employee::where('user_id', Auth::user()->user_id)->first();

                $is_checked_in = MsSql::where('ID', $employee->finger_id)->where('datetime', '>=', date('Y-m-d') . ' 00:00:00')->orderByDesc('datetime')
                    ->select('ms_sql.*', 'datetime as in_out_time')->first();


                $user_data = [
                    "user_id" => Auth::user()->user_id,
                    "user_name" => $employee->first_name . ' ' . $employee->last_name,
                    "role_id" => Auth::user()->role_id,
                    "employee_id" => $employee->employee_id,
                    "finger_id" => $employee->finger_id,
                ];


                return response()->json([
                    'message' => "Login Successful !!!",
                    'status' => true,
                    'access_token' => $token,
                    'is_checked_in' => isset($is_checked_in->type) && $is_checked_in->type == 'IN' ? true : false,
                    'checked_in_data' => $is_checked_in,
                    'user' => $user_data,
                ], 200);
            } elseif ($userStatus == UserStatus::$INACTIVE) {

                Auth::logout();

                return response()->json([
                    'status' => false,
                    'message' => 'You are temporary blocked. please contact to admin',
                ], 200);
            } else {

                Auth::logout();

                return response()->json([
                    'status' => false,
                    'message' => 'You are terminated. please contact to admin',
                ], 200);
            }
        } else {

            return response()->json([
                'status' => false,
                'message' => 'User name or password does not matched',
            ], 200);
        }
    }

    public function register(EmployeeRequest $employeeRequest, UserRequest $userRequest)
    {
        $now = Carbon::now();

        $user = User::create([
            'user_name' => $userRequest['user_name'],
            'password' => Hash::make($userRequest['password']),
            'role_id' => $userRequest['role_id'],
        ]);

        $employee = Employee::create([
            'first_name' => $user->user_name,
            'finger_id' => $employeeRequest['finger_id'],
            'user_id' => $user->user_id,
            'department_id' => $employeeRequest['department_id'],
            'designation_id' => $employeeRequest['designation_id'],
            'branch_id' => $employeeRequest['branch_id'],
            'supervisor_id' => $employeeRequest['supervisor_id'],
            'work_shift_id' => $employeeRequest['work_shift_id'],
            'pay_grade_id' => $employeeRequest['pay_grade_id'],
            'work_shift_id' => $employeeRequest['work_shift_id'],
            'date_of_birth' => $employeeRequest['date_of_birth'],
            'date_of_joining' => $employeeRequest['date_of_joining'],
            'gender' => $employeeRequest['gender'],
            'phone' => $employeeRequest['phone'],
            'status' => $employeeRequest['status'],
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
        ], 201);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    protected function createNewToken($token)
    {

        $employee = Employee::where('user_id', Auth::user()->user_id)->first();

        $is_checked_in = MsSql::where('ID', $employee->finger_id)->where('datetime', '>=', date('Y-m-d') . ' 00:00:00')
            ->select('ms_sql.*', 'datetime as in_out_time')->orderByDesc('datetime')->first();


        $user_data = [
            "user_id" => Auth::user()->user_id,
            "user_name" => $employee->first_name . ' ' . $employee->last_name,
            "role_id" => Auth::user()->role_id,
            "employee_id" => $employee->employee_id,
            "finger_id" => $employee->finger_id,
        ];


        return response()->json([
            'message' => "Authentication Successful !!!",
            'status' => true,
            'access_token' => $token,
            'is_checked_in' => isset($is_checked_in->type) && $is_checked_in->type == 'IN' ? true : false,
            'checked_in_data' => $is_checked_in,
            'user' => $user_data,
        ], 200);
    }
    
    public function changePassword(Request $request)
    {
        $input = Validator::make($request->all(), [
            'user_id' => 'required|exists:user,user_id',
            'password'=>
            [
                'required',
                function ($attribute, $value, $fail) {
                    $request = request();
                    $special = '/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;:\<\>,\.\?]/';
                    $policy1 = preg_match('/[A-Z]/', $value) ? 1 : 0 ;
                    $policy2 = preg_match('/[a-z]/', $value) ? 1 : 0 ;
                    $policy3 = preg_match('/[0-9]/', $value) ? 1 : 0 ;
                    $policy4 = preg_match($special, $value) ? 1 : 0 ;
                    $errorIs = [];
                    if(strlen($value)<User::PASS_MIN) {
                        $errorIs[] = ' should be need minimum '.User::PASS_MIN.' characters';
                    } else if(strlen($value)>User::PASS_MAX) {
                        $errorIs[] = ' should be keep maximum '.User::PASS_MAX.' characters';
                    }
                    if(Auth::user()) {
                        $User = User::find(Auth::user()->user_id);
                        if($User && $User->org_password==$value) {
                            $errorIs[] = ' should not be same as old password';
                        }
                    } else {
                        $errorIs[] = ' authenticate error!';
                    }

                    if(!$policy1) {
                        $errorIs[] = ' need at least 1 uppercase (A-Z)';
                    }
                    if(!$policy2) {
                        $errorIs[] = ' need at least 1 lowercase (a-z)';
                    }
                    if(!$policy3) {
                        $errorIs[] = ' need at least 1 number (0-9)';
                    }
                    if(!$policy4) {
                        $errorIs[] = ' need at least 1 special ('. \stripslashes($special) .')';
                    }

                    if($errorIs) {
                        $fail('New :attribute ' . implode('<br>', $errorIs));
                    }
                },
            ],
        ]);

        if(Auth::user()->user_id!=$request->user_id) {
            return Controller::custom_error('Invalid login user ID given!');
        }
        
        if ($input->fails()) {
            return Controller::custom_error($input->errors()->first());
        }
        $data = [
            'org_password' => $request['password'],
            'password' => Hash::make($request['password']),
        ];
        // $user = User::where('user_id', $request->user_id)->update($data);
        $user = User::where('user_id', Auth::user()->user_id)->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
            'user'    => $user,
        ], 201);
    }


    public function forgetPassword(Request $request)
    {
        try {
            $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
            $pass = array();
            $alphaLength = strlen($alphabet) - 1;
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            $new_password = implode($pass);
            if(env('APP_URL')=='http://localhost/in4solution') {
                $new_password = 'demo1234';
            }
    
            $user_data = User::where('user_name', $request->user_name)->first();

            if ($user_data == '') {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid User ID',
                    'user'    => $user_data,
                ], 201);
            }
            // to avoid admin role user should not be change (security purpose)
            if ($user_data->role_id>3) {
                $input['password'] = Hash::make($new_password);
                $input['org_password'] = $new_password;
                $userupdate = User::where('user_id', $user_data->user_id)->update($input);
                if ($userupdate) {
                    try {

                        $emp = Employee::where('user_id', $user_data->user_id)->first();
                        $admin=Employee::where('employee_id',1)->first();

                        if ($admin->email != '') {
                            Common::mail('emails/forgetPassword', $admin->email, 'New Password Notification', ['new_password' => $new_password, 'request_info' => $emp->first_name . ' ' . $emp->last_name . 'have requested for a new password at-' . date("F j, Y, g:i a")]);
                            return response()->json([
                                'status' => true,
                                'message' => 'New Password Sent To Admin Email !',
                                'user'    => $user_data,
                            ], 201);
                        } elseif ($admin->email == '') {
                            return response()->json([
                                'status' => false,
                                'message' => 'Admin Email Not Given !',
                                'user'    => $user_data,
                            ], 201);
                        }
                    } catch (\Exception $ex) {
                        return $ex;
                        return response()->json([
                            'status' => false,
                            'message' => 'Something Went Wrong !',
                            'user'    => $user_data,
                        ], 201);
                    }
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Admin Password Can Not Change !',
                    'user'    => $user_data,
                ], 201);
            }
        } catch (\Throwable $th) {
            info($th->getMessage());
        }
    }
}
