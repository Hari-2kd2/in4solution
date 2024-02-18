@php
use App\Model\Employee;
use App\Model\PayrollUpload;
use App\Model\EmployeeLeaves;
use Illuminate\Support\Carbon;
use App\Model\LeaveApplication;
use App\Lib\Enumerations\LeaveStatus;
use App\Repositories\SalaryRepository;
@endphp
@extends('admin.master')
@section('content')
@section('title','Test Page')
<div class="container-fluid">
	<div class="row bg-title">
		<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
		   <ol class="breadcrumb">
				<li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i> Dashboard</a></li>
				<li>@yield('title')</li>
			</ol>
		</div>	
		<div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
			<a href="{{ route('user.create') }}"  class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i class="fa fa-plus-circle" aria-hidden="true"></i> Add User</a>
		</div>	
	</div>
                
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-info">
				<div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
				<div class="panel-wrapper collapse in" aria-expanded="true">
					<div class="panel-body">
						@if(session()->has('success'))
							<div class="alert alert-success alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
								<i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
							</div>
						@endif
						@if(session()->has('error'))
							<div class="alert alert-danger alert-dismissable">
								<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
								<i class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
							</div>
						@endif
						<div class="table-responsive">
							{{-- {{ asset('public/storage/faceId/e197355a1598a77637f925e45480adee.jpg') }} --}}
							@php
							$TODAY = '2024-01-01';
							$IS_NEW_YEAR = date('z', strtotime($TODAY));
							echo '<p>IS_NEW_YEAR='.$IS_NEW_YEAR.'</p>';
							// $LeaveApplicationMonths = LeaveApplication::selectRaw('MONTH(application_from_date) AS LeaveMonth, application_from_date')
							// 	->where('status', LeaveStatus::$PENDING)
							// 	->get();
							// 	foreach ($LeaveApplicationMonths as $key => $LeaveApplication) {
							// 		echo '<br>Leave From Date = '. $LeaveApplication->application_from_date;

							// 		$leave_date = $LeaveApplication->application_from_date;
							// 		$sdate = new DateTime( $leave_date );
							// 		$edate = new DateTime( $leave_date );
									
							// 		$leave_day = $sdate->format('d');
							// 		$leave_month_last_day = $sdate->format('t');
							// 		$sdate = $sdate->modify('previous month');
							// 		if($leave_day>=SalaryRepository::PAYROLL_START_DATE) {
							// 			$salary_month_key = new DateTime( $leave_date );
							// 			$salary_month_key = $salary_month_key->modify('next month');
							// 			$salary_month_key = $salary_month_key->format('Y-m-01');
							// 			echo '<p style="font-weight:bold;color:red">$salary_month_key='. $salary_month_key.'</p>';
							// 		} else {
							// 			$salary_month_key = new DateTime( $leave_date );
							// 			$salary_month_key = $salary_month_key->format('Y-m-01');
							// 			echo '<p style="font-weight:bold;color:green">$salary_month_key='. $salary_month_key.'</p>';
							// 		}
							// 		$checkFreeze = PayrollUpload::where('salary_key', $salary_month_key)->where('salary_freeze', 1)->count();
							// 		$previous_month_date = $sdate->format('Y-m-'.SalaryRepository::PAYROLL_START_DATE);
							// 		$current_month_date = $edate->format('Y-m-'.SalaryRepository::PAYROLL_END_DATE);
							// 		echo '<br>$leave_day='. $leave_day.', leave_month_last_day='.$leave_month_last_day;
							// 		echo '<p style="font-weight:bold;color:'. ($checkFreeze ? 'red' : 'green') .'">'. ($checkFreeze ? 'Set Expired!' : 'Still Valid') .'</p>';
							// 		// echo '<br>$previous_month_date='. $previous_month_date;
							// 		// echo '<br>$current_month_date='. $current_month_date;
							// 		echo '<br>-----------------------------------<br>';
							// 	}
								
								// echo '<br>-----------------------------------<br>';
								// $salary_date = '2023-10-25';
								// $sdate = new DateTime( $salary_date );
								// $edate = new DateTime( $salary_date );
								// $sdate = $sdate->modify('previous month');
								// $previous_month_date = $sdate->format('Y-m-'.SalaryRepository::PAYROLL_START_DATE);
								// $current_month_date = $edate->format('Y-m-'.SalaryRepository::PAYROLL_END_DATE);
								// echo '<br>$previous_month_date='. $previous_month_date;
								// echo '<br>$current_month_date='. $current_month_date;
								// echo '<br>-----------------------------------<br>';

							// $LeaveApplication = new \App\Model\LeaveApplication;
							// echo '<pre>'.print_r($LeaveApplication->leaveStatus(), 1).'</pre>';
							// echo "day number " . date('z');
							// echo '<br>NAME='. NAME;
							// echo '<br>EID='. EID;
							// echo $output = truncateNum(4.5);

							// $from = '21:01:00';
							// $to = '05:30:00';
							// $res = timeDiffInHoursFormat($from, $to);
							// echo '<pre>'.print_r($res, 1).'</pre>';
							// $from = '09:30:00';
							// $to = '18:00:00';
							// $res = timeDiffInHoursFormat($from, $to);
							// echo '<pre>'.print_r($res, 1).'</pre>';

							// $price[]=0.00;
							// for ($i=1; $i <=20 ; $i++) { 
							// 	$price[] = rand(0, 99) . '.' . rand(0, 99);
							// }
							// $by = 0.5;
							// echo '<table class="table"><tr><td>Input</td><td>Mod</td><td>Output</td></tr>';
							// 	foreach ($price as $key => $input) {
							// 		$mod = fmod($input, $by);
							// 		$output = truncateNum($input, $by);
							// 		echo "<tr><td>$input</td><td>$mod</td><td>$output</td></tr>";
							// 	}
							// echo '</table';
							
							// $hour[] = '00:01';
							// $hour[] = '01:05';
							// foreach ($hour as $key => $h) {
							// 	# code...
							// 	echo '<br>hour('.$h.') = '.convertHoursMinuteToMinute($h) .'<br>';
							// }

							// $ff[] = -101;
							// $ff[] = 1;
							// $ff[] = 0;
							// $ff[] = '0';
							// $ff[] = '10.5';
							// $ff[] = '-1.5';
							// $ff[] = 'sdsd';
							// $ff[] = '';
							// $ff[] = 2;
							// $ff[] = '{"2"}';
							// $ff[] = '[2]';
							// $ff[] = '{[2]}';
							// $ff[] = '{["2":"test"]}';
							// $ff[] = '{"2":"test"]';
							// $ff[] = '{"days":183,"balance":183,"from":"20/12/2023","to":"20/06/2024","durations":"6 Months"}';
							// foreach ($ff as $key => $tes) {
							// 	echo '<p>tes='. $tes .' isJson= '. isJson($tes) .'</p>';
							// 	echo '<p>&nbsp;---------</p>';
							// }
							// $ends = ['01:31:00','02:59:00', '00:31:01', '00:29:00'];
							// $startTime = Carbon::parse('00:00:00');
							// foreach ($ends as $key => $value) {
							// 	$finishTime = Carbon::parse($value);
							// 	$overTimes = $finishTime->diffInMinutes($startTime);
							// 	$hours = truncateNum($overTimes / 60);
							// 	echo '<br>hours = '.$hours;
							// }

							$leave=request()->get('leave', null);
							if($leave=='update') {
								$Leaves = [['code' => 'T0002', 'casual_leave' => 2.0, 'privilege_leave' => 36.0, 'sick_leave' => 34.0],['code' => 'T0009', 'casual_leave' => 0.0, 'privilege_leave' => 32.0, 'sick_leave' => 8.0],['code' => 'T0010', 'casual_leave' => 1.5, 'privilege_leave' => 39.0, 'sick_leave' => 2.5, 'RH AVAIL'],['code' => 'T0016', 'casual_leave' => 0.0, 'privilege_leave' => 32.0, 'sick_leave' => 0.0],['code' => 'T0020', 'casual_leave' => 0.0, 'privilege_leave' => 10.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0029', 'casual_leave' => 1.0, 'privilege_leave' => 8.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0035', 'casual_leave' => 0.0, 'privilege_leave' => 8.0, 'sick_leave' => 0.0],['code' => 'T0042', 'casual_leave' => 0.0, 'privilege_leave' => 42.0, 'sick_leave' => 0.0],['code' => 'T0053', 'casual_leave' => 1.5, 'privilege_leave' => 9.0, 'sick_leave' => 5.0],['code' => 'T0060', 'casual_leave' => 0.5, 'privilege_leave' => 45.0, 'sick_leave' => 9.0],['code' => 'T0063', 'casual_leave' => 0.0, 'privilege_leave' => 25.0, 'sick_leave' => 3.5, 'RH AVAIL'],['code' => 'T0067', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0072', 'casual_leave' => 0.0, 'privilege_leave' => 11.0, 'sick_leave' => 21.0],['code' => 'T0073', 'casual_leave' => 0.0, 'privilege_leave' => 2.0, 'sick_leave' => 0.0],['code' => 'T0074', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0088', 'casual_leave' => 0.0, 'privilege_leave' => 45.0, 'sick_leave' => 26.0, 'RH AVAIL'],['code' => 'T0089', 'casual_leave' => 5.0, 'privilege_leave' => 9.0, 'sick_leave' => 7.0, 'RH AVAIL'],['code' => 'T0091', 'casual_leave' => 0.0, 'privilege_leave' => 1.0, 'sick_leave' => 0.0],['code' => 'T0100', 'casual_leave' => 0.0, 'privilege_leave' => 28.0, 'sick_leave' => 1.0],['code' => 'T0103', 'casual_leave' => 0.0, 'privilege_leave' => 42.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0108', 'casual_leave' => 6.0, 'privilege_leave' => 33.0, 'sick_leave' => 45.0],['code' => 'T0115', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 6.5],['code' => 'T0118', 'casual_leave' => 0.0, 'privilege_leave' => 24.0, 'sick_leave' => 0.5, 'RH AVAIL'],['code' => 'T0119', 'casual_leave' => 0.0, 'privilege_leave' => 38.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0127', 'casual_leave' => 0.0, 'privilege_leave' => 17.0, 'sick_leave' => 0.0],['code' => 'T0128', 'casual_leave' => 0.0, 'privilege_leave' => 39.0, 'sick_leave' => 40.5],['code' => 'T0132', 'casual_leave' => 3.5, 'privilege_leave' => 41.0, 'sick_leave' => 43.0, 'RH AVAIL'],['code' => 'T0137', 'casual_leave' => 6.0, 'privilege_leave' => 45.0, 'sick_leave' => 44.5],['code' => 'T0145', 'casual_leave' => 0.0, 'privilege_leave' => 39.0, 'sick_leave' => 0.5, 'RH AVAIL'],['code' => 'T0150', 'casual_leave' => 2.0, 'privilege_leave' => 45.0, 'sick_leave' => 25.0],['code' => 'T0151', 'casual_leave' => 0.0, 'privilege_leave' => 36.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0155', 'casual_leave' => 0.5, 'privilege_leave' => 35.0, 'sick_leave' => 1.0],['code' => 'T0164', 'casual_leave' => 3.0, 'privilege_leave' => 37.0, 'sick_leave' => 24.0, 'RH AVAIL'],['code' => 'T0165', 'casual_leave' => 0.0, 'privilege_leave' => 10.0, 'sick_leave' => 0.5, 'RH AVAIL'],['code' => 'T0167', 'casual_leave' => 5.0, 'privilege_leave' => 45.0, 'sick_leave' => 43.0],['code' => 'T0171', 'casual_leave' => 0.0, 'privilege_leave' => 45.0, 'sick_leave' => 2.0],['code' => 'T0172', 'casual_leave' => 0.5, 'privilege_leave' => 25.0, 'sick_leave' => 9.5],['code' => 'T0178', 'casual_leave' => 1.5, 'privilege_leave' => 6.0, 'sick_leave' => 13.0],['code' => 'T0180', 'casual_leave' => 0.5, 'privilege_leave' => 41.0, 'sick_leave' => 31.0],['code' => 'T0182', 'casual_leave' => 2.0, 'privilege_leave' => 39.0, 'sick_leave' => 7.0, 'RH AVAIL'],['code' => 'T0188', 'casual_leave' => 0.0, 'privilege_leave' => 8.0, 'sick_leave' => 2.0],['code' => 'T0190', 'casual_leave' => 0.5, 'privilege_leave' => 0.0, 'sick_leave' => 0.5],['code' => 'T0191', 'casual_leave' => 6.0, 'privilege_leave' => 6.0, 'sick_leave' => 25.0, 'RH AVAIL'],['code' => 'T0196', 'casual_leave' => 0.0, 'privilege_leave' => 39.0, 'sick_leave' => 7.5],['code' => 'T0197', 'casual_leave' => 4.0, 'privilege_leave' => 24.0, 'sick_leave' => 40.0],['code' => 'T0201', 'casual_leave' => 0.0, 'privilege_leave' => 22.0, 'sick_leave' => 2.5],['code' => 'T0208', 'casual_leave' => 0.0, 'privilege_leave' => 2.0, 'sick_leave' => 1.5],['code' => 'T0212', 'casual_leave' => 3.0, 'privilege_leave' => 0.0, 'sick_leave' => 8.5],['code' => 'T0213', 'casual_leave' => 3.5, 'privilege_leave' => 0.0, 'sick_leave' => 3.0],['code' => 'T0218', 'casual_leave' => 4.0, 'privilege_leave' => 34.0, 'sick_leave' => 7.0],['code' => 'T0220', 'casual_leave' => 0.0, 'privilege_leave' => 5.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0221', 'casual_leave' => 2.5, 'privilege_leave' => 31.0, 'sick_leave' => 9.0, 'RH AVAIL'],['code' => 'T0226', 'casual_leave' => 3.5, 'privilege_leave' => 20.0, 'sick_leave' => 25.0],['code' => 'T0228', 'casual_leave' => 0.0, 'privilege_leave' => 21.0, 'sick_leave' => 2.5, 'RH AVAIL'],['code' => 'T0238', 'casual_leave' => 5.0, 'privilege_leave' => 15.0, 'sick_leave' => 24.0],['code' => 'T0239', 'casual_leave' => 2.5, 'privilege_leave' => 9.0, 'sick_leave' => 42.0],['code' => 'T0240', 'casual_leave' => 1.0, 'privilege_leave' => 22.0, 'sick_leave' => 19.0],['code' => 'T0245', 'casual_leave' => 0.0, 'privilege_leave' => 2.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0262', 'casual_leave' => 1.0, 'privilege_leave' => 2.0, 'sick_leave' => 2.0, 'RH AVAIL'],['code' => 'T0263', 'casual_leave' => 0.0, 'privilege_leave' => 35.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0272', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0276', 'casual_leave' => 0.5, 'privilege_leave' => 12.0, 'sick_leave' => 5.0],['code' => 'T0280', 'casual_leave' => 0.5, 'privilege_leave' => 34.0, 'sick_leave' => 7.0, 'RH AVAIL'],['code' => 'T0281', 'casual_leave' => 6.0, 'privilege_leave' => 13.0, 'sick_leave' => 19.0],['code' => 'T0282', 'casual_leave' => 0.0, 'privilege_leave' => 45.0, 'sick_leave' => 39.0],['code' => 'T0285', 'casual_leave' => 0.0, 'privilege_leave' => 28.0, 'sick_leave' => 8.0],['code' => 'T0286', 'casual_leave' => 0.5, 'privilege_leave' => 19.0, 'sick_leave' => 0.5, 'RH AVAIL'],['code' => 'T0288', 'casual_leave' => 0.0, 'privilege_leave' => 12.0, 'sick_leave' => 9.0],['code' => 'T0291', 'casual_leave' => 3.0, 'privilege_leave' => 31.0, 'sick_leave' => 20.0],['code' => 'T0292', 'casual_leave' => 1.0, 'privilege_leave' => 45.0, 'sick_leave' => 34.0, 'RH AVAIL'],['code' => 'T0296', 'casual_leave' => 0.0, 'privilege_leave' => 23.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0298', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0300', 'casual_leave' => 0.0, 'privilege_leave' => 29.0, 'sick_leave' => 11.0, 'RH AVAIL'],['code' => 'T0302', 'casual_leave' => 6.0, 'privilege_leave' => 42.0, 'sick_leave' => 27.0],['code' => 'T0303', 'casual_leave' => 0.0, 'privilege_leave' => 43.0, 'sick_leave' => 23.5, 'RH AVAIL'],['code' => 'T0304', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0305', 'casual_leave' => 2.0, 'privilege_leave' => 43.0, 'sick_leave' => 31.0, 'RH AVAIL'],['code' => 'T0306', 'casual_leave' => 1.0, 'privilege_leave' => 45.0, 'sick_leave' => 38.0],['code' => 'T0307', 'casual_leave' => 1.0, 'privilege_leave' => 4.0, 'sick_leave' => 6.0, 'RH AVAIL'],['code' => 'T0309', 'casual_leave' => 0.0, 'privilege_leave' => 7.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0310', 'casual_leave' => 1.0, 'privilege_leave' => 37.0, 'sick_leave' => 2.5, 'RH AVAIL'],['code' => 'T0312', 'casual_leave' => 0.0, 'privilege_leave' => 4.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0315', 'casual_leave' => 4.5, 'privilege_leave' => 41.0, 'sick_leave' => 37.0, 'RH AVAIL'],['code' => 'T0317', 'casual_leave' => 5.0, 'privilege_leave' => 1.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0318', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0319', 'casual_leave' => 2.0, 'privilege_leave' => 3.0, 'sick_leave' => 32.0],['code' => 'T0320', 'casual_leave' => 3.5, 'privilege_leave' => 0.0, 'sick_leave' => 11.0],['code' => 'T0321', 'casual_leave' => 6.0, 'privilege_leave' => 27.0, 'sick_leave' => 18.0],['code' => 'T0325', 'casual_leave' => 0.0, 'privilege_leave' => 32.0, 'sick_leave' => 6.0, 'RH AVAIL'],['code' => 'T0327', 'casual_leave' => 0.0, 'privilege_leave' => 29.0, 'sick_leave' => 8.0, 'RH AVAIL'],['code' => 'T0328', 'casual_leave' => 0.5, 'privilege_leave' => 23.0, 'sick_leave' => 14.5, 'RH AVAIL'],['code' => 'T0329', 'casual_leave' => 0.0, 'privilege_leave' => 12.0, 'sick_leave' => 3.0, 'RH AVAIL'],['code' => 'T0332', 'casual_leave' => 0.0, 'privilege_leave' => 27.0, 'sick_leave' => 3.0, 'RH AVAIL'],['code' => 'T0334', 'casual_leave' => 0.5, 'privilege_leave' => 15.0, 'sick_leave' => 7.0, 'RH AVAIL'],['code' => 'T0335', 'casual_leave' => 0.5, 'privilege_leave' => 27.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0338', 'casual_leave' => 0.0, 'privilege_leave' => 4.0, 'sick_leave' => 0.0],['code' => 'T0341', 'casual_leave' => 0.0, 'privilege_leave' => 4.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0343', 'casual_leave' => 0.0, 'privilege_leave' => 33.0, 'sick_leave' => 34.0],['code' => 'T0344', 'casual_leave' => 0.0, 'privilege_leave' => 33.0, 'sick_leave' => 32.0],['code' => 'T0348', 'casual_leave' => 0.0, 'privilege_leave' => 13.0, 'sick_leave' => 0.5, 'RH AVAIL'],['code' => 'T0349', 'casual_leave' => 0.0, 'privilege_leave' => 2.0, 'sick_leave' => 0.0],['code' => 'T0350', 'casual_leave' => 0.0, 'privilege_leave' => 26.0, 'sick_leave' => 6.5, 'RH AVAIL'],['code' => 'T0352', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 2.5, 'RH AVAIL'],['code' => 'T0353', 'casual_leave' => 0.0, 'privilege_leave' => 25.0, 'sick_leave' => 2.0, 'RH AVAIL'],['code' => 'T0355', 'casual_leave' => 0.0, 'privilege_leave' => 18.0, 'sick_leave' => 1.5, 'RH AVAIL'],['code' => 'T0356', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 3.5, 'RH AVAIL'],['code' => 'T0357', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 16.5],['code' => 'T0360', 'casual_leave' => 0.5, 'privilege_leave' => 5.0, 'sick_leave' => 4.0, 'RH AVAIL'],['code' => 'T0361', 'casual_leave' => 4.5, 'privilege_leave' => 2.0, 'sick_leave' => 5.0],['code' => 'T0363', 'casual_leave' => 2.0, 'privilege_leave' => 28.0, 'sick_leave' => 10.0, 'RH AVAIL'],['code' => 'T0367', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0370', 'casual_leave' => 3.0, 'privilege_leave' => 36.0, 'sick_leave' => 10.0],['code' => 'T0374', 'casual_leave' => 0.0, 'privilege_leave' => 25.0, 'sick_leave' => 2.5, 'RH AVAIL'],['code' => 'T0375', 'casual_leave' => 3.0, 'privilege_leave' => 20.0, 'sick_leave' => 6.0, 'RH AVAIL'],['code' => 'T0379', 'casual_leave' => 1.5, 'privilege_leave' => 30.0, 'sick_leave' => 9.5],['code' => 'T0380', 'casual_leave' => 4.0, 'privilege_leave' => 6.0, 'sick_leave' => 5.0],['code' => 'T0381', 'casual_leave' => 0.0, 'privilege_leave' => 2.0, 'sick_leave' => 0.0],['code' => 'T0383', 'casual_leave' => 0.0, 'privilege_leave' => 2.0, 'sick_leave' => 12.5],['code' => 'T0385', 'casual_leave' => 5.0, 'privilege_leave' => 27.0, 'sick_leave' => 23.0],['code' => 'T0386', 'casual_leave' => 0.0, 'privilege_leave' => 3.0, 'sick_leave' => 7.5],['code' => 'T0387', 'casual_leave' => 3.5, 'privilege_leave' => 11.0, 'sick_leave' => 5.0],['code' => 'T0388', 'casual_leave' => 0.0, 'privilege_leave' => 18.0, 'sick_leave' => 4.0],['code' => 'T0392', 'casual_leave' => 0.0, 'privilege_leave' => 4.0, 'sick_leave' => 6.0, 'RH AVAIL'],['code' => 'T0393', 'casual_leave' => 0.0, 'privilege_leave' => 8.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0396', 'casual_leave' => 0.0, 'privilege_leave' => 21.0, 'sick_leave' => 12.0, 'RH AVAIL'],['code' => 'T0401', 'casual_leave' => 0.0, 'privilege_leave' => 11.0, 'sick_leave' => 3.5, 'RH AVAIL'],['code' => 'T0402', 'casual_leave' => 2.0, 'privilege_leave' => 20.0, 'sick_leave' => 7.0, 'RH AVAIL'],['code' => 'T0403', 'casual_leave' => 4.0, 'privilege_leave' => 17.0, 'sick_leave' => 9.0],['code' => 'T0406', 'casual_leave' => 0.5, 'privilege_leave' => 7.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0408', 'casual_leave' => 0.0, 'privilege_leave' => 1.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0409', 'casual_leave' => 0.0, 'privilege_leave' => 4.0, 'sick_leave' => 2.5, 'RH AVAIL'],['code' => 'T0410', 'casual_leave' => 1.5, 'privilege_leave' => 0.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0411', 'casual_leave' => 0.0, 'privilege_leave' => 1.0, 'sick_leave' => 0.0],['code' => 'T0412', 'casual_leave' => 4.0, 'privilege_leave' => 1.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0413', 'casual_leave' => 0.0, 'privilege_leave' => 9.0, 'sick_leave' => 0.5],['code' => 'T0414', 'casual_leave' => 2.0, 'privilege_leave' => 15.0, 'sick_leave' => 5.0],['code' => 'T0417', 'casual_leave' => 2.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0418', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 3.0],['code' => 'T0419', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 1.0, 'RH AVAIL'],['code' => 'T0420', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0422', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0],['code' => 'T0423', 'casual_leave' => 0.0, 'privilege_leave' => 7.0, 'sick_leave' => 0.0],['code' => 'T0425', 'casual_leave' => 1.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0426', 'casual_leave' => 3.0, 'privilege_leave' => 12.0, 'sick_leave' => 0.0],['code' => 'T0427', 'casual_leave' => 3.0, 'privilege_leave' => 15.0, 'sick_leave' => 7.0],['code' => 'T0428', 'casual_leave' => 3.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0429', 'casual_leave' => 2.0, 'privilege_leave' => 12.0, 'sick_leave' => 0.0],['code' => 'T0432', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0433', 'casual_leave' => 2.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0434', 'casual_leave' => 0.0, 'privilege_leave' => 12.0, 'sick_leave' => 0.0],['code' => 'T0435', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0],['code' => 'T0436', 'casual_leave' => 2.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0],['code' => 'T0437', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0438', 'casual_leave' => 3.5, 'privilege_leave' => 15.0, 'sick_leave' => 1.0],['code' => 'T0439', 'casual_leave' => 0.0, 'privilege_leave' => 15.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0440', 'casual_leave' => 1.5, 'privilege_leave' => 15.0, 'sick_leave' => 0.0],['code' => 'T0441', 'casual_leave' => 0.5, 'privilege_leave' => 12.0, 'sick_leave' => 0.0],['code' => 'T0442', 'casual_leave' => 0.5, 'privilege_leave' => 15.0, 'sick_leave' => 0.0],['code' => 'T0443', 'casual_leave' => 0.5, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0444', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0445', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0447', 'casual_leave' => 1.5, 'privilege_leave' => 0.0, 'sick_leave' => 2.0],['code' => 'T0448', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0450', 'casual_leave' => 0.5, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0451', 'casual_leave' => 3.0, 'privilege_leave' => 0.0, 'sick_leave' => 1.0],['code' => 'T0452', 'casual_leave' => 3.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0455', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0456', 'casual_leave' => 4.0, 'privilege_leave' => 0.0, 'sick_leave' => 4.0],['code' => 'T0457', 'casual_leave' => 3.5, 'privilege_leave' => 0.0, 'sick_leave' => 2.0],['code' => 'T0458', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0459', 'casual_leave' => 1.5, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0460', 'casual_leave' => 2.5, 'privilege_leave' => 0.0, 'sick_leave' => 0.0, 'RH AVAIL'],['code' => 'T0461', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0463', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0464', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0465', 'casual_leave' => 0.5, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0466', 'casual_leave' => 6.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0467', 'casual_leave' => 4.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0468', 'casual_leave' => 4.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0470', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0471', 'casual_leave' => 2.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0472', 'casual_leave' => 4.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0473', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0475', 'casual_leave' => 1.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0476', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0477', 'casual_leave' => 0.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0478', 'casual_leave' => 1.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],['code' => 'T0479', 'casual_leave' => 1.0, 'privilege_leave' => 0.0, 'sick_leave' => 0.0],];
								foreach ($Leaves as $key => $record) {
									$Employee = Employee::where('emp_code', $record['code'])->first();
									if(!$Employee) {
										dd('Not found ' . $record['code']);
									} else if($Employee->emp_code!=$Employee->finger_id) {
										dd('Finger ID and Code missmatch (emp_code='.$Employee->emp_code.', finger_id='.$Employee->finger_id.')');
									} else if(!$Employee->EmployeeLeaves) {
										\App\Components\Common\addEmployeeLeaves($Employee->employee_id);
									}
									
									$Employee = Employee::where('emp_code', $record['code'])->first();
									if(!$Employee->EmployeeLeaves) {
										dd('Employee record not found: ' . $record['code']);
									}
								}
								foreach ($Leaves as $key => $record) {
									$Employee = Employee::where('emp_code', $record['code'])->first();
									
									$EmployeeLeaves = $Employee->EmployeeLeaves;
									if(!$EmployeeLeaves) {
										$EmployeeLeaves = new EmployeeLeaves;
										$EmployeeLeaves->employee_id = $Employee->employee_id;
										$EmployeeLeaves->branch_id = $Employee->branch_id;
										$EmployeeLeaves->save();
									}
									if($EmployeeLeaves) {
										$EmployeeLeaves->casual_leave = $record['casual_leave'];
										$EmployeeLeaves->privilege_leave = $record['privilege_leave'];
										$EmployeeLeaves->sick_leave = $record['sick_leave'];
										if($Employee->gender=='Male') {
											$paternity = $Employee->paternity_leave_ploicy();
											if($paternity && isset($paternity['status']) && $paternity['status'] && isset($paternity['paternity_leave'])) {
												$EmployeeLeaves->paternity_leave = $paternity['paternity_leave'];
											}
										} else if($Employee->gender=='Female') {
											$maternity = $Employee->maternity_leave_ploicy();
											if($maternity && isset($maternity['status']) && $maternity['status'] && isset($maternity['maternity_leave'])) {
												$EmployeeLeaves->maternity_leave = $maternity['maternity_leave'];
											}
										}
										$EmployeeLeaves->rh_leave = array_search('RH AVAIL', $record) !==false ? 0 : 1;
										$EmployeeLeaves->OD = 1;
										$EmployeeLeaves->comp_off = 0;
										$EmployeeLeaves->update();
										echo '<pre>Emp Code: '.$record['code'].'<br>'.print_r($EmployeeLeaves->getAttributes(), 1).'</pre>';
									}
								}
							}
							@endphp
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
