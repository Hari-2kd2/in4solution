@extends('admin.master')
@section('content')
@section('title')
@lang('attendance.attendance_summary_report')
@endsection
<style type="text/css">
	.form-check label{font-weight: normal;}
	.inner-section .panel-heading {background-color: transparent !important;padding: 8px 13px;}
	.inner-section .panel-body {height: 250px;overflow: auto;padding:10px;}
</style>
<div class="container-fluid">
	<br>
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-info">
				<div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>Generate Report</div>
				<div class="panel-wrapper collapse in" aria-expanded="true">
					<div class="panel-body inner-section">
						{{ Form::open(array('route' => 'search.result','enctype'=>'multipart/form-data','class'=>'form-horizontal','id'=>'awardForm','method'=>'GET')) }}
						<div class="row">
							<div class="col-md-6">
								<div class="panel panel-info" style="border:0.1px solid lightgrey;">
									<div class="panel-heading">Employee</div>
									<div class="panel-wrapper collapse in" aria-expanded="true">
										<div class="panel-body">
											@php $emp=\App\Model\Employee::where('status',1)->orderBy('first_name','ASC')->get(); @endphp
											@foreach($emp as $Data)
											<div class="form-check">
												<input type="checkbox" class="form-check-input" name="employee[]" id="employee{{$Data->employee_id}}" value="{{$Data->employee_id}}">
												<label class="form-check-label" for="employee{{$Data->employee_id}}">{{$Data->detailname()}}</label>
											 </div>
											 @endforeach
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="panel panel-info" style="border:0.1px solid lightgrey;">
									<div class="panel-heading">Financial Years</div>
									<div class="panel-wrapper collapse in" aria-expanded="true">
										<div class="panel-body">
											 <div class="form-group">
												 <select class="form-control" id="financialyear">
													 @php
														$yearList = \App\Model\calanderYear::yearList();
														@endphp
											      <option value="">- Select Year -</option>
											      @foreach ($yearList as $year_id => $year_name)
												  <option value="{{ $year_id }}">{{$year_name}}</option>
												  @endforeach
											    </select>
											  </div>
											<div class="month-list">
												
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="panel panel-info" style="border:0.1px solid lightgrey;">
									<div class="panel-heading">Payroll</div>
									<div class="panel-wrapper collapse in" aria-expanded="true">
										<div class="panel-body">
											@php
											 $payroll=keyFields('payroll');
											@endphp

											@foreach($payroll as $key=>$payrollData)
												<div class="form-check">
													<input type="checkbox" name="filterset[]" class="form-check-input" id="{{$key}}" value="{{$key}}">
													<label class="form-check-label" for="{{$key}}">{{$payrollData}}</label>
												</div>
											@endforeach
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="panel panel-info" style="border:0.1px solid lightgrey;">
									<div class="panel-heading">Classification Details</div>
									<div class="panel-wrapper collapse in" aria-expanded="true">
										<div class="panel-body">
											@php
											 $classification=['role'=>'Role','acno'=>'Bank A/c No','ifsc'=>'Bank IFSC','bankname'=>'Bank Name','bonus'=>'Bonus','branch'=>'Branch','department'=>'Department','designation'=>'Designation','workshif'=>'Work Shift','hod'=>'HOD','ctc'=>'CTC'];
											 $classification=keyFields('classifications');
											@endphp
											@foreach($classification as $key=>$classData)
												<div class="form-check">
													<input type="checkbox" name="filterset[]" class="form-check-input" id="{{$key}}" value="{{$key}}">
													<label class="form-check-label" for="{{$key}}">{{$classData}}</label>
												</div>
											@endforeach
											
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="panel panel-info" style="border:0.1px solid lightgrey;">
									<div class="panel-heading">Additional Information</div>
									<div class="panel-wrapper collapse in" aria-expanded="true">
										<div class="panel-body">
											@php
											 $additional=['username'=>'Username','fingerid' => 'Finger Print ID', 'empcode'=>'Emp Code','email'=>'Email ID','mobie'=>'Mobile No','gender'=>'Gender','uan'=>'UAN','costcenter'=>'Cost Center','pan'=>'PAN/GIR No','pfno'=>'PF Account No','esi'=>'ESI No','religion'=>'Religion','dob'=>'Date of Birth','doj'=>'Date of Joining','salrev'=>'Salary Revision','dol'=>'Date of Leaving','marital'=>'Martial Status','childs'=>'No of Childs','status'=>'Status','ota'=>'Overtime Allowed','epf'=>'EPF Status','emgencycno'=>'Emergency Contact No','address'=>'Address','photo'=>'Photo'];
											 $additional=keyFields('additionals');
											@endphp
											@foreach($additional as $key=>$addData)
												<div class="form-check">
													<input type="checkbox" name="filterset[]" class="form-check-input" id="{{$key}}" value="{{$key}}">
													<label class="form-check-label" for="{{$key}}">{{$addData}}</label>
												</div>
											@endforeach
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="panel panel-info" style="border:0.1px solid lightgrey;">
									<div class="panel-heading">Leave Information</div>
									<div class="panel-wrapper collapse in" aria-expanded="true">
										<div class="panel-body">
											@php
											 $information=["cw"=>"CW","ood"=>"OOD","cl"=>"CL","sl"=>"SL","pl"=>"PL","l"=>"L","weekoff"=>"Weekly Off","generalholiday"=>"General Holiday"];
											 $information=keyFields('attendance');
											@endphp
											@foreach($information as $key=>$infoData)
												<div class="form-check">
													<input type="checkbox" name="filterset[]" class="form-check-input" id="{{$key}}" value="{{$key}}">
													<label class="form-check-label" for="{{$key}}">{{$infoData}}</label>
												</div>
											@endforeach
										</div>
									</div>
								</div>
							</div>
							<div class="form-actions">
								<div class="row">
									<div class="col-md-8">
										<div class="row">
											<div class="col-md-offset-4 col-md-8">
												@if(isset($editModeData))
													<button type="submit" class="btn btn-info btn_style"><i class="fa fa-pencil"></i> @lang('common.update')</button>
												@else
													<button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i> @lang('common.save')</button>
												@endif
											</div>
										</div>
									</div>
								</div>
							</div>
							{{ Form::close() }}
						</div>
					<br>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('page_scripts')
<script type="text/javascript">
	$(document).ready(function(){

		$('#financialyear').change(function(){
			var year=$(this).val();
			$.ajax({
					  url: "{{url('monthlist')}}", 
					  data: {
							year : year
						},
					  success: function(data, textStatus, jqXHR)
					  {
						  $ ('.month-list').html(data); 
					  },
					  error: function(jqXHR, textStatus, errorThrown)
					  {
						  console.log("Error"); 
					  }
				}); 



		});

	});
</script>
@endsection