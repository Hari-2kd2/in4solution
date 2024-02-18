<?php

namespace App\Http\Controllers\Payroll;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ProfessionalTax;

class ProfessionalTaxController extends Controller
{

    public function index(Request $request)
    {
        $results = ProfessionalTax::orderBy('pt_id', 'desc')->get();
        return view('admin.payroll.ProfessionalTax.index', ['results' => $results]);
    }

    public function create(Request $request)
    {
        return view('admin.payroll.ProfessionalTax.form');
    }

    public function store(Request $request)
    {

        $request->validate([
            'months' => 'required',
            'amount' => 'required|numeric|min:0',

        ]);
        $input = $request->all();
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $selectedbranchId = session('selected_branchId');

        if ($roleId == 1) {
            $input['branch_id'] = $selectedbranchId;
        } else {
            $input['branch_id'] = $branchId;
        }

        $input['months'] = implode(',', $request->months);
        $settings = new ProfessionalTax;
        $settings->create($input);

        return redirect(route('ProfessionalTax.index'))->with('success', 'Professional Tax settings successfully updated.');
    }

    public function edit(Request $request)
    {
        $editModeData = ProfessionalTax::find($request->id);
        return view('admin.payroll.ProfessionalTax.form', compact('editModeData'));
    }

    public function update(Request $request)
    {

        $request->validate([
            'months' => 'required',
            'amount' => 'required|numeric|min:0',

        ]);
        $input = $request->all();
        $input['months'] = implode(',', $request->months);
        $settings = ProfessionalTax::find($request->id);


        $settings->update($input);

        return redirect(route('ProfessionalTax.index'))->with('success', 'Professional Tax settings successfully updated.');
    }
}
