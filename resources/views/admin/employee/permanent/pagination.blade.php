<div class="table-responsive">
    <table id="" class="table table-hover table-bordered manage-u-table">
        <thead>
            <tr class="tr_header">
                <th>@lang('common.serial')</th>
                <th>@lang('employee.photo')</th>
                <th>@lang('employee.employee_name')</th>
                <th>@lang('employee.department')</th>
                <th>@lang('employee.date_of_joining')</th>
                <th>@lang('common.permanent_status')</th>
                <th>@lang('common.action')</th>
            </tr>
        </thead>
        <tbody>
            {!! $sl = null !!}
            @foreach ($results as $key => $value)
                <tr class="{!! $value->employee_id !!}">
                    <td style="width: 100px;">{!! ++$sl !!}</td>
                    <td>
                        @if ($value->photo != '' && file_exists('uploads/employeePhoto/' . $value->photo))
                            <img style=" width: 60px; " src="{!! asset('uploads/employeePhoto/' . $value->photo) !!}" alt="user-img" class="img-circle">
                        @else
                            <img style=" width: 60px; " src="{!! asset('admin_assets/img/default.png') !!}" alt="user-img" class="img-circle">
                        @endif
                    </td>
                    <td>
                        <span class="font-medium">
                            {!! $value->displayNameWithCode() !!}
                        </span>
                        <br /><span class="text-muted">Role :
                            @if (isset($value->userName->role->role_name))
                                {!! $value->userName->role->role_name !!}
                            @endif
                        </span>
                        <br /><span class="text-muted">

                            @if (isset($value->supervisor))
                                @lang('employee.supervisor') : <span
                                    title="{{ $value->supervisorTitle }}">{!! $value->supervisorDetail() !!}</span>
                            @endif
                        </span>
                    </td>
                    <td>
                        <span class="font-medium">
                            @if (isset($value->department->department_name))
                                {!! $value->department->department_name !!}
                            @endif
                        </span>
                        <br /><span class="text-muted">Designation :
                            @if (isset($value->designation->designation_name))
                                {!! $value->designation->designation_name !!}
                            @endif
                        </span>
                        <br /><span class="text-muted">
                            @if (isset($value->branch->branch_name))
                                Branch : {!! $value->branch->branch_name !!}
                            @endif
                        </span>

                    </td>
                    </span>
                    <td>
                        <span class="font-medium">
                            {{ dateConvertDBtoForm($value->date_of_joining) }}
                        </span>
                        <br /><span class="text-muted">
                            {{ \Carbon\Carbon::parse($value->date_of_joining)->diffForHumans() }}
                        </span>
                    </td>
                    <td>
                        <select class="form-control permanent_status" style="font-size: 13px;width: 150px;">
                            <option value="0">@lang('employee_permanent.probation_period')</option>
                            <option value="1">@lang('employee_permanent.permanent')</option>
                            {{-- <option value="1">@lang('employee_permanent.resigned')</option> --}}
                        </select>
                    </td>
                    <input type="hidden" class="employee_id" value="{{ $value->employee_id }}">
                    <td style="width: 150px">
                        <button type="button" class="btn btn-sm btn-success updateStatus">
                            @lang('employee_permanent.update_status')
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="text-center">
        {{ $results->links('vendor.pagination.default') }}
    </div>
</div>
