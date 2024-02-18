<?php

namespace App\Http\Controllers\Company;

use App\Model\Role;
use Illuminate\Support\Str;
use App\Model\CompanyPolicy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\CompanyPolicyRequest;

class CompanyPolicyController extends Controller
{
    public function index(Request $request)
    {

        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $results = CompanyPolicy::latest();
        $role = Role::where('role_name', 'like', '%Manager%')->first();
        
        if ($roleId == 1) {
            $results = $results->get();
        } else {
            if ($role) {
                $results = $results->whereIn('branch_id', [$branchId, 0])->get();
            } else {
                $results = $results->where([['branch_id', $branchId], ['title', '<=', 1]])->orWhere([['branch_id', 0], ['title', '<=', 1]])->get();
            }
        }
        return view('admin.company.companyPolicy.index', ['results' => $results]);
    }

    public function create()
    {
        return view('admin.company.companyPolicy.form');
    }

    public function store(CompanyPolicyRequest $request)
    {
        $input = $request->all();
        unset($input['file']);
        $input['created_by'] = auth()->id();
        $input['updated_by'] =  auth()->id();

        $photo = $request->file('file');

        if ($photo) {
            $fileName = md5(Str::random(30) . time() . '_' . $request->file('file')) . '.' . $request->file('file')->getClientOriginalExtension();
            $request->file('file')->move('uploads/employeePolicy/', $fileName);
            $input['file'] = $fileName;
        }

        try {
            CompanyPolicy::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            dd($e);
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('companyPolicy')->with('success', 'Company policy successfully saved.');
        } else {
            return redirect('companyPolicy')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit()
    {
        $editModeData = CompanyPolicy::latest()->first();
        return view('admin.company.companyPolicy.form', ['editModeData' => $editModeData]);
    }

    public function update(CompanyPolicyRequest $request, $id)
    {
        $data = CompanyPolicy::findOrFail($id);

        $input = $request->all();
        $input['created_by'] = auth()->id();
        $input['updated_by'] =  auth()->id();

        $photo = $request->file('file');

        if ($photo) {
            $fileName = md5(Str::random(30) . time() . '_' . $request->file('file')) . '.' . $request->file('file')->getClientOriginalExtension();
            $request->file('file')->move('uploads/employeePolicy/', $fileName);
            $input['file'] = $fileName;
        }


        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('companyPolicy')->with('success', 'Company policy successfully updated.');
        } else {
            return redirect('companyPolicy')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $data = CompanyPolicy::findOrFail($id);
            $data->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            echo "success";
        } else {
            echo 'error';
        }
    }
}
