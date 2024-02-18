<?php

namespace App\Http\Controllers\User;

use App\User;

use App\Model\Employee;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ChangePasswordRequest;


class ChangePasswordController extends Controller
{

    public function index() {
        return view('admin.user.user.changePassword');
    }


    public function update(ChangePasswordRequest $request,$id){
        $input['password'] = Hash::make($request['password']);
        if(Auth::attempt(['user_id'=>Auth::user()->user_id,'password'=>$request->oldPassword])){
            $input['org_password'] = $request['password'];
               User::where('user_id', Auth::user()->user_id)->update($input);
              return redirect()->back()->with('success', 'Password successfully updated.');
        }else{
            return redirect()->back()->with('error', 'Old Password does not match.');
        }
    }

    public function newPassword(Request $request)
    {
        $bug= 0;
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $new_password = implode($pass); //turn the array into a string 
       
        $user = User::where('user_name', $request->user_name)->first();
        // to avoid admin role user should not be change (security purpose)
        if($user && $user->role_id<=3){
            $bug= -1; 
            goto errorHandle; 
        } else if($user == ''){
            $bug= 1; 
            goto errorHandle; 
        }  
        if(env('APP_URL')=='http://localhost/in4solution') {
            $new_password = 'demo1234';
        }
        $input['password'] = Hash::make($new_password);
        $input['org_password'] = $new_password;
        $userupdate = User::where('user_id', $user->user_id)->update($input);
        if($userupdate){
            try{
                //Admin reset password email notification 
                $emp=Employee::where('user_id',$user->user_id)->first();
                $admin=Employee::where('employee_id',1)->first();

                if($admin->email !=''){                    
                    \App\Components\Common::mail('emails/forgetPassword',$admin->email,'New Password Notification',['new_password'=> $new_password,'request_info'=> $emp->first_name.' '.$emp->last_namr.'have requested for a new password at '.' '. date("F j, Y, g:i a")]);
                }elseif($admin->email == ''){  
                    $bug= 2;  
                } 
                                
                //End Admin reset password email notification
            } catch (\Exception $ex) {
                return $ex;
               $bug= 3;
               
            } 
        }
        
        errorHandle:
        
        if($bug == -1){
            return redirect()->back()->with('success', 'Admin Password Can Not Change !');
        }elseif($bug == 0){
            return redirect()->back()->with('success', 'New Password Sent To Admin Email.');
        }elseif($bug == 1){
            return redirect()->back()->with('error', 'Invalid User ID.');
        }elseif($bug == 2){
            return redirect()->back()->with('error', 'Admin Email Not Given.');
        } else{
            return redirect()->back()->with('error', 'Something Went Wrong.');
        }
          
        
    }


}
