@if (isset($body['user_data']))
<h2>Hello {{$body['user_data']['first_name']}},</h2><br>
@else
<h2>Dear Sir/Madam,</h2><br>
@endif
<h3>Leave Approved Details</h3><br>
<p>Employee Id :{{$body['finger_id']}}</p>
<p>Name : {{$body['name']}}</p>
<p>Date : {{dateConvertDBtoForm($body['date'])}}</p>
<p>From Date : {{dateConvertDBtoForm($body['from'])}}</p>
<p>To Date : {{dateConvertDBtoForm($body['to'])}}</p>
<p>Leave Type : {{$body['type']}}</p>
<p>No of Days : {{$body['days']}}</p>
<div style="width: 500px">
    <h3 class="text-center" style="color: green;">Accepted</h3>
</div>
<p>Approved By : {{$body['approve_name']}}</p>

 
From,<br>
{{ appName() }}<br>.