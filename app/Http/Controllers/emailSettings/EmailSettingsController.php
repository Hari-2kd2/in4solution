<?php

namespace App\Http\Controllers\emailSettings;

use App\Components\Common;
use App\Model\Employee;
use App\Model\Keypeople;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\HrpeopleRequest;
use App\Http\Requests\KeypeopleRequest;

class EmailSettingsController extends Controller
{

    private $search=[' ', PHP_EOL, '	'];
    private $replace=['', '', ''];

    public function index(Request $request) {
        $activeBranch = Common::activeBranch();
        $Keypeople = Keypeople::where('branch_id', $activeBranch)->first();
        if(!$Keypeople) {
            $Keypeople = new Keypeople;
        }
        $HrPeople = clone $Keypeople;
        return view('admin.keypeople', compact('Keypeople', 'HrPeople'));
    }
    
    public function store(KeypeopleRequest $request) {
        $input = $request->all();
        $input['key_user_ids'] = implode(',', $input['key_user_ids']);
        $input['key_director_emails'] = str_replace($this->search, $this->replace, $input['key_director_emails']);
        try {
            Keypeople::createTrait($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('emailSettings')->with('success', 'Key people successfully saved.');
        } else {
            return redirect('emailSettings')->with('error', 'Something Error Found !, Please try again.');
        }

    }

    public function hrStore(HrpeopleRequest $request) {
        $input = $request->all();
        $input['key_hr_emails'] = str_replace($this->search, $this->replace, $input['key_hr_emails']);
        try {
            Keypeople::createTrait($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('emailSettings')->with('success', 'HR people successfully saved.');
        } else {
            return redirect('emailSettings')->with('error', 'Something Error Found !, Please try again.');
        }

    }

    public function update(KeypeopleRequest $request, $id) {
        $Keypeople = Keypeople::findOrFail($id);
        $input = $request->all();
        $input['key_user_ids'] = implode(',', $input['key_user_ids']);
        $input['key_director_emails'] = str_replace($this->search, $this->replace, $input['key_director_emails']);
        try {
            $Keypeople->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        
        if ($bug == 0) {
            return redirect('emailSettings')->with('success', 'Key people settings successfully saved.');
        } else {
            return redirect('emailSettings')->with('error', 'Something Error Found !, Please try again.');
        }

    }

    public function hrUpdate(HrpeopleRequest $request, $id) {
        $Keypeople = Keypeople::findOrFail($id);
        $input = $request->all();
        $input['key_hr_emails'] = str_replace($this->search, $this->replace, $input['key_hr_emails']);
        try {
            $Keypeople->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }
        
        if ($bug == 0) {
            return redirect('emailSettings')->with('hrSuccess', 'HR people settings successfully saved.');
        } else {
            return redirect('emailSettings')->with('hrError', 'Something Error Found !, Please try again.');
        }

    }

    public function keypeopleSearch(Request $request) {
        $q = $request->get('q');
		$json = [];
        if($q) {
            $activeBranch = Common::activeBranch();
            $EmployeeList = Employee::whereRaw("(email LIKE '$q%' OR finger_id LIKE '$q%')")->where('branch_id', $activeBranch)->orderBy('employee_id')->limit(50)->get();
            foreach ($EmployeeList as $key => $Employee) {
                $json[] = ['id' => $Employee->employee_id, 'text' => $Employee->finger_id.' ('.$Employee->email.')'];
            }
        }
        return response()->json($json);
    }

    public function destroy($id)
    {
        try {
            $Keypeople = Keypeople::findOrFail($id);
            $Keypeople->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('keypeople')->with('success', 'Key people settings successfully deleted.');
        } else {
            echo 'error';
        }
    }

    public function hrDestroy($id)
    {
        try {
            $Keypeople = Keypeople::findOrFail($id);
            $Keypeople->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('keypeople')->with('success', 'HR people settings successfully deleted.');
        } else {
            echo 'error';
        }
    }


}
