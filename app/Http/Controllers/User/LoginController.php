<?php

namespace App\Http\Controllers\User;

use App\Model\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    public function index()
    {
        if (Auth::check()) {
            return redirect()->intended(url('/dashboard'));
        }

        return view('admin.login');
    }

    public function Auth(Request $request)
    {

        if (Auth::attempt(['user_name' => $request->user_name, 'password' => $request->user_password])) {
            $userStatus = Auth::user()->status;

            if ($userStatus == UserStatus::$ACTIVE) {
                $employee = Employee::where('user_id', Auth::user()->user_id)->first();

                if ($employee) {
                    $user_data = [
                        "user_id" => Auth::user()->user_id,
                        "user_name" => Auth::user()->user_name,
                        "role_id" => Auth::user()->role_id,
                        "employee_id" => $employee->employee_id,
                        "department_id" => $employee->department_id,
                        "finger_id" => $employee->finger_id,
                        "branch_id" => $employee->branch_id,
                        "email" => $employee->email,
                    ];
                    $data = collect($user_data);

                    Session()->put('logged_session_data', $data);
                    if (Auth::user()->role_id == 1) {

                        $branch = Branch::first();
                        Session()->put('selected_branchId', $branch->branch_id);
                        session()->put('branch_name', $branch->branch_name);
                    }

                    return redirect()->intended(url('/dashboard'));
                } else {
                    Auth::logout();
                    return redirect(url('login'))->withInput()->with('error', 'Employee data not found.');
                }
            } elseif ($userStatus == UserStatus::$INACTIVE) {
                Auth::logout();
                return redirect(url('login'))->withInput()->with('error', 'You are temporarily blocked. Please contact the admin.');
            } else {
                Auth::logout();
                return redirect(url('login'))->withInput()->with('error', 'You are terminated. Please contact the admin.');
            }
        } else {
            return redirect(url('login'))->withInput()->with('error', 'User name or password does not match.');
        }
    }

    public function logout()
    {

        session()->flush();
        Auth::logout();
        return response()->json(['message' => 'Logout successfully']);
    }


    public function authenticated()
    {

        $user = Auth::user();
        if ($user->google2fa_secret) {
            Auth::logout();
            session()->put('2fa:user:id', $user->user_id);
            return redirect('2fa/validate');
        }

        return redirect()->intended('login');
    }

    public function postValidateToken(Request $request)
    {
        $userId = $request->session()->pull('2fa:user:id');
        $key = $userId . ':' . $request->totp;

        Cache::add($key, true, 4);
        Auth::loginUsingId($userId);

        return redirect()->intended('dashboard');
    }

    public function getValidateToken()
    {
        if (session('2fa:user:id')) {
            return view('2fa/validate');
        }
        return redirect('login');
    }
    public function clear()
    {
        return response()->json(['message' => 'Session data cleared']);
    }
    public function storeBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        $name = Branch::where('branch_id', $branchId)->first();
        $selectedBranchId = session()->put('selected_branchId', $branchId);
        $selectedBranchName = session()->put('branch_name', $name->branch_name);
        return response()->json(['message' => 'Branch ID stored successfully', 'name' => $name->branch_name]);
    }


    public function demoLogin($username, $password)
    {
        // manually logout
        $logout = Auth::logout();

        // clear all auth session data
        $session =  Session::flush();
        session()->put('logged_session_data', null);

        // dd(session('logged_session_data'));
        // dd($username);

        // force Login
        if (Auth::attempt(['user_name' => $username, 'password' => $password])) {
            $employee = Employee::where('user_id', Auth::user()->user_id)->first();
            $user_data = [
                "user_id" => Auth::user()->user_id,
                "user_name" => Auth::user()->user_name,
                "role_id" => Auth::user()->role_id,
                "employee_id" => $employee->employee_id,
                "department_id" => $employee->department_id,
                "finger_id" => $employee->finger_id,
                "branch_id" => $employee->branch_id,
                "email" => $employee->email,
            ];

            session()->put('logged_session_data', $user_data);
            return redirect()->intended(url('/dashboard'));
        }

        return redirect()->intended('login');
    }
}
