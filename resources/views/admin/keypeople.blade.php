@extends('admin.master')
@section('content')

@section('title')
@lang('menu.email_settings')
@endsection


	<div class="container-fluid">
		<div class="row bg-title">
			<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
				<ol class="breadcrumb">
					<li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
					<li>@yield('title')</li>
				  
				</ol>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-info">
					<div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@yield('title')</div>
					<p>&nbsp;</p>
					<div class="panel-heading">@lang('common.key_people_setting')</div>
					<div class="panel-wrapper collapse in" aria-expanded="true">
						<div class="panel-body">
							<p class="alert-danger">&nbsp;A specific key people taken leaves are notify to directors email.</p>
							@if($Keypeople->key_id)
							{{ Form::model($Keypeople, array('url' => Route('emailSettings.update', $Keypeople->key_id), 'method' => 'POST', 'id' => 'keyPeopleForm','class' => 'form-horizontal')) }}
							@else
							{{ Form::open(array('url' => Route('emailSettings.store'), 'method' => 'POST', 'id' => 'keyPeopleForm', 'class' => 'form-horizontal')) }}
							@endif
							
							<div class="form-body">
								<div class="row">
									<div class="col-md-offset-2 col-md-6">
										@if(request()->post('directors') && $errors->any())
										<div class="alert alert-danger alert-dismissible" role="alert">
											<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
											@foreach($errors->all() as $error)
											<strong>{!! $error !!}</strong><br>
											@endforeach
												</div>
												@endif
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
											</div>
										</div>
										<div class="row">
											<div class="col-md-8">
												<div class="form-group">
													<label class="control-label col-md-4">@lang('common.key_user_ids')<span class="validateRq">*</span></label>
													<div class="col-md-8">
														@php
														$preLoadKeypeople=[];
														if($Keypeople->key_user_ids) {
															$Keypeople_key_user_ids = explode(',', $Keypeople->key_user_ids);
															$Keypeople->key_user_ids = explode(',', $Keypeople->key_user_ids);
															foreach ($Keypeople_key_user_ids as $key => $id) {
																$Employee = \App\Model\Employee::find($id);
																$preLoadKeypeople[$Employee->employee_id] = $Employee->finger_id.' ('.$Employee->email.')';
															}
														}
														// dd($preLoadKeypeople);
													@endphp
													{!! Form::select(
														'key_user_ids[]',
														$preLoadKeypeople,
														Input::old('key_user_ids'),
														$attributes = [
															'class' => 'form-control key_user_ids required',
															'data-placeholder' => __('Key People'),
															'id' => 'select2-keypeople',
															'multiple' =>true,
															]
															) !!}
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-8">
											<div class="form-group">
												<label class="control-label col-md-4">@lang('common.key_director_emails')<span class="validateRq">*</span></label>
												<div class="col-md-8">
													{!! Form::textarea(
														'key_director_emails',
                                                        $Keypeople->key_director_emails ? $Keypeople->key_director_emails : Input::old('key_director_emails'),
                                                        $attributes = [
															'class' => 'form-control required key_director_emails',
                                                            'id' => 'key_director_emails',
                                                            'placeholder' => __('Director Emails'),
                                                            'readonly' => false,
															'rows' => '5',
                                                        ],
														) !!}
												</div>
											</div>
										</div>
									</div>
									
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-8">
											<div class="row">
												<div class="col-md-offset-4 col-md-8">
													@if($Keypeople->key_id)
													<button type="submit" name="directors" class="btn btn-info btn_style"><i class="fa fa-pencil"></i> @lang('common.update')</button>
													{{-- <a href="{{ Route('emailSettings.delete', ['id' => $Keypeople->key_id]) }}" class="btn btn-danger btn_style pull-right delete-keypeople"><i class="fa fa-trash"></i> @lang('common.delete')</a> --}}
													@else
													<button type="submit" name="directors" class="btn btn-info btn_style"><i class="fa fa-check"></i> @lang('common.save')</button>
													@endif
												</div>
											</div>
										</div>
									</div>
								</div>
								{{ Form::close() }}
							</div>
						</div>
						
						<p>&nbsp;</p>
						<div class="panel-heading">@lang('common.hr_people_setting')</div>
						<div class="panel-wrapper collapse in" aria-expanded="true">
							<div class="panel-body">
								<p class="alert-danger">&nbsp;All employee taken leaves are notify to HR email.</p>
							@if($HrPeople->key_id)
							{{ Form::model($HrPeople, array('url' => Route('emailSettings.hrUpdate', $HrPeople->key_id), 'method' => 'POST', 'id' => 'keyPeopleForm2','class' => 'form-horizontal')) }}
							@else
							{{ Form::open(array('url' => Route('emailSettings.hrStore'), 'method' => 'POST', 'id' => 'keyPeopleForm2', 'class' => 'form-horizontal')) }}
							@endif
							
							<div class="form-body">
								<div class="row">
									<div class="col-md-offset-2 col-md-6">
										@if($errors->any())
										<div class="alert alert-danger alert-dismissible" role="alert">
											<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
											@foreach($errors->all() as $error)
											<strong>{!! $error !!}</strong><br>
											@endforeach
												</div>
											@endif
											@if(session()->has('hrSuccess'))
												<div class="alert alert-success alert-dismissable">
													<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
													<i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('hrSuccess') }}</strong>
												</div>
											@endif
											@if(session()->has('hrError'))
												<div class="alert alert-danger alert-dismissable">
													<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
													<i class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('hrError') }}</strong>
												</div>
											@endif
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-8">
											<div class="form-group">
												<label class="control-label col-md-4">@lang('common.key_hr_emails')<span class="validateRq">*</span></label>
												<div class="col-md-8">
													{!! Form::textarea(
                                                        'key_hr_emails',
                                                        $HrPeople->key_hr_emails ? $HrPeople->key_hr_emails : Input::old('key_hr_emails'),
                                                        $attributes = [
                                                            'class' => 'form-control required key_hr_emails',
                                                            'id' => 'key_hr_emails',
                                                            'placeholder' => __('HR Emails'),
                                                            'readonly' => false,
															'rows' => '5',


                                                        ],
                                                    ) !!}
												</div>
											</div>
										</div>
									</div>
									
								</div>
								<div class="form-actions">
									<div class="row">
										<div class="col-md-8">
											<div class="row">
												<div class="col-md-offset-4 col-md-8">
													@if($HrPeople->key_id)
														<button type="submit" name="hrs" class="btn btn-info btn_style"><i class="fa fa-pencil"></i> @lang('common.update')</button>
														{{-- <a href="{{ Route('emailSettings.hrDelete', ['id' => $HrPeople->key_id]) }}" class="btn btn-danger btn_style pull-right delete-keypeople"><i class="fa fa-trash"></i> @lang('common.delete')</a> --}}
													@else
														<button type="submit" name="hrs" class="btn btn-info btn_style"><i class="fa fa-check"></i> @lang('common.save')</button>
													@endif
												</div>
											</div>
										</div>
									</div>
								</div>
							{{ Form::close() }}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('page_scripts')
    <script>
        $(function() {
			$("#select2-keypeople").select2({
				ajax: {
					url: "{{route('emailSettings.keypeopleSearch')}}",
					dataType: 'json',
					delay: 250,
					data: function (params) {
						return {
							q: params.term, // search term
						};
					},
					processResults: function (response) {
						return {
							results: response
						};
					},
					cache: true
				}
			});

			$('.delete-keypeople').click(function (e) { 
				e.preventDefault();
				return confirm('Are sure want delete?');
			});
        });
    </script>
@endsection('page_scripts')