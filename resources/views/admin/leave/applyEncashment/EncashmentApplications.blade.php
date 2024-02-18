@php
// $role_id = session('logged_session_data.role_id');
@endphp
@extends('admin.master')
@section('content')
@section('title')
@lang('leave.my_encashment_list')
@endsection
	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
			   <ol class="breadcrumb">
					<li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
					<li>@yield('title')</li>
				</ol>
			</div>	
			<div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
				<a href="{{ route('leave.EncashmentApply') }}"  class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i class="fa fa-plus-circle" aria-hidden="true"></i> @lang('leave.EncashmentApply')</a>
			</div>	
		</div>
					
		<div class="row">
			<div class="col-sm-12">
				<div class="panel panel-info">
					<div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
					<div class="panel-wrapper collapse in" aria-expanded="true">
						<div class="panel-body">
							@include('flash_message')
							<div class="table-responsive">
								<table class="table table-hover manage-u-table">
									<thead >
                                         <tr>
                                            <th>#</th>
                                            <th>@lang('leave.year_id')</th>
                                            <th>@lang('employee.name')</th>
                                            <th>@lang('employee.emp_code')</th>
                                            <th>@lang('leave.enc_open')</th>
                                            <th>@lang('leave.enc_days')</th>
                                            <th>@lang('leave.enc_amount')</th>
                                            <th>@lang('leave.enc_salary_date')</th>
                                            <th>@lang('leave.enc_submit_on')</th>
                                            <th>@lang('leave.enc_action_on')</th>
                                            <th>@lang('leave.enc_action_by')</th>
                                            <th>@lang('common.status')</th>
                                        </tr>
									</thead>
									<tbody>
										{!! $sl=null !!}
										@foreach($LeaveEncashmentList AS $LeaveEncashmentEach)
											@php
											@endphp
											<tr>
												<td style="width:50px;">{!! ++$sl !!}</td>
												<td style="width:50px;">{{$LeaveEncashmentEach->calanderYear->year_name??''}}</td>
												<td>{{$LeaveEncashmentEach->employee_name}}</td>
												<td>{{$LeaveEncashmentEach->emp_code}}</td>
												<td>{{$LeaveEncashmentEach->enc_open}}</td>
												<td>{{$LeaveEncashmentEach->enc_days}}</td>
												<td>{{$LeaveEncashmentEach->enc_amount}}</td>
												<td>{{$LeaveEncashmentEach->enc_salary_date_display}}</td>
												<td>{{$LeaveEncashmentEach->enc_submit_on_display}}</td>
												<td>{{$LeaveEncashmentEach->enc_action_on_display}}</td>
												<td>{{$LeaveEncashmentEach->enc_action_by_display}}</td>
												<td>
                                                    {{$LeaveEncashmentEach->enc_status_display}}
                                                    @if ($LeaveEncashmentEach->enc_status == $LeaveEncashmentEach::PENDING)
                                                        <br><a href="{!! route('leave.EncashmentDetail', $LeaveEncashmentEach->enc_entry_id) !!}" title="View leave details!"
                                                            class="btn btn-info btn-md btnColor">
                                                            <i class="fa fa-arrow-circle-right"></i>
                                                        </a>
                                                    @else
                                                        <br><i class="btn btn-success btn-sm fa fa-check"></i>
                                                    @endif
                                                </td>
											</tr>
										@endforeach
									</tbody>
								</table>
								{{-- <div class="text-center">
									{{$results->links()}}
								</div> --}}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('page_scripts')
<script>
	$(document).ready(function () {

	}); // end $(document).ready
</script>
@endsection