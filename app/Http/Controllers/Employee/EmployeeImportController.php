<?php

namespace App\Http\Controllers\Employee;

use App\Model\Branch;
use App\Model\Employee;
use App\Model\Department;
use Illuminate\Http\Request;
use App\Model\DepartmentCase;
use App\Model\DesignationCase;
use App\Imports\EmployeeImport;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Repositories\EmployeeRepository;
use Maatwebsite\Excel\Facades\Excel as Excel;

class EmployeeImportController extends Controller
{
    protected $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    public function import(Request $request)
    {
        try {
            $file = $request->file('select_file');
            $data = Excel::toArray(new EmployeeImport, $file);
            // dd($data);
            if(isset($data[0])) { // sheet one
                $sheet = $data[0];                      // get sheet one by default
                $EMAILS = array_column($sheet, '9');    // get email value column as a array

                // check in excel file email column same email entered
                // get email column by unique array
                $DUBLICATES = array_unique( array_diff_assoc( $EMAILS, array_unique( $EMAILS ) ) );
                $emailMessage = [];
                
                // get email column by unique value to display an error message expect null or empty (means excel 9th column blank)
                foreach ($DUBLICATES as $diffKey => $diffEmail) {
                    if(!is_null($diffEmail) && $diffEmail!='') {
                        $emailMessage[]=$diffEmail;
                    }
                }

                $allMessage = [];
                // check in DB email column from given excel file email entered
                foreach ($sheet as $recKey => $recCol) {
                    if($recCol[9]!='') {
                        $exists = Employee::where('email', $recCol[9])->where('finger_id', '!=', $recCol[3])->first();
                        if($exists) {
                            $emailMessage[]=$recCol[9];
                        }
                    }
                }

                if(count($allMessage)>0) {
                    $allMessage = '<br>' . implode(',<br>', $allMessage);
                } else {
                    $allMessage = '';
                }
                
                if(count($emailMessage)>0 || $allMessage) {
                    if($emailMessage) {
                        $emailMessage = 'Duplicate emails found: ' . implode(', ', $emailMessage) . $allMessage;
                    } else {
                        $emailMessage = $allMessage;
                    }
                    return redirect()->back()->withInput()->with('error', $emailMessage);
                }
            }

            Excel::import(new EmployeeImport($request->all()), $file);
            
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $import = new EmployeeImport();
            $import->import($file);

            foreach ($e->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
        }
        return back()->with('success', 'Employee information imported successfully.');
    }
}
