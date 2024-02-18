<?php

namespace App\Http\Controllers\Payroll;

use App\Model\SalaryTax;
use App\Traits\CrudTrait;
use App\Traits\BranchTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\SalaryTaxRequest;
use Illuminate\Database\Eloquent\Model;

class SalaryTaxSlabController extends Controller
{

    public function index()
    {
        $results = SalaryTax::orderBy('slab_salary_from', 'asc')->get();
        return view('admin.payroll.salaryTax.index', ['results' => $results]);
    }

    public function create()
    {
        return view('admin.payroll.salaryTax.form');
    }

    public function store(SalaryTaxRequest $request)
    {
        $input = $request->all();
        try {
            SalaryTax::createTrait($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
            dd($e->getMessage());
        }

        if ($bug == 0) {
            return redirect('salaryTaxSlab')->with('success', 'Salary tax slab successfully saved.');
        } else {
            return redirect('salaryTaxSlab')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $editModeData = SalaryTax::findOrFail($id);
        return view('admin.payroll.salaryTax.form', ['editModeData' => $editModeData]);
    }

    public function update(SalaryTaxRequest $request, $id)
    {
        $SalaryTax = SalaryTax::findOrFail($id);
        $input = $request->all();
        try {
            $SalaryTax->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Salary tax slab successfully updated. ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $SalaryTax = SalaryTax::findOrFail($id);
            $SalaryTax->delete();
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
