<?php

namespace App\Http\Controllers\Api;

use App\Model\MsSql;
use App\Model\Device;
use App\Model\Employee;
use App\Model\AccessControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DeviceController extends Controller
{

    public function add(Request $request)
    {

        DB::beginTransaction();
        $device = Device::create($request->all());
        Device::where('id', $device->id)->update(['id' => $request->id]);
        DB::commit();

        return json_encode(['status' => 'success', 'message' => 'Device created Successfully !'], 200);
    }

    public function update(Request $request)
    {

        DB::beginTransaction();
        $device = Device::findOrFail($request->id);
        $device->update($request->all());
        DB::commit();

        return json_encode(['status' => 'success', 'message' => 'Device Successfully updated !'], 200);
    }

    public function destroy(Request $request)
    {

        $devices = Device::FindOrFail($request->id);
        $devices->status = 2;
        $devices->save();

        AccessControl::where('device', $request->id)->delete();

        return json_encode(['status' => 'success', 'message' => 'Device Log Successfully updated !'], 200);
    }

    public function importlogs(Request $request)
    {

        try {

            $chk = MsSql::where('local_primary_id', $request->primary_id)->count();

            $employee = Employee::where('finger_id', $request->ID)->first();
            if ($employee) {
                $employee_id = $employee->employee_id;
            } else {
                $employee_id = NULL;
            }

            $check_type = 'IN';

            $lastAttendanceData = MsSql::whereRaw("DATE_FORMAT(datetime,'%Y-%m-%d')= '" . DATE('Y-m-d', strtotime($request->datetime)) . "' ")->where('ID', $request->ID)->orderBy('datetime', 'DESC')->first();
            if ($lastAttendanceData) {
                if ($lastAttendanceData->type == 'IN') {
                    $check_type = 'OUT';
                } elseif ($lastAttendanceData->type == 'OUT') {
                    $check_type = 'IN';
                }
            }

            Log::info('import done at :' . date('Y-m-d H:i:s'));
            DB::beginTransaction();

            if (!$chk) {

                $device = new MsSql();
                $device->local_primary_id = $request->primary_id;
                $device->evtlguid = $request->evtlguid;
                $device->ID = $request->ID;
                $device->type = $check_type;
                $device->employee = $employee_id;
                $device->status = 0;
                $device->datetime = $request->datetime;
                //$device->devdt = $request->devdt;
                $device->devuid = $request->devuid;
                $device->punching_time = $request->punching_time;
                $device->created_at = date('Y-m-d H:i:s');
                $device->updated_at = date('Y-m-d H:i:s');

                $device->terminal_sn = $request->terminal_sn;
                $device->terminal_alias = $request->terminal_alias;
                $device->area_alias = $request->area_alias;

                $device->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            info($th->getMessage());
        }

        return response()->json(['status' => 'success', 'message' => 'Device Log Successfully updated !'], 200);
    }
}
