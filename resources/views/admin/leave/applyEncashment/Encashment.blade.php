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
                                            {{-- <th>@lang('leave.leave_type')</th> --}}
                                            <th>@lang('leave.enc_open')</th>
                                            <th>@lang('leave.enc_days')</th>
                                            <th>@lang('leave.enc_amount')</th>
                                            {{-- <th>@lang('leave.enc_close')</th> --}}
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
												<td style="width: 100px;">{!! ++$sl !!}</td>
												<td>{{$LeaveEncashmentEach->calanderYear->year_name??''}}</td>
												{{-- <td>{{$LeaveEncashmentEach->LeaveType->leave_type_name??''}}</td> --}}
												<td>{{$LeaveEncashmentEach->enc_open}}</td>
												<td>{{$LeaveEncashmentEach->enc_days}}</td>
												{{-- <td>{{$LeaveEncashmentEach->enc_close}}</td> --}}
												<td>{{$LeaveEncashmentEach->enc_amount}}</td>
												<td>{{$LeaveEncashmentEach->enc_submit_on_display}}</td>
												<td>{{$LeaveEncashmentEach->enc_action_on_display}}</td>
												<td>{{$LeaveEncashmentEach->enc_action_by_display}}</td>
												<td>{{$LeaveEncashmentEach->enc_status_display}}</td>
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