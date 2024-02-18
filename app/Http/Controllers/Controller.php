<?php

namespace App\Http\Controllers;

use App\Components\CamAttendance;
use App\Events\AccessLogEvent;
use App\Model\Employee;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // public $camAttendance;

    // public function __construct(CamAttendance $camAttendance)
    // {
    //     $this->camAttendance = $camAttendance;
    // }

    public function success($message, $data)
    {
        return response()->json([
            'status' => \true,
            'message' => $message,
            'data' => $data,
        ], 200);
    }
    public function data($data)
    {
        return response()->json([
            'status' => \true,
            'data' => $data,
        ], 200);
    }

    public function successdualdata($message, $data, $list)
    {
        return response()->json([
            'status' => \true,
            'message' => $message,
            'data' => $data,
            'list' => $list,
        ], 200);
    }

    public function error()
    {
        return response()->json([
            'status' => \false,
            'message' => "Something error found !, Please try again.",
        ], 200);
    }

    public function custom_error($custom_message)
    {
        return response()->json([
            'status' => \false,
            'message' => $custom_message,
        ], 200);
    }

    // public function ms_sql(Request $request)
    // {

    //     $employees = Employee::select('finger_id', 'employee_id')->groupby('finger_id')->get();
    //     $date = '2022-11-21';

    //     foreach ($employees as $key1 => $finger_id) {
    //         $start_date = DATE('Y-m-d', strtotime($date)) . " 05:00:00";
    //         $end_date = DATE('Y-m-d', strtotime($date . " +1 day")) . " 07:00:00";

    //         $results = DB::table('ms_sql')
    //             ->whereRaw("datetime >= '" . $start_date . "' AND datetime <= '" . $end_date . "'")
    //             ->where('ID', $finger_id->finger_id)
    //             ->whereBetween('device', [19, 28])
    //             ->where('status', 0)
    //             ->orderby('datetime', 'ASC')
    //             ->get();
    //     }
    // }

    // public function test(Request $request)
    // {
    //     try {

    //         $last_id = MsSql::max('local_primary_id');

    //         $data = [
    //             'ID' => 'A001',
    //             'datetime' => $request->datetime,
    //             'type' => $request->type,
    //             'local_primary_id' => $last_id ?? 1,
    //             'status' => 1,
    //         ];

    //         event(new AccessLogEvent($data));

    //         return response()->json([
    //             'status' => true,
    //             'data' => $data,
    //         ], 200);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'status' => true,
    //             'message' => $th->getMessage(),
    //         ], 200);
    //     }
    // }

    public function sample()
    {
        return json_encode(['status' => true]);

        // foreach (AttendanceLog::all() as $key => $value) {
        //     // dd($value);
        //     MsSql::insert([
        //         'ID' => $value->employeeId,
        //         'datetime' => date('Y-m-d H:i:s', strtotime($value->date . ' ' . $value->time)),
        //         'punching_time' => date('Y-m-d H:i:s', strtotime($value->date . ' ' . $value->time)),
        //         'type' => $value->type == 0 ? 'IN' : 'OUT',
        //         'device_name' => $value->locationName,
        //         'devuid' => $value->locationId,
        //         'created_at' => date('Y-m-d H:i:s'),
        //         'updated_at' => date('Y-m-d H:i:s'),
        //     ]);
        // }

        // $emp = Employee::where('employee_id', '1')->first()->toArray();
        // $usr = User::where('user_id', '1')->first()->toArray();

        // $emp_id = AttendanceLog::groupBy('employeeId')->pluck('employeeId');

        // foreach ($emp_id as $key => $employeeId) {
        //     try {
        //         $usr['user_name'] = $employeeId;
        //         $usr['user_id'] = $employeeId;
        //         $usr_id = User::create($usr);
        //         $emp['user_id'] = $usr_id->user_id;
        //         $usr['employee_id'] = $employeeId;
        //         $emp['finger_id'] = $employeeId;
        //         Employee::create($emp);
        //     } catch (\Throwable $th) {
        //         //throw $th;
        //     }
        // }

        // $usr = User::where('user_id', '!=', '1')->get();

        // foreach ($usr as $key => $user) {

        //     try {
        //         $emp['user_id'] = $user->user_id;
        //         $emp['finger_id'] = $user->user_id;
        //         Employee::create($emp);
        //     } catch (\Throwable $th) {
        //         //throw $th;
        //     }
        // }

    }

    // public function log()
    // {
    //     return $this->camAttendance->init();
    // }
}
