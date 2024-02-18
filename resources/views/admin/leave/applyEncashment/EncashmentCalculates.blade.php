@php
use App\Model\LeaveApplication;
use App\Lib\Enumerations\LeaveStatus;
@endphp
<table class="table" id="EncashmentCalculates" border="1">
	<thead>
		<th>@lang('leave.LEAVE_PERDAY_AMOUNT')</th>
		<th>@lang('leave.LEAVE_ENCASHMENT_AMOUNT')</th>
	</thead>
	<tbody>
		<tr>
			<td><span class="inr">₹</span>{{ $DATA['LEAVE_PERDAY_AMOUNT'] }}</td>
			<td><span class="inr">₹</span>{{ $DATA['LEAVE_ENCASHMENT_AMOUNT'] }}</td>
		</tr>
	</tbody>
</table>