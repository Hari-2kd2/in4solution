<div class="table-responsive">

    <table id="myDataTable" class="table table-bordered table-hover manage-u-table">

        <thead class="tr_header">

            <tr>

                <th>#</th> 
                <th>@lang('payroll.salary_month')</th>
                <th>@lang('payroll.emp_code')</th>
                {{-- <th>@lang('payroll.ctc')</th>
                <th>@lang('payroll.gross_salary')</th>
                <th>@lang('payroll.over_time')</th>
                <th>@lang('payroll.days_leaves')</th>
                <th>@lang('payroll.days_absents')</th> --}}
                <th>@lang('payroll.other_earnings')</th>
                <th>@lang('payroll.tds')</th>
                <th>@lang('payroll.salary_advance')</th>
                <th>@lang('payroll.labour_welfare')</th>
                <th>@lang('payroll.professional_tax')</th>
                <th>@lang('payroll.excess_telephone_usage')</th>
                <th>@lang('common.action')</th>

            </tr>

        </thead>

        <tbody>
@php
$sno = 1;
@endphp
            @foreach ($results as $key => $value)
                <tr class="{!! $value->payroll_upload_id !!}">
                    <td style="width: 100px;">{{$sno++}} </td> 
                    <td>{!! $value->salary_month !!} </td> 
                    <td>{!! $value->emp_code !!} </td> 
                    {{-- <td>{!! $value->ctc !!} </td>
                    <td>{!! $value->gross_salary !!} </td>
                    <td>{!! $value->over_time !!} </td>
                    <td>{!! $value->days_leaves !!}</td>
                    <td>{!! $value->days_absents !!}</td> --}}
                    <td>{!! $value->other_earnings !!} </td> 
                    <td>{!! $value->tds !!} </td> 
                    <td>{!! $value->salary_advance !!} </td> 
                    <td>{!! $value->labour_welfare !!} </td> 
                    <td>{!! $value->professional_tax !!} </td> 
                    <td>{!! $value->excess_telephone_usage !!} </td> 
                    <td style="width: 100px;">
                        <a href="{!! route('upload.payrolldelete', $value->payroll_upload_id ) !!}" data-token="{!! csrf_token() !!}" data-id="{!! $value->payroll_upload_id  !!}" class="deletepayroll btn btn-danger btn-xs deleteBtn btnColor"><i class="fa fa-trash-o" aria-hidden="true"></i></a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{-- <div class="text-center">
        {{ $results->links() }}
    </div> --}}
</div>
