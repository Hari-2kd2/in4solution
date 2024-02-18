@extends('admin.master')
@section('content')
@section('title')

@lang('restrictedHoliday.holiday_list')

@endsection
	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
			   <ol class="breadcrumb">
					<li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
					<li>@yield('title')</li>
				</ol>
			</div>	
			<div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
				<a href="{{ route('restrictedHoliday.create') }}"  class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i class="fa fa-plus-circle" aria-hidden="true"></i> @lang('restrictedHoliday.add_holiday')</a>
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
								<table id="myDataTable" class="table table-bordered">
									<thead>
										 <tr class="tr_header">
											<th>@lang('common.serial')</th>
											<th>@lang('common.name')</th>
											<th>@lang('common.date')</th>
											<th style="text-align: center;">@lang('common.action')</th>
										</tr>
									</thead>
									<tbody>
										{!! $sl=null; $today = date('Y-m-d') !!}
										@foreach($results AS $value)
											<tr class="{!! $value->holiday_id !!}">
												<td style="width: 50px;">{!! ++$sl !!}</td>
												<td>{!! $value->holiday_name !!}</td>
												<td>{!! dateConvertDBtoForm($value->holiday_date) !!}</td>
												
												<td style="width: 100px;">
													@if ($today<=$value->holiday_date)
														<a href="{!! route('restrictedHoliday.edit',$value->holiday_id ) !!}"  class="btn btn-success btn-xs btnColor">
															<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
														</a>
														<a href="{!!route('restrictedHoliday.delete',$value->holiday_id  )!!}" data-token="{!! csrf_token() !!}" data-id="{!! $value->holiday_id !!}" class="delete btn btn-danger btn-xs deleteBtn btnColor"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
													@else
														<span class="text-red">@lang('common.past_holiday')</span>
													@endif
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
