<div class="table-responsive">

    <table id="" class="table table-bordered table-hover manage-u-table">

        <thead class="tr_header">
            <tr>
                <th>@lang('common.serial')</th>
                <th>@lang('employee.photo')</th>
                <th>@lang('employee.employee_name')</th>
                <th>@lang('employee.department')</th>
                <th>@lang('employee.phone')</th>
                <th>@lang('employee.date_of_joining')</th>
                <th>@lang('common.status')</th>
                <th>@lang('common.action')</th>
            </tr>
        </thead>

        <tbody>
            @php
                $s = 1;
            @endphp
            @foreach ($results as $key => $value)
                <tr class="{!! $value->employee_id !!}">
                    <td style="width: 100px;">{!! $s++ !!}</td>
                    <td>
                        @if ($value->photo != '' && file_exists('uploads/employeePhoto/' . $value->photo))
                            <a href="{!! route('employee.show', $value->employee_id) !!}"><img style=" width: 60px; height:60px"
                                    src="{!! asset('uploads/employeePhoto/' . $value->photo) !!}" alt="user-img" class="img-circle"></a>
                        @else
                            <a href="{!! route('employee.show', $value->employee_id) !!}"> <img style=" width: 60px; height:60px"
                                    src="{!! asset('admin_assets/img/default.png') !!}" alt="user-img" class="img-circle"></a>
                        @endif

                    </td>

                    <td>
                        <span class="font-medium">
                            <a href="{!! route('employee.show', $value->employee_id) !!}">{!! $value->displayNameWithCode() !!}</a>
                        </span>
                        <br />
                        <span class="text-muted">@lang('employee.role') :
                            @if (isset($value->userName->role->role_name))
                                {!! $value->userName->role->role_name !!}
                            @endif
                        </span>
                        <br />
                        <span class="text-muted">
                            @if ($value->supervisor_id)
                                @php
                                    $supervisorDetail = $value->supervisorDetail();
                                    $supervisorTitle = $value->supervisorTitle;
                                @endphp
                                @lang('employee.supervisor') : <span title="{{ $supervisorTitle }}">{!! $supervisorDetail !!}</span>
                            @endif
                        </span>
                    </td>

                    <td>
                        <span class="font-medium">
                            {!! $value->department_disp() !!}
                        </span>
                        <br />
                        <span class="text-muted">@lang('employee.designation') :
                            {!! $value->designation_disp() !!}
                        </span>
                        <br />
                        <span class="text-muted">
                            @if (isset($value->branch->branch_name))
                                @lang('branch.branch_name') : {!! $value->branch->branch_name !!}
                            @endif
                        </span>
                    </td>

                    <td>
                        <span class="font-medium">
                            {{ $value->phone }}
                        </span>
                        <br />
                        <span class="text-muted">
                            @if ($value->email != '')
                                @lang('employee.email') :{!! $value->email !!}
                            @endif
                        </span>
                    </td>

                    <td>
                        <span class="font-medium">
                            {{ dateConvertDBtoForm($value->date_of_joining) }}
                        </span>

                        <br /><span class="text-muted">
                            {{ \Carbon\Carbon::parse($value->date_of_joining)->diffForHumans() }}
                        </span>

                        <br />
                        <span class="text-muted">
                            @lang('employee.job_status'):
                            @if ($value->permanent_status == 0)
                                @lang('employee_permanent.probation_period')
                            @elseif($value->permanent_status == 1)
                                @lang('employee_permanent.confirmed')
                            @else
                                @lang('employee_permanent.resigned')
                            @endif
                        </span>
                    </td>

                    <td class="text-left">

                        @if ($value->date_of_leaving == date('Y-m-d'))
                            <span class="label label-danger">@lang('employee_permanent.resigned')</span>
                            <br>
                            <p></p>
                            @if ($value->status == 1)
                                <span class="label label-success">@lang('common.active')</span>
                            @else
                                <span class="label label-warning">@lang('common.inactive')</span>
                            @endif
                        @else
                            {{-- $value->date_of_joining >= date('Y-m-d', strtotime('-6 months')) &&  --}}
                            @if ($value->permanent_status == 0)
                                <span class="label label-warning">@lang('employee_permanent.probation_period')</span>
                            @elseif($value->permanent_status == 1)
                                <span class="label label-success">@lang('employee_permanent.confirmed')</span>
                            @else
                                <span class="label label-danger">@lang('employee_permanent.resigned')</span>
                            @endif
                            <br>
                            <p></p>
                            @if ($value->status == 1)
                                <span class="label label-success">@lang('common.active')</span>
                            @else
                                <span class="label label-warning">@lang('common.inactive')</span>
                            @endif
                        @endif


                    </td>


                    <td style="width: 150px">
                        <a title="Profile" href="{!! route('employee.show', $value->employee_id) !!}" class="btn btn-primary btn-xs btnColor">
                            <i class="glyphicon glyphicon-th-large" aria-hidden="true"></i>
                        </a>

                        <?php if($value->employee_id != session()->get('logged_session_data.employee_id')) { ?>
                        <a href="{!! route('employee.edit', $value->employee_id) !!}" class="btn btn-success btn-xs btnColor">
                            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                        </a>
                        <a href="{!! route('employee.delete', $value->employee_id) !!}" data-token="{!! csrf_token() !!}"
                            data-id="{!! $value->employee_id !!}"
                            class="delete btn btn-danger btn-xs deleteBtn btnColor"><i class="fa fa-trash-o"
                                aria-hidden="true"></i></a>
                        <?php } ?>

                    </td>

                </tr>
            @endforeach

        </tbody>

    </table>

    <div class="text-center">

        {{ $results->links() }}

    </div>

</div>
