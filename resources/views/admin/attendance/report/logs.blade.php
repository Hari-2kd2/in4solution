@extends('admin.master')
@section('content')
@section('title')
	@lang('attendance.logs_report')
@endsection
<div class="container-fluid">
	<div class="row  ">
		<div class="col-lg-6 col-md-4 col-sm-4 col-xs-12">
			<ol class="breadcrumb">
				<li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
						@lang('dashboard.dashboard')</a></li>
				<li>@yield('title')</li>
			</ol>
			<div class="sync-status">
						@if(session()->has('success'))
						<div class="alert alert-success alert-dismissable">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
							<i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success')
								}}</strong>
						</div>
						@endif
						@if(session()->has('error'))
						<div class="alert alert-danger alert-dismissable">
							<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
							<i class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error')
								}}</strong>
						</div>
						@endif
						</div>
		</div>
			
	</div>

	@php
		$employees = \App\Model\Employee::where('branch_id', session('selected_branchId'))->orderBy('finger_id')->get();
			$s=1;
			$datesync = date('Y-m-d'); 
			if(isset($_GET['date'])){
				$datesync=$_GET['date'];
			}
		@endphp
		
		
		{{ Form::open([
             'route' => 'access.manualsynchronization',
             'id' => 'manualSynclist',
             'class' => 'form-horizontal',
			 'method'=>'GET'
            ]) }}
			<div class="row">
			<div class="col-md-1">
				 <label style="margin-top:9px;float:right;">Date</label>
			</div>
			<div class="col-md-3" style="float:left;">
				<div class="form-group">
					
					<input type="date" class="form-control " style="height: 35px;"
							required   placeholder="@lang('common.date')" id="date" value="{{isset($_GET['date']) ? date('Y-m-d',strtotime($_GET['date'])) : date('Y-m-d')}}"
							name="date" >
				</div>
			</div>		
			<div class="col-md-3">			
						<input type="submit" id="filter" style="width: 150px;"
							class="btn btn-info " value="@lang('common.filter')">			
							
			</div>
			<div class="col-md-4">
			@php
				$last_gen_data=App\Model\MsSql::where('branch_id', session('selected_branchId'))->orderBy('ID','DESC')->first();
			
				$last_syn_data='';
				if($last_gen_data){
					$last_syn_data = date('d-m-Y h:i A',strtotime($last_gen_data->datetime) ); 
				}
		    @endphp
				<table class="table">			
					<tr>
						<td>Last Log At</td>
						<td>{{$last_syn_data}}</td>
					</tr>
				</table>
			</div>			
			
			</div>
		{{ Form::close() }}
	
	 

	
	<div class="row">
		
		<div class="col-sm-12"> 
		
		<table id="myDataTable" class="table table-bordered ">			
        	<thead>
            <tr class="tr_header">
			    <th style="width:5%">SNo</th>     
				<th style="width:14%">Finger ID</th>
                <th style="width:35%">Employee Name</th>
				<th>Punches</th>               
            </tr>
        	</thead>
        <tbody>   
		<?php
			foreach ($employees as $emp){ 
				$ms_sqls = \App\Model\MsSql::where('branch_id', session('selected_branchId'))->where('employee',$emp->employee_id)->whereDate('datetime',$datesync)->get();
		?>
                <tr>
                    <td>{!!  $s++ !!}</td>                    
                    <td>{!! $emp->finger_id  !!} </td>  
                    <td>{!! $emp->first_name.' '.$emp->last_name !!}</td>
					<td> 
						<?php
							 $time_set=[];
							 if($ms_sqls){
								foreach($ms_sqls as $time){
									echo DATE('h:i A',strtotime($time->datetime)).' ('.$time->type.') ';
								}
								echo implode(", ",$time_set);
							}
							$time_set = '';
							
						?>
					</td>                  
                </tr>
			
            <?php } ?>
        </tbody>
    </table>
		</div>
	</div>
</div>

@endsection

</script>
@section('page_scripts')
<script type="text/javascript">
    $(function() {
        // $('#myDataTable').DataTable({
		// 	"lengthMenu": [50,10,25,100,200]  
        // });
    });
</script>
@endsection('page_scripts')
 
 

 