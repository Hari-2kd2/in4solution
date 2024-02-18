@extends('admin.master')
@section('content')
@section('title')
@lang('leave.restricted_holiday')
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
				<a href="{{ route('applyForRestrictedHoliday.create') }}"  class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i class="fa fa-plus-circle" aria-hidden="true"></i> @lang('leave.apply_for_restricted_holiday')</a>
			</div>	
		</div>
					
		<div class="row">
			<div class="col-sm-12">
				<div class="panel panel-info">
					<div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
					<div class="panel-wrapper collapse in" aria-expanded="true">
						<div class="panel-body">
							@php
								// $template,$to,$subject,$data
								// \App\Components\Common::mail('test', ['rgopi.com@gmail.com'], ['Subject '.date('d/m/Y H:i:s')],'message '.date('d/m/Y H:i:s'));
							@endphp
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
							<div class="">
								<table class="table table-hover manage-u-table">
									<thead >
                                         <tr>
                                            <th>#</th>
                                            <th>@lang('leave.application_date')</th>
                                            <th>@lang('leave.holiday_date')</th>
                                            <th>@lang('leave.holiday_name')</th>
                                            <th>@lang('leave.purpose')</th>
                                            <th>@lang('common.status')</th>
                                            <th>@lang('leave.response_date')</th>
                                            <th>@lang('leave.response_by')</th>
                                        </tr>
									</thead>
									<tbody>
										{!! $sl=null !!}
										@foreach($RhApplicationList AS $RhApplication)
											<tr>
												<td style="width: 100px;">{!! ++$sl !!}</td>
												<td>{!! dateConvertDBtoForm($RhApplication->application_date) !!}</td>
												<td>{!! dateConvertDBtoForm($RhApplication->holiday_date) !!}</td>
												<td>{!! $RhApplication->RestrictedHoliday->holiday_name !!}</td>
												<td>{!! $RhApplication->purpose !!}</td>
												@if($RhApplication->status == 1)
													<td  style="width: 100px;"><span class="label label-warning">@lang('common.pending')</span></td>
												@elseif($RhApplication->status == 2)
													<td  style="width: 100px;"><span class="label label-success">@lang('common.approved')</span></td>
												@elseif($RhApplication->status == 3)
													<td  style="width: 100px;"><span class="label label-danger">@lang('common.rejected')</span></td>
												@elseif($RhApplication->status == 4)
													<td  style="width: 100px;"><span class="label label-warning">@lang('common.canceled')</span></td>
												@else
													<td  style="width: 100px;">-</td>
												@endif
												<td>{!! $RhApplication->status > 1 ? dateTimeConvertDBtoForm($RhApplication->updated_at) : '-' !!}</td>
												<td>{!! $RhApplication->reviewedBy() !!}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
								<div class="text-center">
									{{$RhApplicationList->links()}}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
