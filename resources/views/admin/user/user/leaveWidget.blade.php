@if ($EmployeeLeaves = $employeeInfo->EmployeeLeaves)
    @php
        $paternity_leave_ploicy = $employeeInfo->paternity_leave_ploicy();
        $maternity_leave_ploicy = $employeeInfo->maternity_leave_ploicy();
    @endphp
    <div class="leave_information">
        <section class="content">
            <div class="row">
                <div class="panel-custom">
                    <h3 class="panel-title"><i class="fa fa-laptop"></i> @lang('leave.leave_information')</h3>
                </div>
                <div class="box">
                    <div class="box-body">
                        <div class="personal_info">
                            <div class="item">
                                <div class="col-xs-2 col-sm-2 col-md-3">@lang('leave.casual_leave')</div>
                                <div class="col-xs-10 col-sm-10 col-md-9">
                                    :&nbsp;&nbsp;&nbsp;&nbsp;{{ $EmployeeLeaves->casual_leave }} Days</div>
                            </div>
                            <div class="item">
                                <div class="col-xs-2 col-sm-2 col-md-3">@lang('leave.sick_leave')</div>
                                <div class="col-xs-10 col-sm-10 col-md-9">
                                    :&nbsp;&nbsp;&nbsp;&nbsp;{{ $EmployeeLeaves->sick_leave }} Days</div>
                            </div>
                            {{-- <div class="item">
                                <div class="col-xs-2 col-sm-2 col-md-3">@lang('leave.privilege_leave')</div>
                                <div class="col-xs-10 col-sm-10 col-md-9">
                                    :&nbsp;&nbsp;&nbsp;&nbsp;{{ $EmployeeLeaves->privilege_leave }} Days</div>
                            </div>
                            <div class="item">
                                <div class="col-xs-2 col-sm-2 col-md-3">@lang('leave.comp_off')</div>
                                <div class="col-xs-10 col-sm-10 col-md-9">
                                    :&nbsp;&nbsp;&nbsp;&nbsp;{{ $EmployeeLeaves->comp_off }} Days</div>
                            </div>
                            @if ($paternity_leave_ploicy && isset($paternity_leave_ploicy['status']) && $paternity_leave_ploicy['status'])
                                <div class="item">
                                    <div class="col-xs-2 col-sm-2 col-md-3">@lang('leave.paternity_leave')</div>
                                    <div class="col-xs-10 col-sm-10 col-md-9">
                                        :&nbsp;&nbsp;&nbsp;&nbsp;{{ $paternity_leave_ploicy['paternity_leave'] }} Days</div>
                                </div>
                            @elseif($maternity_leave_ploicy && isset($maternity_leave_ploicy['status']) && $maternity_leave_ploicy['status'])
                                <div class="item">
                                    <div class="col-xs-2 col-sm-2 col-md-3">@lang('leave.maternity_leave')</div>
                                    <div class="col-xs-10 col-sm-10 col-md-9">
                                        :&nbsp;&nbsp;&nbsp;&nbsp;{{ (int) ($maternity_leave_ploicy['maternity_leave'] / 30) }} Months</div>
                                </div>
                            @endif --}}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endif
