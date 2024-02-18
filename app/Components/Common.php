<?php

namespace App\Components;

use Exception;
use App\Model\Device;
use Illuminate\Support\Facades\Mail;

class Common
{
    const MAX_ALLOWED_RESTRICTED_HOLIDAY=1; // per calander year
    const APP_NAME='In4Solution'; // application name
    const COMPANY_NAME='In4Solution (INDIA) PRIVATE LIMITED'; // company name
    const COMPANY_ADDRESS='A-18, SIPCOT Industrial Growth Centre, Panrutti A Village, Oragadam, Sriperumbudur Taluk, Kancheepuram District - 631 604'; // company address
    const MAIL_FROM='info@tesamm.in'; // system from email
    const PERCENTAGE_BASIC=40;
    const PERCENTAGE_HRA=30;
    const PERCENTAGE_BONUS=8.33;
    const PERMISSION_LIMIT=3;
    const PER_PERMISSION_HOUR=1;
    const TRACE=['T0002'];

    public static function restartdevice($try_count = 0)
    {

        $device = Device::where('status', 1)->get();

        foreach ($device as $key => $Data) {

            $Data->device_status = 'offline';
            $Data->save();

            try {
                $rawdata = [
                    "SearchDescription" => [
                        "position"  => 0,
                        "maxResult" => 100,
                        "Filter"    => [
                            "key"          => $Data->ip,
                            "devType"      => "AccessControl",
                            "protocolType" => ["ISAPI"],
                            "devStatus"    => ["online", "offline"],
                        ],
                    ],
                ];

                $client   = new \GuzzleHttp\Client();
                $response = $client->request('POST', 'http://localhost:' . $Data->port . '/' . $Data->protocol . '/ContentMgmt/DeviceMgmt/deviceList?format=json', [
                    'auth' => [$Data->username, $Data->password, "digest"],
                    'json' => $rawdata,
                ]);

                $statusCode = $response->getStatusCode();
                $content    = $response->getBody()->getContents();
                $data       = json_decode($content);
                //dd($data);
                if ($data->SearchResult->numOfMatches == 1) {
                    $deviceInfo          = $data->SearchResult->MatchList[0]->Device;
                    $Data->model         = $deviceInfo->devMode;
                    $Data->device_status = $deviceInfo->devStatus;

                    if ($Data->verification_status == 0 && $Data->device_status == "online") {
                        $Data->verification_status = 1;
                    }

                    $Data->save();
                }
            } catch (\Exception $e) {
                //return redirect()->back()->with('error', 'Something went wrong try again ! ');
            }
        }

        $offline_device = Device::where('device_status', 'offline')->where('status', '!=', 2)->get();

        //dd(count($offline_device) , count($device));

        if (count($offline_device) == count($device)) {
            if ($try_count == 0) {
                $out = exec('C:\Program Files\AC Gateway\Guard\stop.bat', $output, $return);
                $out = exec('C:\Program Files\AC Gateway\Guard\start.bat', $output, $return);
                if ($return == 0) {
                    sleep(20);
                    return Common::restartdevice($try_count + 1);
                } else {
                    return Common::restartdevice($try_count + 1);
                }
            } elseif ($try_count < 6) {
                return Common::restartdevice($try_count + 1);
            } elseif ($try_count >= 6) {
                return json_encode(["status" => "all_offline_check_cable", 'msg' => 'All the devices are offline. Please check the network connection !']);
            }
        } else {
            $online_device = Device::where('device_status', 'online')->where('status', '!=', 2)->count();
            if ($online_device != count($device)) {
                //\Log::info($try_count);
                if ($try_count < 6) {
                    sleep(7);
                    return Common::restartdevice($try_count + 1);
                } else {
                    if (count($offline_device)) {
                        $offline_set = [];
                        foreach ($offline_device as $offlineData) {
                            $offline_set[] = $offlineData->name . " ( " . $offlineData->model . " )";
                        }
                        $offlineDevice = implode(", ", $offline_set);
                        return json_encode(["status" => "some_offline", "offline_device" => $offlineDevice, 'msg' => 'The following device(s) are not reachable / offline , so unable to sync. Please check the device connection.The offline Devices are : [ ' . $offlineDevice . ' ]']);
                    } else {
                        return json_encode(["status" => "all_online"]);
                    }
                }
            } else {

                if (count($offline_device)) {
                    $offline_set = [];
                    foreach ($offline_device as $offlineData) {
                        $offline_set[] = $offlineData->name . " ( " . $offlineData->model . " )";
                    }
                    $offlineDevice = implode(", ", $offline_set);
                    return json_encode(["status" => "some_offline", "offline_device" => $offlineDevice, 'msg' => 'The following device(s) are not reachable / offline , so unable to sync. Please check the device connection.The offline Devices are : [ ' . $offlineDevice . ' ]']);
                } else {
                    return json_encode(["status" => "all_online"]);
                }
            }
        }
    }

    public static function clearinternalerror()
    {
        $out = exec('C:\Program Files\AC Gateway\Guard\stop.bat', $output, $return);
        $out = exec('C:\Program Files\AC Gateway\Guard\start.bat', $output, $return);
        sleep(15);
        return true;
    }


    public static function triggerException()
    {
        // using throw keyword
        throw new Exception('Client error:"POSThttp://localhost/ISAP/AccesCantrel/AcsEventformat-json&deyindex=69006054-1770-447-8569-5608A735076 resulted in a `403 Forbidden` response: {"errorCode":805306388."errorMsg":"Internal error.","statusCode":3,"statusString":"Device Error"');
    }

    public static function errormsg()
    {
        return "Device not responding. Please navigate to Device Configuration and click Refresh device service button.";
    }


    public static function liveurl()
    {
        // tata smartfood
        // return "https://ipro-people.com/tatasmartfoodz/api/";
        return "https://localhost/tatasmartfoodz/api/";
    }

    public static function mail($template,$to,$subject,$data){
		$mail = Mail::send($template,$data, function ($message) use ($to,$subject) {
			$message->from(self::MAIL_FROM, self::APP_NAME.' Administrator');
			$message->to($to, self::APP_NAME)->subject($subject);
		});
	}

    public static function mailing($emailViewBlade, $toEMAIL, $SUBJECT, $DATA, $OPTIONALS=[])
	{
        // $DATA variable to merge with blade template, $DATA array can access $emailViewBlade file
        $attachFILE = isset($OPTIONALS['filename']) ? $OPTIONALS['filename'] : '';
        $bccEMAIL = isset($OPTIONALS['bcc']) ? $OPTIONALS['bcc'] : [];
        Mail::send($emailViewBlade, $DATA,  function ($message) use ($toEMAIL, $SUBJECT, $bccEMAIL, $attachFILE) {
            $message->to($toEMAIL);
            if($bccEMAIL) {
                $message->bcc($bccEMAIL);
            }
            if($attachFILE) {
                $message->attach(base_path() . "/storage/app/" . $attachFILE);
            }
            $message->from(self::MAIL_FROM, self::APP_NAME);
            $message->subject($SUBJECT);
        });
	}

    public static function dayscount($from_date,$to_date){
		
		$datetime1 = new \DateTime($from_date);
		$datetime2 = new \DateTime($to_date);
		$interval = $datetime1->diff($datetime2);
		$interval=$interval->format('%a');
		return $interval+1;

	}

    public static function datestotalsundays($from_date, $to_date){
		$fmonth = date("m",strtotime($from_date));
		$fyears = date("Y",strtotime($from_date));
		$fmonthName = date("F", mktime(0, 0, 0, $fmonth));	
		
		$tmonth = date("m",strtotime($to_date));
		$tyears = date("Y",strtotime($to_date));
		$tmonthName = date("F", mktime(0, 0, 0, $tmonth));	

		// $fromdt =  $from_date;
		// $todt = $to_date;
		 
		$fromdt=date('Y-m-26 ',strtotime("First Day Of  $fmonthName $fyears")) ;
		$todt=date('Y-m-25 ',strtotime("Last Day of $tmonthName $tyears"));

		 
		// $fromdt = $datetime1->format('Y-d-m');
		// $todt = $datetime2->format('Y-d-m');
		// dd($todt); 
		$num_sundays='';                
		for ($i = 0; $i <= ((strtotime($todt) - strtotime($fromdt)) / 86400); $i++)
		{
			//echo date('d-m-Y',strtotime($fromdt) + ($i * 86400))."||".date('l',strtotime($fromdt) + ($i * 86400));
			//echo "<br>";
			if(date('l',strtotime($fromdt) + ($i * 86400)) == 'Sunday')
			{
					$num_sundays++;
			}    
		}
		return $num_sundays;
	}

	public static function excludesundays($from_date,$to_date){
		$startDate = new \DateTime($from_date);
		$endDate = new \DateTime($to_date);

		$sundays = array();

		while ($startDate <= $endDate) {
		    if ($startDate->format('w') == 0) {
		        $sundays[] = $startDate->format('Y-m-d');
		    }
		    
		    $startDate->modify('+1 day');
		}
		
		return $sundays;
	}

    public static function activeBranch() {
        $branchId = session('logged_session_data.branch_id');
        $roleId = session('logged_session_data.role_id');
        $selectedbranchId = session('selected_branchId');
        if($roleId==1) {
            return $selectedbranchId;
        } else {
            return $branchId;
        }
    }
    
    public static function addEmployeeLeaves($employee_id) {
        $EmployeeLeaves = \App\Model\EmployeeLeaves::where('employee_id', $employee_id)->first();
        $Employee = \App\Model\Employee::find($employee_id);
        if(!$Employee) {
            return false;
        }
        if(!$EmployeeLeaves) {
            $EmployeeLeaves = new \App\Model\EmployeeLeaves;
            $EmployeeLeaves->employee_id = $employee_id;
            $EmployeeLeaves->branch_id = $Employee->branch_id;

            $flag = 0;
            // 1 - Casual Leave
            $LeaveType = \App\Model\LeaveType::find(1);
            if($LeaveType) {
                $EmployeeLeaves->casual_leave = $LeaveType->num_of_day;
                $flag = 1;
            }
            // 2 - Sick Leave
            $LeaveType = \App\Model\LeaveType::find(2);
            if($LeaveType) {
                $EmployeeLeaves->sick_leave = $LeaveType->num_of_day;
                $flag = 1;
            }
            // 3 - Privilege Leave
            $LeaveType = \App\Model\LeaveType::find(3);
            if($LeaveType) {
                $EmployeeLeaves->privilege_leave = $LeaveType->num_of_day;
                $flag = 1;
            }

            // 4 - On Duty
            $LeaveType = \App\Model\LeaveType::find(4);
            if($LeaveType) {
                $EmployeeLeaves->OD = $LeaveType->num_of_day;
                $flag = 1;
            }
            
            // 7 - Comp off
            $LeaveType = \App\Model\LeaveType::find(7);
            if($LeaveType) {
                $EmployeeLeaves->comp_off = $LeaveType->num_of_day;
                $flag = 1;
            }
            
            // 5 - Maternity Leave
            $LeaveType = \App\Model\LeaveType::find(5);
            if($LeaveType && $Employee->gender=='Female' && $Employee->marital_status=='Married' && $Employee->no_of_child < 2) {
                $EmployeeLeaves->maternity_leave = $LeaveType->num_of_day;
                $flag = 1;
            }
            
            // 6 - Paternity Leave
            $LeaveType = \App\Model\LeaveType::find(6);
            if($LeaveType && $Employee->gender=='Male' && $Employee->marital_status=='Married' && $Employee->no_of_child < 2) {
                $EmployeeLeaves->paternity_leave = $LeaveType->num_of_day;
                $flag = 1;
            }

            if($flag==1) {
                $EmployeeLeaves->save();
                return $EmployeeLeaves;
            }
        }
    }
}
