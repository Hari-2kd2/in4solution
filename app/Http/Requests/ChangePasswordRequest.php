<?php

namespace App\Http\Requests;


use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'oldPassword' => 'required',
            // 'password'=>'required|confirmed',
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
            'password_confirmation'=>'required',
        ];
    }
}
