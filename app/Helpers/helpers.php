<?php

use App\Model\MsSql;
use App\Model\Branch;
use GuzzleHttp\Client;
use App\Model\Employee;
use App\Model\WorkShift;
use App\Components\Common;
use App\Model\FrontSetting;
use Illuminate\Support\Carbon;
use App\Model\ManualAttendance;
use Illuminate\Support\Facades\DB;
use App\Lib\Enumerations\AppConstant;
use App\Repositories\LeaveRepository;
use Illuminate\Support\Facades\Config;
use App\Repositories\AttendanceRepository;

function keyFields($key)
{
    $classifications = ['department_id' => 'Department', 'designation_id' => 'Designation', 'role_id' => 'Role', 'bank_account' => 'Bank Account', 'bank_ifsc' => 'IFSC', 'bank_name' => 'Bank Name', 'branch_id' => 'Branch', 'work_shift_id' => 'Work Shift', 'supervisor_id' => 'HOD', 'date_of_birth' => 'Date of birth', 'date_of_joining' => 'Date of joining', 'date_of_leaving' => 'Date of leaving', 'finger_id' => 'Finger Print ID', 'gender' => 'Gender', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'emp_code' => 'Emp Code', 'status' => 'Status',];
    $payroll = ['salary_month' => 'Salary Month', 'pf_account_number' => 'PF Account Number', 'pf_status' => 'PF Status', 'salary_ctc' => 'CTC', 'salary_esi_stop' => 'Revision ESI Stop', 'salary_revision' => 'Date of revision', 'Basic' => 'Basic', 'Bonus' => 'Bonus', 'EarnedCTC' => 'Earned CTC', 'EarnedGross' => 'Earned Gross', 'ESI_Employee' => 'ESI Employee', 'ESI_Employee_Total' => 'ESI Employee Total', 'ESI_Employer' => 'ESI Employer', 'ESI_Employer_Total' => 'ESI Employer Total', 'ESI_TOTAL' => 'ESI TOTAL', 'Excess_Telephoone_Usage' => 'Excess Telephoone Usage', 'fullname' => 'Fullname', 'HRA' => 'HRA', 'Labour_Welfare' => 'Labour Welfare', 'LOP' => 'LOP', 'LTA' => 'LTA', 'Net_Salary' => 'Net Salary', 'Nett_Gross' => 'Nett Gross', 'OT_ESI_Employee' => 'OT ESI Employee', 'OT_ESI_Employer' => 'OT ESI Employer', 'OT_HOURS' => 'OT HOURS', 'OT_PER_HOUR' => 'OT PER HOUR', 'Over_Time' => 'Over Time', 'Other_earnings' => 'Other Earnings', 'PF_Employee' => 'PF Employee', 'PF_Employer' => 'PF Employer', 'PF_TOTAL' => 'PF TOTAL', 'Professional_Tax' => 'Professional Tax', 'Salary_Advance' => 'Salary Advance', 'Special_Allowance' => 'Special Allowance', 'TDS' => 'TDS', 'Total_Deduction' => 'Total Deduction', 'Total_Work_Days' => 'Total Work Days', 'pay_slip' => 'Payslip'];
    $additionals = ['user_name' => 'Username', 'address' => 'Address', 'cost_centre' => 'Cost Centre', 'email' => 'Email', 'overtime_status' => 'Overtime Status', 'emergency_contacts' => 'Emergency Contacts', 'marital_status' => 'Marital Status', 'no_of_child' => 'Number Of Child', 'pan_gir_no' => 'PAN', 'permanent_status' => 'Permanent Status', 'phone' => 'Phone', 'religion' => 'Religion', 'uan' => 'UAN', 'esi_card_number' => 'ESI Card Number'];
    // $attendance = ['Present' => 'Present','Absent' => 'Absent','TotalLeave' => 'Total Leaves','TotalHoliday' => 'Total Holidays','Weekoff' => 'Week Off','PublicHoliday' => 'Public Holiday','CasualLeave' => 'Casual Leave','SickLeave' => 'Sick Leave','PrivilegeLeave' => 'Privilege Leave','OnDuty' => 'On Duty','MaternityLeave' => 'Maternity Leave','PaternityLeave' => 'Paternity Leave','Permission' => 'Permission','CompOff' => 'Comp Off',];
    $attendance = ['days_presents' => 'Present', 'days_absents' => 'Absent', 'days_leaves' => 'Leaves', 'days_holidays' => 'Total Holidays', 'days_weekoffs' => 'Week Off'];
    $header = array_merge($classifications, $additionals, $attendance, $payroll);
    $defaults = [];
    if ($key == 'defaults') {
        if (isset($header['finger_id'])) {
            $defaults['finger_id'] = 'finger_id';
        }
        if (isset($payroll['fullname'])) {
            $defaults['fullname'] = 'fullname';
        }
    }
    $dateFunctions = ['date_of_birth' => 'date_of_birth', 'date_of_joining' => 'date_of_joining', 'date_of_leaving' => 'date_of_leaving', 'salary_esi_stop' => 'salary_esi_stop', 'salary_revision' => 'salary_revision'];
    $relationFunctions = [
        'department_id' => ['object' => 'Employee', 'relation' => 'department', 'field' => 'department_name'],
        'designation_id' => ['object' => 'Employee', 'relation' => 'designation', 'field' => 'designation_name'],
        'branch_id' => ['object' => 'Employee', 'relation' => 'branch', 'field' => 'branch_name'],
        'work_shift_id' => ['object' => 'Employee', 'relation' => 'workShift', 'field' => 'shift_name'],
    ];
    $otherFunctions = [
        'status' =>  function ($status) {
            return \App\Lib\Enumerations\UserStatus::statusList($status);
        }
    ];
    return isset($$key) ? $$key : [];
}

function shiftList()
{
    $workShift = WorkShift::all();
    $result = [];

    foreach ($workShift as $key => $value) {
        $result[$value->work_shift_id] = $value->shift_name;
    }

    return $result;
}

function branchList()
{
    $branches = Branch::all();
    $result = ['' => '---- Please Select ----'];

    foreach ($branches as $value) {
        $result[$value->branch_id] = $value->branch_name;
    }
    return $result;
}

function branches()
{
    $branches = Branch::all();
    $result = [0 => 'All Branches'];

    foreach ($branches as $value) {
        $result[$value->branch_id] = $value->branch_name;
    }
    return $result;
}

function monthlyDropdownList()
{
    // show last 20 months list to API drop down
    $result = [];
    $monthsGroups = DB::table('view_employee_in_out_data')->groupBy(DB::raw('MONTH(date), YEAR(date)'))->orderByDesc('date')->limit(20)->get();
    foreach ($monthsGroups as $key => $group) {
        $resultKey = date("Y-m", strtotime($group->date));
        $resultDisplay = date("m/Y", strtotime($group->date));
        $one['id'] = $key + 1;
        $one['month'] = $resultKey;
        $one['input'] = $resultKey;
        $one['display'] = $resultDisplay;
        $result[] = $one;
    }
    return $result;

    // $dt = strtotime(date('Y-m-01'));
    // $id=1;
    // for ($j = 0; $j <=6; $j++) {
    //     $resultKey = date("Y-m", strtotime(" -$j month", $dt));
    //     $resultDisplay = date("m/Y", strtotime(" -$j month", $dt));
    //     $one['id'] = $id;
    //     $one['month'] = $resultKey;
    //     $one['input'] = $resultKey;
    //     $one['display'] = $resultDisplay;
    //     $result[] = $one;
    //     $id++;
    // }
    // return $result;
}

function fullOrHalfDay($status)
{
    $array = array("0" => 'Half Day', "1" => 'Full Day');
    foreach ($array as $key => $value) {
        if ((int) $key == $status) {
            return $value;
        }
    }
}

function dateConvertFormtoDB($date)
{
    if (!empty($date)) {
        return date("Y-m-d", strtotime(str_replace('/', '-', $date)));
    }
}

function monthConvertFormtoDB($month)
{
    if (!empty($month)) {
        return date("Y-m", strtotime(str_replace('/', '-', $month)));
    }
}

function weekOffDateList($day, $month)
{
    // $start_date = $month . '-01';
    // $end_date   = date("Y-m-t", strtotime($start_date));

    $date = new DateTime('first ' . $day . ' of this month');
    // $thisMonth = $date->format('m');
    $thisMonth = date('m', strtotime($month));
    $dates = array();
    $i = 0;
    while ($date->format('m') === $thisMonth) {
        $i++;
        $dates[] .= $date->format('Y-m-d');
        $date->modify('next ' . $day);
    }
    return $dates;
}

function nextMonthFirstDate($inputDate)
{
    $date = new DateTime($inputDate);
    $date->modify('first day of next month');
    return $date->format('Y-m-d');
}

function getDateOnly($inputDate)
{
    $date = new DateTime($inputDate);
    return $date->format('d');
}

function getMonthOnly($inputDate)
{
    $date = new DateTime($inputDate);
    return $date->format('m');
}

function addMonth($date, $months)
{
    $datetime = new \DateTime($date);
    $datetime->modify('+' . $months . ' months');
    return $datetime->format('Y-m-d');
}

function subtractMonth($date, $months)
{
    $datetime = new \DateTime($date);
    $datetime->modify('-' . $months . ' months');
    return $datetime->format('Y-m-d');
}

function operateDays($date, $days, $oper = '+')
{
    $datetime = new \DateTime($date);
    $datetime->modify($oper . $days . ' days');
    return $datetime->format('Y-m-d');
}

function addYear($date, $years)
{
    $datetime = new \DateTime($date);
    $datetime->modify('+' . $years . ' years');
    return $datetime->format('Y-m-d');
}

function monthDiffs($date2, $date1)
{
    $d1 = new DateTime($date2);
    $d2 = new DateTime($date1);
    $Months = $d2->diff($d1);
    $howeverManyMonths = (($Months->y) * 12) + ($Months->m);
    return $howeverManyMonths;
}

function yearDiffs($date2, $date1)
{
    $d1 = new DateTime($date2);
    $d2 = new DateTime($date1);
    $OBJ = $d2->diff($d1);
    $howeverManyYears = $OBJ->y;
    return $howeverManyYears;
}

function daysDiffs($date2, $date1)
{
    // $date2=new DateTime($date2);
    // $date1=new DateTime($date1);

    $datediff = strtotime($date2) - strtotime($date1);
    return ($datediff) / 60 / 60 / 24;
}

function monthConvertDBtoForm($month)
{
    if (!empty($month)) {
        $month = strtotime($month);
        return date('Y/m', $month);
    }
}
function dateConvertDBtoForm($date)
{
    if (!empty($date)) {
        $date = strtotime($date);
        return date('d/m/Y', $date);
    }
}
function dateTimeConvertDBtoForm($date)
{
    if (!empty($date)) {
        $date = strtotime($date);
        return date('d/m/Y H:i:s', $date);
    }
}
function findMonthFromToDate($start_date, $end_date)
{

    $target = strtotime($start_date);

    $workingDate = [];

    while ($target <= strtotime(date("Y-m-d", strtotime($end_date)))) {
        $temp = [];
        $temp['date'] = date('Y-m-d', $target);
        $temp['day'] = date('d', $target);
        $temp['day_name'] = date('D', $target);
        $workingDate[] = $temp;
        $target += (60 * 60 * 24);
    }
    // dd($workingDate);
    return $workingDate;
}
function employeeInfo()
{
    return DB::select("call SP_getEmployeeInfo('" . session('logged_session_data.employee_id') . "')");
}

function permissionCheck()
{

    $role_id = session('logged_session_data.role_id');
    return $result = json_decode(DB::table('menus')->select('menu_url')
        ->join('menu_permission', 'menu_permission.menu_id', '=', 'menus.id')
        ->where('menu_permission.role_id', '=', $role_id)
        ->whereNotNull('action')->get()->toJson(), true);
}

function showMenu()
{
    $role_id = session('logged_session_data.role_id');
    $modules = json_decode(DB::table('modules')->get()->toJson(), true);
    $menus = json_decode(DB::table('menus')
        ->select(DB::raw('menus.id, menus.name, menus.menu_url, menus.parent_id, menus.module_id'))
        ->join('menu_permission', 'menu_permission.menu_id', '=', 'menus.id')
        ->where('menu_permission.role_id', $role_id)
        ->where('menus.status', 1)
        ->whereNull('action')
        ->orderBy('menus.id', 'ASC')
        ->get()->toJson(), true);

    $sideMenu = [];
    if ($menus) {
        foreach ($menus as $menu) {
            if (!isset($sideMenu[$menu['module_id']])) {
                $moduleId = array_search($menu['module_id'], array_column($modules, 'id'));

                $sideMenu[$menu['module_id']] = [];
                $sideMenu[$menu['module_id']]['id'] = $modules[$moduleId]['id'];
                $sideMenu[$menu['module_id']]['name'] = $modules[$moduleId]['name'];
                $sideMenu[$menu['module_id']]['icon_class'] = $modules[$moduleId]['icon_class'];
                $sideMenu[$menu['module_id']]['menu_url'] = '#';
                $sideMenu[$menu['module_id']]['parent_id'] = '';
                $sideMenu[$menu['module_id']]['module_id'] = $modules[$moduleId]['id'];
                $sideMenu[$menu['module_id']]['sub_menu'] = [];
            }
            if ($menu['parent_id'] == 0) {
                $sideMenu[$menu['module_id']]['sub_menu'][$menu['id']] = $menu;
                $sideMenu[$menu['module_id']]['sub_menu'][$menu['id']]['sub_menu'] = [];
            } else {
                array_push($sideMenu[$menu['module_id']]['sub_menu'][$menu['parent_id']]['sub_menu'], $menu);
            }
        }
    }

    return $sideMenu;
}

function convartMonthAndYearToWord($data)
{
    $monthAndYear = explode('-', $data);

    $month = $monthAndYear[1];
    $dateObj = DateTime::createFromFormat('!m', $month);
    $monthName = $dateObj->format('F');
    $year = $monthAndYear[0];

    return $monthAndYearName = $monthName . " " . $year;
}

function employeeAward()
{
    return ['Employee of the Month' => 'Employee of the Month', 'Employee of the Year' => 'Employee of the Year', 'Best Employee' => 'Best Employee'];
}

function weekedName()
{
    $week = array("Sun" => 'Sunday', "Mon" => 'Monday', "Tue" => 'Tuesday', "Wed" => 'Wednesday', "Thu" => 'Thursday', "Fri" => 'Friday', "Sat" => 'Saturday');
    return $week;
}

function attStatus($att_status)
{
    $status = array("1" => 'Present', "2" => 'Absent', "3" => 'Leave', "4" => 'Holiday', "5" => 'Future', "6" => 'Update', "7" => 'Error', "8" => 'Missing OUT Punch', "9" => 'Missing In Punch', '10' => 'Less Hours', '11' => 'Comp Off');
    foreach ($status as $key => $value) {
        if ((int) $key == $att_status) {
            return $value;
        }
    }
}

function userStatus($att_status)
{
    $status = \App\Lib\Enumerations\UserStatus::statusList($att_status);
    return $status;
    // $status = array("0" => 'Probation Period', "1" => 'Active', "2" => 'Inactive', "3" => 'Terminated', "4" => 'Permanent');
    // foreach ($status as $key => $value) {
    //     if ((int) $key == $att_status) {
    //         return $value;
    //     }
    // }
}

function allDevices()
{
    $options = [];
    $device = MsSql::select('device_name')->groupBy('device_name')->get('device_name')->toArray();
    $manual = ManualAttendance::select('device_name')->groupBy('device_name')->get('device_name')->toArray();
    $devices = (object) array_merge($device, $manual);

    foreach ($devices as $value) {
        $options[] = $value['device_name'] != null ? $value['device_name'] : "N/A";
    }

    return $options;
}

function getSundays($yearmonth, $returnBy = 'date')
{
    $date = $yearmonth . '-01';
    $first_day = date('N', strtotime($date));
    $first_day = 7 - $first_day + 1;
    $last_day =  date('t', strtotime($date));
    $days = array();
    for ($i = $first_day; $i <= $last_day; $i = $i + 7) {
        if ($returnBy == 'date') {
            $days[] = $yearmonth . '-' . $i;
        } else {
            $days[] = $i;
        }
    }
    return  $days;
}

// $days = getSundays(2016,04);

function findMonthToAllDate($month)
{
    $start_date = $month . '-01';
    $end_date = date("Y-m-t", strtotime($start_date));

    $target = strtotime($start_date);
    $workingDate = [];
    while ($target <= strtotime(date("Y-m-d", strtotime($end_date)))) {
        $temp = [];
        $temp['date'] = date('Y-m-d', $target);
        $temp['day'] = date('d', $target);
        $temp['day_name'] = date('D', $target);
        $workingDate[] = $temp;
        $target += (60 * 60 * 24);
    }
    return $workingDate;
}

function findFromDateToDateToAllDate($start_date, $end_date)
{
    $target = strtotime($start_date);
    $workingDate = [];
    while ($target <= strtotime(date("Y-m-d", strtotime($end_date)))) {
        $temp = [];
        $temp['date'] = date('Y-m-d', $target);
        $temp['day'] = date('d', $target);
        $temp['day_name'] = date('D', $target);
        $workingDate[] = $temp;
        $target += (60 * 60 * 24);
    }
    return $workingDate;
}

function findMonthToStartDateAndEndDate($month)
{
    $start_date = $month . '-01';
    $end_date = date("Y-m-t", strtotime($start_date));
    $data = [
        'start_date' => $start_date,
        'end_date' => $end_date,
    ];
    return $data;
}

function getFrontData()
{
    $setting = FrontSetting::orderBy('id', 'desc')->first();

    return $setting;
}

function password($count)
{
    $result = "";
    for ($value = 0; $value <= $count; $value++) {
        $result = $result . '*';
    }
    return $result;
}

function getRouteData($search)
{
    $options = [];

    $qry = '1 ';
    if ($search != '') {
        $qry = 'menus.menu_url LIKE  %' . $search . '%';
    }
    $menus = DB::table('menus')->where('status', AppConstant::$OKEY)
        ->where('menus.menu_url', '!=', null)
        ->join('menu_permission', 'menu_permission.menu_id', 'menus.id')
        ->where('menu_permission.role_id', session('logged_session_data.role_id'))
        ->whereRaw($qry)
        ->orderBy('menus.name')
        ->get();

    foreach ($menus as $value) {
        $options[$value->menu_url] = $value->name;
    }

    return $options;
}

function appName()
{
    return Common::APP_NAME;
}

function float2($number)
{
    return number_format((float) $number, 2, '.', '');
}

function float1($number)
{
    return number_format((float) $number, 1, '.', '');
}

function getModelData()
{
    $options = [
        'App\User',
        'App\Model\Employee',
    ];

    return $options;
}

function amountToWords($number)
{
    $no = floor($number);
    $point = round($number - $no, 2) * 100;
    $hundred = null;
    $digits_1 = strlen($no);
    $i = 0;
    $str = array();
    $words = array(
        '0' => '', '1' => 'one', '2' => 'two',
        '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
        '7' => 'seven', '8' => 'eight', '9' => 'nine',
        '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
        '13' => 'thirteen', '14' => 'fourteen',
        '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
        '18' => 'eighteen', '19' => 'nineteen', '20' => 'twenty',
        '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
        '60' => 'sixty', '70' => 'seventy',
        '80' => 'eighty', '90' => 'ninety'
    );
    $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str[] = ($number < 21) ? $words[$number] .
                " " . $digits[$counter] . $plural . " " . $hundred
                :
                $words[floor($number / 10) * 10]
                . " " . $words[$number % 10] . " "
                . $digits[$counter] . $plural . " " . $hundred;
        } else $str[] = null;
    }
    $str = array_reverse($str);
    $result = implode('', $str);
    $points = ($point) ?
        "." . $words[$point / 10] . " " .
        $words[$point = $point % 10] : '';
    return $result . ($points > 0 ? " and  " . $points . " paise" : '');
}

function timeDiffInHoursFormat($from, $to)
{

    if ($from > $to) {
        $startTime = Carbon::parse($from);
        $endTime = Carbon::parse('23:59:00');
        $totalDuration1 =  $startTime->diff($endTime)->format('%H:%I:%S');

        $startTime = Carbon::parse('00:00:00');
        $endTime = Carbon::parse($to);
        $totalDuration2 =  $startTime->diff($endTime)->format('%H:%I:%S');

        $totalDuration1 = strtotime($totalDuration1);
        $totalDuration2 = strtotime($totalDuration2);

        $TOTAL_DURATIONS = ($totalDuration2 + $totalDuration1 + 60) - strtotime('00:00:00');
        $TOTAL_HOURS['FROM'] = date('H:i:s', strtotime($from));
        $TOTAL_HOURS['TO'] = date('H:i:s', strtotime($to));
        $TOTAL_HOURS['TOTAL_HOURS'] = date('H:i:s', ($TOTAL_DURATIONS));
        return $TOTAL_HOURS;
    }
    $startTime = Carbon::parse($from);
    $endTime = Carbon::parse($to);
    $TOTAL_DURATIONS =  strtotime($startTime->diff($endTime)->format('%H:%I:%S'));

    $TOTAL_HOURS['FROM'] = date('H:i:s', strtotime($from));
    $TOTAL_HOURS['TO'] = date('H:i:s', strtotime($to));
    $TOTAL_HOURS['TOTAL_HOURS'] =  $startTime->diff($endTime)->format('%H:%I:%S');
    return $TOTAL_HOURS;
}

function truncateNum($input, $by = 0.5)
{
    // 33.22	0.5	    33
    // 50.45	0.5	    50
    // 83.91	0.5	    83.5
    $mod = fmod($input, $by);
    return $input - $mod;
}

function activeBranch()
{
    $branchId = session('logged_session_data.branch_id');
    $roleId = session('logged_session_data.role_id');
    $selectedbranchId = session('selected_branchId');
    if ($roleId == 1) {
        return $selectedbranchId;
    } else {
        return $branchId;
    }
}

function supervisorIds()
{
    $LoggedEmployee = Employee::loggedEmployee();
    $supervisorIds = $LoggedEmployee->supervisorIds();
    return $supervisorIds;
}

function plAskBefore()
{
    // as per given leave Leave Policy.docx
    // For Privilege Leave, the application should be made at least 20 business days in advance
    $days = LeaveRepository::BEFORE_DAYS;
    $TODAY = date('Y-m-d');
    $before_date = operateDays($TODAY, $days);
    return $before_date;
}

function debugBackLog($log = false, $level = 5)
{
    $debug = debug_backtrace();
    $from = '';
    for ($k = 0; $k <= $level; $k++) {
        $from .= isset($debug[$k]) && isset($debug[$k]['file']) ? ('FILE: ' . $debug[$k]['file'] . PHP_EOL) : '';
        $from .= isset($debug[$k]) && isset($debug[$k]['line']) ? ('LINE: ' . $debug[$k]['line'] . PHP_EOL) : '';
        $fromFunction = isset($debug[$k]) && isset($debug[$k]['function']) ? ('FUNCTION: ' . $debug[$k]['function'] . PHP_EOL) : '';
        $from .= $fromFunction;
    }
    $message = '<pre>' . print_r($from, 1) . '</pre>';
    return $log ? $from : $message;
}

// 60 minutes result 01:00, 65 minutes result 01:05
function convertMinutesToHour($time, $format = '%02d:%02d')
{
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

// 00:01 hour result 1, 01:05 minutes result 65
function convertHoursMinuteToMinute($strHourMinute)
{
    $from = date('Y-m-d 00:00:00');
    $to = date('Y-m-d ' . $strHourMinute);
    $diff = strtotime($to) - strtotime($from);
    $minutes = $diff / 60;
    return (int) $minutes;
}

function flog($data)
{
    $params = func_get_args();
    $path = storage_path() . '/logs/custom_log-' . date('d-m-Y') . '.log';
    foreach ($params as $key => $each_data) {
        $content = '(' . ($key + 1) . ' ' . Carbon::now() . ') ' . print_r($each_data, 1);
        file_put_contents($path, $content . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    file_put_contents($path, PHP_EOL, FILE_APPEND | LOCK_EX);
}

function elog($finger_id, $data)
{
    if (!is_string($finger_id)) {
        return null;
    }
    $params = func_get_args();
    if (array_search($finger_id, Common::TRACE ?? []) !== false) {
        flog($params);
    }
}

function isJson($string)
{
    return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
}

function minutes($time)
{
    $time = explode(':', $time);
    return ($time[0] * 60) + ($time[1]) + ($time[2] / 60);
}

function hoursandmins($time, $format = '%02d:%02d')
{
    if ($time < 1) {
        return;
    }
    $hours = floor($time / 60);
    $minutes = ($time % 60);
    return sprintf($format, $hours, $minutes);
}

function fnHeadStatus($status)
{
    switch ($status) {
        case 1:
            return `<span class="label label-info">@lang('common.pending')</span>`;
            break;
        case 2:
            return `<span class="label label-success">@lang('common.approved')</span>`;
            break;

        case 3:
            return `<span class="label label-danger">@lang('common.rejected')</span>`;
            break;
        case 4:
            return `<span class="label label-info">@lang('common.passed')</span>`;
            break;

        default:
            return `<span class="label label-info">None</span>`;
            break;
    }
}

function policyList($id = '')
{
    $options = ['0' => 'Common Policy', '1' => 'Branch Policy', '2' => 'Manager Task/Checklist', '3' => 'Holiday List'];

    if ($id != '') {
        return $options[$id];
    }

    return $options;
}
