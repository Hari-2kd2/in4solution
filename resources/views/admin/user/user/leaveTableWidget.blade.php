@if ($EmployeeLeaves = $employeeInfo->EmployeeLeaves)
    @php
        $paternity_leave_ploicy = $employeeInfo->paternity_leave_ploicy();
        $maternity_leave_ploicy = $employeeInfo->maternity_leave_ploicy();
        // dd($maternity_leave_ploicy);
    @endphp
    <table class="table" id="leave-information-table">
        <thead>
            <th colspan="2">@lang('leave.leave_information')</th>
        </thead>
        <tbody>
            <tr>
                <td>@lang('leave.casual_leave')</td>
                <td>{{ $EmployeeLeaves->casual_leave }} Days</td>
            </tr>
            <tr>
                <td>@lang('leave.sick_leave')</td>
                <td>{{ $EmployeeLeaves->sick_leave }} Days</td>
            </tr>
            {{-- <tr>
                <td>@lang('leave.privilege_leave')</td>
                <td>{{ $EmployeeLeaves->privilege_leave }} Days</td>
            </tr>
            <tr>
                <td>@lang('leave.comp_off')</td>
                <td>{{ $EmployeeLeaves->comp_off }} Days</td>
            </tr>
            @if ($paternity_leave_ploicy && isset($paternity_leave_ploicy['status']) && $paternity_leave_ploicy['status'])
                <tr>
                    <td>@lang('leave.paternity_leave')</td>
                    <td>{{ $paternity_leave_ploicy['paternity_leave'] }} Days</td>
                </tr>
            @elseif($maternity_leave_ploicy && isset($maternity_leave_ploicy['status']) && $maternity_leave_ploicy['status'])
                <tr>
                    <td>@lang('leave.maternity_leave')</td>
                    <td>{{ (int) ($maternity_leave_ploicy['maternity_leave'] / 30) }} Months</td>
                </tr>
            @endif --}}
        </tbody>
    </table>
@endif
