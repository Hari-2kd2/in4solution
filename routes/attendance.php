<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::get('mobileAttendanceReport', ['as' => 'mobileAttendance.mobileAttendanceReport', 'uses' => 'Attendance\MobileAttendanceController@mobileAttendanceReport']);
    Route::get('mobileAttendance', ['as' => 'mobileAttendance.mobileAttendance', 'uses' => 'Attendance\MobileAttendanceController@mobileAttendance']);
    Route::post('mobileAttendance', ['as' => 'mobileAttendance.mobileAttendance', 'uses' => 'Attendance\MobileAttendanceController@mobileAttendance']);
    Route::get('downloadMonthlyAttendanceExcel', 'Attendance\AttendanceReportController@monthlyExcel');
    Route::get('downloadSummaryAttendanceExcel', 'Attendance\AttendanceReportController@summaryExcel');
});

Route::group(['middleware' => ['preventbackbutton', 'auth']], function () {

    Route::group(['prefix' => 'approveOvertime'], function () {
        Route::get('/', ['as' => 'approveOvertime.index', 'uses' => 'Attendance\ApproveOverTimeController@index']);
        Route::post('/', ['as' => 'approveOvertime.index', 'uses' => 'Attendance\ApproveOverTimeController@index']);
        Route::get('/create', ['as' => 'approveOvertime.create', 'uses' => 'Attendance\ApproveOverTimeController@create']);
        Route::post('/store', ['as' => 'approveOvertime.store', 'uses' => 'Attendance\ApproveOverTimeController@store']);
        Route::post('/import', ['as' => 'approveOvertime.import', 'uses' => 'Attendance\ApproveOverTimeController@import']);
        Route::post('/export', ['as' => 'approveOvertime.export', 'uses' => 'Attendance\ApproveOverTimeController@export']);
        Route::post('/download', ['as' => 'approveOvertime.download', 'uses' => 'Attendance\ApproveOverTimeController@download']);
        Route::get('/employeeOvertimeTemplate', ['as' => 'approveOvertime.employeeOvertimeTemplate', 'uses' => 'Attendance\ApproveOverTimeController@employeeOvertimeTemplate']);
        Route::get('/{approveOvertime}/edit', ['as' => 'approveOvertime.edit', 'uses' => 'Attendance\ApproveOverTimeController@edit']);
        Route::put('/{approveOvertime}', ['as' => 'approveOvertime.update', 'uses' => 'Attendance\ApproveOverTimeController@update']);
        Route::delete('/{approveOvertime}/delete', ['as' => 'approveOvertime.delete', 'uses' => 'Attendance\ApproveOverTimeController@destroy']);
        Route::get('/reportDetails', ['as' => 'approveOvertime.reportDetails', 'uses' => 'Attendance\ApproveOverTimeController@reportDetails']);
    });

    Route::group(['prefix' => 'workShift'], function () {
        Route::get('/', ['as' => 'workShift.index', 'uses' => 'Attendance\WorkShiftController@index']);
        Route::get('/create', ['as' => 'workShift.create', 'uses' => 'Attendance\WorkShiftController@create']);
        Route::post('/store', ['as' => 'workShift.store', 'uses' => 'Attendance\WorkShiftController@store']);
        Route::get('/{workShift}/edit', ['as' => 'workShift.edit', 'uses' => 'Attendance\WorkShiftController@edit']);
        Route::put('/{workShift}', ['as' => 'workShift.update', 'uses' => 'Attendance\WorkShiftController@update']);
        Route::delete('/{workShift}/delete', ['as' => 'workShift.delete', 'uses' => 'Attendance\WorkShiftController@destroy']);
    });

    Route::group(['prefix' => 'shiftDetails'], function () {
        Route::get('/', ['as' => 'shiftDetails.index', 'uses' => 'Attendance\ShiftDetailsController@index']);
        Route::post('/', ['as' => 'shiftDetails.index', 'uses' => 'Attendance\ShiftDetailsController@index']);
        Route::post('/import', ['as' => 'shiftDetails.import', 'uses' => 'Attendance\ShiftDetailsController@import']);
        Route::post('/export', ['as' => 'shiftDetails.export', 'uses' => 'Attendance\ShiftDetailsController@export']);
        Route::get('/download', ['as' => 'shiftDetails.download', 'uses' => 'Attendance\ShiftDetailsController@download']);
        Route::get('/employeeShiftTemplate', ['as' => 'shiftDetails.employeeShiftTemplate', 'uses' => 'Attendance\ShiftDetailsController@employeeShiftTemplate']);
    });

    Route::group(['prefix' => 'deviceConfigure'], function () {
        Route::get('/', ['as' => 'deviceConfigure.index', 'uses' => 'Attendance\DeviceConfigurationController@index']);
        Route::get('/create', ['as' => 'deviceConfigure.create', 'uses' => 'Attendance\DeviceConfigurationController@create']);
        Route::post('/store', ['as' => 'deviceConfigure.store', 'uses' => 'Attendance\DeviceConfigurationController@store']);
        Route::get('/{deviceConfigure}/edit', ['as' => 'deviceConfigure.edit', 'uses' => 'Attendance\DeviceConfigurationController@edit']);
        Route::put('/{deviceConfigure}', ['as' => 'deviceConfigure.update', 'uses' => 'Attendance\DeviceConfigurationController@update']);
        Route::delete('/{deviceConfigure}/delete', ['as' => 'deviceConfigure.delete', 'uses' => 'Attendance\DeviceConfigurationController@destroy']);
        Route::get('/refresh', ['as' => 'deviceConfigure.refresh', 'uses' => 'Attendance\DeviceConfigurationController@refresh']);
        //Route::get('device/refresh', 'Attendance\DeviceConfigurationController@refresh')->name('device.refresh');
    });
    Route::group(['prefix' => 'templates'], function () {
        Route::get('/approveOvertimeTemplate', ['as' => 'templates.approveOvertimeTemplate', 'uses' => 'Attendance\ApproveOverTimeController@approveOvertimeTemplate']);
    });

    Route::get('generateReport', ['as' => 'generateReport.generateReport', 'uses' => 'Attendance\GenerateReportController@regenerateAttendanceReport']);
    Route::get('calculateAttendance', ['as' => 'calculateAttendance.calculateAttendance', 'uses' => 'Attendance\GenerateReportController@calculateAttendance']);

    Route::get('attendanceRecord', ['as' => 'attendanceRecord.attendanceRecord', 'uses' => 'Attendance\AttendanceReportController@attendanceRecord']);
    Route::post('attendanceRecord', ['as' => 'attendanceRecord.attendanceRecord', 'uses' => 'Attendance\AttendanceReportController@attendanceRecord']);

    Route::get('dailyAttendance', ['as' => 'dailyAttendance.dailyAttendance', 'uses' => 'Attendance\AttendanceReportController@dailyAttendance']);
    Route::post('dailyAttendance', ['as' => 'dailyAttendance.dailyAttendance', 'uses' => 'Attendance\AttendanceReportController@dailyAttendance']);
    Route::get('monthlyAttendance', ['as' => 'monthlyAttendance.monthlyAttendance', 'uses' => 'Attendance\AttendanceReportController@monthlyAttendance']);
    Route::post('monthlyAttendance', ['as' => 'monthlyAttendance.monthlyAttendance', 'uses' => 'Attendance\AttendanceReportController@monthlyAttendance']);

    Route::get('myAttendanceReport', ['as' => 'myAttendanceReport.myAttendanceReport', 'uses' => 'Attendance\AttendanceReportController@myAttendanceReport']);
    Route::post('myAttendanceReport', ['as' => 'myAttendanceReport.myAttendanceReport', 'uses' => 'Attendance\AttendanceReportController@myAttendanceReport']);

    Route::get('attendanceSummaryReport', ['as' => 'attendanceSummaryReport.attendanceSummaryReport', 'uses' => 'Attendance\AttendanceReportController@attendanceSummaryReport']);
    Route::post('attendanceSummaryReport', ['as' => 'attendanceSummaryReport.attendanceSummaryReport', 'uses' => 'Attendance\AttendanceReportController@attendanceSummaryReport']);

    Route::get('manualAttendance', ['as' => 'manualAttendance.manualAttendance', 'uses' => 'Attendance\ManualAttendanceController@manualAttendance']);
    Route::get('manualAttendance/filter', ['as' => 'manualAttendance.filter', 'uses' => 'Attendance\ManualAttendanceController@filterData']);
    Route::post('manualAttendanceStore', ['as' => 'manualAttendance.store', 'uses' => 'Attendance\ManualAttendanceController@store']);
    Route::post('manualAttendance', ['as' => 'manualAttendance.individualReport', 'uses' => 'Attendance\ManualAttendanceController@individualReport']);

    Route::get('downloadDailyAttendance', 'Attendance\AttendanceReportController@downloadDailyAttendance');
    Route::get('downloadMonthlyAttendance', 'Attendance\AttendanceReportController@downloadMonthlyAttendance');
    Route::get('downloadMyAttendance', 'Attendance\AttendanceReportController@downloadMyAttendance');
    Route::get('downloadAttendanceSummaryReport/{date}', 'Attendance\AttendanceReportController@downloadAttendanceSummaryReport');

    // get attendance by ip

    Route::post('ip-attendance', ['as' => 'ip.attendance', 'uses' => 'Attendance\ManualAttendanceController@ipAttendance']);

    // setup ip  attendance

    Route::get('setup-employee-attendance', ['as' => 'attendance.dashboard', 'uses' => 'Attendance\ManualAttendanceController@setupDashboardAttendance']);

    Route::post('setup-employee-attendance-post', ['as' => 'attendance.dashboard.post', 'uses' => 'Attendance\ManualAttendanceController@postDashboardAttendance']);
    Route::get('manualsynchronization', ['as' => 'access.manualsynchronization', 'uses' => 'Attendance\AttendanceReportController@manual']);
});
