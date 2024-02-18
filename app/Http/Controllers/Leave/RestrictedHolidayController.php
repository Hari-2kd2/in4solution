<?php

namespace App\Http\Controllers\Leave;

use App\Model\Branch;
use App\Model\calanderYear;
use App\Model\RestrictedHoliday;
use App\Http\Controllers\Controller;
use App\Http\Requests\RestrictedHolidayRequest;
use Illuminate\Support\Facades\Lang;

class RestrictedHolidayController extends Controller
{

    public function index()
    {
        
        $calanderYear = calanderYear::currentYear();
        $results = RestrictedHoliday::orderBy('holiday_date', 'desc')->get();
        return view('admin.leave.restrictedHoliday.index', ['results' => $results]);
    }

    public function create()
    {
        return view('admin.leave.restrictedHoliday.form');
    }

    public function store(RestrictedHolidayRequest $request)
    {
        $input = $request->all();
        $input['holiday_date'] = dateConvertFormtoDB($input['holiday_date']);
        $calanderYear = calanderYear::currentYear();
        $input['year_id'] = $calanderYear->year_id;
        try {
            RestrictedHoliday::createTrait($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('restrictedHoliday')->with('success', 'Restricted holiday successfully saved.');
        } else {
            return redirect('restrictedHoliday')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $editModeData = RestrictedHoliday::findOrFail($id);
        $today = date('Y-m-d');
        if($today>$editModeData->holiday_date) {
            abort(403, 'Can not update past holiday');
        }
        return view('admin.leave.restrictedHoliday.form', ['editModeData' => $editModeData]);
    }

    public function update(RestrictedHolidayRequest $request, $id)
    {
        $holiday = RestrictedHoliday::findOrFail($id);
        $input = $request->all();
        $input['holiday_date'] = dateConvertFormtoDB($input['holiday_date']);
        $input['year_id'] = $holiday->year_id;

        try {
            $holiday->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Restricted holiday successfully updated. ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $holiday = RestrictedHoliday::findOrFail($id);
            $holiday->delete();
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
