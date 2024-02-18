<?php

namespace App\Http\Controllers\Employee;

use App\Model\Branch;
use App\Model\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;

class BranchController extends Controller
{

    public function index()
    {
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $selectedbranchId = session('selected_branchId');
        if ($branchId !== null && $roleId !== 1) {
            $results = Branch::where('branch_id', session('logged_session_data.branch_id'))->get();
        } elseif ($roleId == 1) {
            $results = Branch::get();
        } else
            $results = Branch::get();
        return view('admin.employee.branch.index', ['results' => $results]);
    }

    public function create()
    {
        return view('admin.employee.branch.form');
    }

    public function store(BranchRequest $request)
    {
        $input = $request->all();
        try {
            Branch::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('branch')->with('success', 'Branch successfully saved.');
        } else {
            return redirect('branch')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $editModeData = Branch::findOrFail($id);
        return view('admin.employee.branch.form', ['editModeData' => $editModeData]);
    }

    public function update(BranchRequest $request, $id)
    {
        $branch = Branch::findOrFail($id);
        $input = $request->all();
        try {
            $branch->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Branch successfully updated ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {

        $count = Employee::where('branch_id', $id)->count();

        if ($count > 0) {

            return 'hasForeignKey';
        }

        try {
            $branch = Branch::findOrFail($id);
            $branch->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            echo "success";
        } elseif ($bug == 1451) {
            echo 'hasForeignKey';
        } else {
            echo 'error';
        }
    }
}
