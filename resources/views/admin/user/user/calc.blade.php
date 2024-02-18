<h1>Test Page</h1>
@php
$LOOP=0;
$CTC = 24000;
$BASIC = round($CTC * 40 / 100, 0);
$ESI_EMPLOYER_CONTRIBUTION=0;
$BONUS = round($BASIC * 8.33 / 100, 0);
$EPF_EMPLOYER_CONTRIBUTION=0;
$GROSS_SALARY=0;
$SPECIAL_ALLOWANCE=0;
$LEAVE_TRAVEL_ASSISTANCE= round($BASIC * 2 / 12);
$LOOP2=0;
$HRA=0;
regross:
$GROSS_SALARY = $CTC - ($EPF_EMPLOYER_CONTRIBUTION + $ESI_EMPLOYER_CONTRIBUTION + $BONUS);
$HRA=round($GROSS_SALARY * 30 / 100, 0);
$EPF_EMPLOYER_CONTRIBUTION=round(($GROSS_SALARY - $HRA) * 12 / 100, 0);
if($LOOP==0 || $GROSS_SALARY<21000) {
    $ESI_EMPLOYER_CONTRIBUTION=(round($GROSS_SALARY * 3.25 / 100, 0) );
} else {
    $ESI_EMPLOYER_CONTRIBUTION=0;
}
// if($LOOP2==0) {
//     $LOOP2++;
//     goto regross;
// }
$SPECIAL_ALLOWANCE = $CTC - ($BONUS + $ESI_EMPLOYER_CONTRIBUTION + $EPF_EMPLOYER_CONTRIBUTION + $LEAVE_TRAVEL_ASSISTANCE + $HRA + $BASIC);
$DATASET = [
    'CTC' => $CTC,
    'BASIC' => $BASIC,
    'HRA' => $HRA,
    'LEAVE_TRAVEL_ASSISTANCE' => $LEAVE_TRAVEL_ASSISTANCE,
    'SPECIAL_ALLOWANCE' => $SPECIAL_ALLOWANCE,
    'GROSS_SALARY' => $GROSS_SALARY,
    'EPF_EMPLOYER_CONTRIBUTION' => $EPF_EMPLOYER_CONTRIBUTION,
    'ESI_EMPLOYER_CONTRIBUTION' => $ESI_EMPLOYER_CONTRIBUTION,
    'BONUS' => $BONUS,
];
echo '<br>'.$LOOP .'. SET<br>';
foreach ($DATASET as $KEY => $VAL ) {
    echo $KEY.'='.$VAL.'<br>';
}
if($LOOP<1) {
    $LOOP++;
    goto regross;
}

// 24000 Format 1
if($CTC==24000) {
    $EXCELSET = [
        'CTC' => $CTC,
        'BASIC' => 9600,
        'HRA' => 6234,
        'LEAVE_TRAVEL_ASSISTANCE' => 1600,
        'SPECIAL_ALLOWANCE' => 3346,
        'GROSS_SALARY' => 20780,
        'EPF_EMPLOYER_CONTRIBUTION' => 1745,
        'ESI_EMPLOYER_CONTRIBUTION' => 675,
        'BONUS' => 800,
    ];
} else if($CTC==19000) {
    // 19000 Format 2
    $EXCELSET = [
        'CTC' => $CTC,
        'BASIC' => 7600,
        'HRA' => 4935,
        'LEAVE_TRAVEL_ASSISTANCE' => 1267,
        'SPECIAL_ALLOWANCE' => 2648,
        'GROSS_SALARY' => 16450,
        'EPF_EMPLOYER_CONTRIBUTION' => 1385,
        'ESI_EMPLOYER_CONTRIBUTION' => 535,
        'BONUS' => 633,
    ];
} else {
    $EXCELSET = [
        'CTC' => $CTC,
        'BASIC' => 0,
        'HRA' => 0,
        'LEAVE_TRAVEL_ASSISTANCE' => 0,
        'SPECIAL_ALLOWANCE' => 0,
        'GROSS_SALARY' => 0,
        'EPF_EMPLOYER_CONTRIBUTION' => 0,
        'ESI_EMPLOYER_CONTRIBUTION' => 0,
        'BONUS' => 0,
    ];
}

@endphp
<style>
    th.ok {
        color: green;
    }
    th.not {
        color: red;
    }
</style>
<table class="table" style="width:50%">
    <tbody>
        @foreach ($DATASET as $KEY => $VAL )
            <tr>
                @php
                    $LABEL = str_replace('_', ' ', $KEY);
                    // $LABEL = ucwords(strtolower($LABEL));
                    $CLASS = isset($EXCELSET[$KEY]) && $VAL == $EXCELSET[$KEY] ? 'ok' : 'not';
                    $DIFF = isset($EXCELSET[$KEY]) ? $VAL - $EXCELSET[$KEY] : '';
                @endphp
                <th>{{ $LABEL }}</th>
                <th>{{ $VAL }}</th>
                <th class="{{ $CLASS }}">{{ isset($EXCELSET[$KEY]) ? $EXCELSET[$KEY] : '' }}</th>
                <th>{{ $DIFF }}</th>
            </tr>
        @endforeach
    </tbody>
</table>
<p>&nbsp;</p>