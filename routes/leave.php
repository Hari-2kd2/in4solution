<?php

use App\Model\PermissionMaster;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Leave\LeavePolicyController;
use App\Http\Controllers\Leave\PermissionMasterController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('downloadSummaryReport', 'Leave\ReportController@downloadSummaryReport');
});
Route::group(['middleware' => ['preventbackbutton', 'auth']], function () {

    Route::group(['prefix' => '/Leave/Encashment'], function () {
        // employee routers
        Route::get('/', ['as' => 'leave.Encashment', 'uses' => 'Leave\ApplyForLeaveController@Encashment']);
        Route::match(['get', 'post'], '/Apply', ['as' => 'leave.EncashmentApply', 'uses' => 'Leave\ApplyForLeaveController@EncashmentApply']);
        Route::post('/calculates', ['as' => 'leave.EncashmentCalculates', 'uses' => 'Leave\ApplyForLeaveController@EncashmentCalculates']);

        // amind, hr routers
        Route::get('/Applications', ['as' => 'leave.EncashmentApplications', 'uses' => 'Leave\ApplyForLeaveController@EncashmentApplications']);
        Route::get('/{id}/EncashmentDetail', ['as' => 'leave.EncashmentDetail', 'uses' => 'Leave\ApplyForLeaveController@EncashmentDetail']);
        Route::post('/{id}/EncashmentAction', ['as' => 'leave.EncashmentAction', 'uses' => 'Leave\ApplyForLeaveController@EncashmentAction']);
    });

    Route::group(['prefix' => 'manageHoliday'], function () {
        Route::get('/', ['as' => 'holiday.index', 'uses' => 'Leave\HolidayController@index']);
        Route::get('/create', ['as' => 'holiday.create', 'uses' => 'Leave\HolidayController@create']);
        Route::post('/store', ['as' => 'holiday.store', 'uses' => 'Leave\HolidayController@store']);
        Route::get('/{manageHoliday}/edit', ['as' => 'holiday.edit', 'uses' => 'Leave\HolidayController@edit']);
        Route::put('/{manageHoliday}', ['as' => 'holiday.update', 'uses' => 'Leave\HolidayController@update']);
        Route::delete('/{manageHoliday}/delete', ['as' => 'holiday.delete', 'uses' => 'Leave\HolidayController@destroy']);
    });

    Route::group(['prefix' => 'restrictedHoliday'], function () {
        Route::get('/', ['as' => 'restrictedHoliday.index', 'uses' => 'Leave\RestrictedHolidayController@index']);
        Route::get('/create', ['as' => 'restrictedHoliday.create', 'uses' => 'Leave\RestrictedHolidayController@create']);
        Route::post('/store', ['as' => 'restrictedHoliday.store', 'uses' => 'Leave\RestrictedHolidayController@store']);
        Route::get('/{restrictedHoliday}/edit', ['as' => 'restrictedHoliday.edit', 'uses' => 'Leave\RestrictedHolidayController@edit']);
        Route::put('/{restrictedHoliday}', ['as' => 'restrictedHoliday.update', 'uses' => 'Leave\RestrictedHolidayController@update']);
        Route::delete('/{RestrictedHoliday}/delete', ['as' => 'restrictedHoliday.delete', 'uses' => 'Leave\RestrictedHolidayController@destroy']);
    });

    Route::group(['prefix' => 'publicHoliday'], function () {
        Route::get('/', ['as' => 'publicHoliday.index', 'uses' => 'Leave\PublicHolidayController@index']);
        Route::get('/create', ['as' => 'publicHoliday.create', 'uses' => 'Leave\PublicHolidayController@create']);
        Route::post('/store', ['as' => 'publicHoliday.store', 'uses' => 'Leave\PublicHolidayController@store']);
        Route::get('/{publicHoliday}/edit', ['as' => 'publicHoliday.edit', 'uses' => 'Leave\PublicHolidayController@edit']);
        Route::put('/{publicHoliday}', ['as' => 'publicHoliday.update', 'uses' => 'Leave\PublicHolidayController@update']);
        Route::delete('/{publicHoliday}/delete', ['as' => 'publicHoliday.delete', 'uses' => 'Leave\PublicHolidayController@destroy']);
    });

    Route::group(['prefix' => 'weeklyHoliday'], function () {
        Route::get('/', ['as' => 'weeklyHoliday.index', 'uses' => 'Leave\WeeklyHolidayController@index']);
        Route::get('/create', ['as' => 'weeklyHoliday.create', 'uses' => 'Leave\WeeklyHolidayController@create']);
        Route::post('/store', ['as' => 'weeklyHoliday.store', 'uses' => 'Leave\WeeklyHolidayController@store']);
        Route::get('/{weeklyHoliday}/edit', ['as' => 'weeklyHoliday.edit', 'uses' => 'Leave\WeeklyHolidayController@edit']);
        Route::put('/{weeklyHoliday}', ['as' => 'weeklyHoliday.update', 'uses' => 'Leave\WeeklyHolidayController@update']);
        Route::delete('/{weeklyHoliday}/delete', ['as' => 'weeklyHoliday.delete', 'uses' => 'Leave\WeeklyHolidayController@destroy']);
        Route::post('/import', ['as' => 'weeklyHoliday.import', 'uses' => 'Leave\WeeklyHolidayController@importWeeklyHoliday']);
    });

    Route::group(['prefix' => 'compOff'], function () {
        Route::get('/', ['as' => 'compOff.index', 'uses' => 'Leave\CompOffController@index']);
        Route::get('/create', ['as' => 'compOff.create', 'uses' => 'Leave\CompOffController@create']);
        Route::post('/store', ['as' => 'compOff.store', 'uses' => 'Leave\CompOffController@store']);
        Route::get('/{compOff}/edit', ['as' => 'compOff.edit', 'uses' => 'Leave\CompOffController@edit']);
        Route::put('/{compOff}', ['as' => 'compOff.update', 'uses' => 'Leave\CompOffController@update']);
        Route::delete('/{compOff}/delete', ['as' => 'compOff.delete', 'uses' => 'Leave\CompOffController@destroy']);
        Route::get('/getWorkingtime', ['as' => 'compOff.getWorkingtime', 'uses' => 'Leave\CompOffController@getWorkingtime']);
    });
    Route::group(['prefix' => 'incentive'], function () {
        Route::get('/', ['as' => 'incentive.index', 'uses' => 'Leave\IncentiveController@index']);
        Route::get('/create', ['as' => 'incentive.create', 'uses' => 'Leave\IncentiveController@create']);
        Route::post('/store', ['as' => 'incentive.store', 'uses' => 'Leave\IncentiveController@store']);
        Route::get('/{incentive}/edit', ['as' => 'incentive.edit', 'uses' => 'Leave\IncentiveController@edit']);
        Route::put('/{incentive}', ['as' => 'incentive.update', 'uses' => 'Leave\IncentiveController@update']);
        Route::delete('/{incentive}/delete', ['as' => 'incentive.delete', 'uses' => 'Leave\IncentiveController@destroy']);
        Route::get('/getWorkingtime', ['as' => 'incentive.getWorkingtime', 'uses' => 'Leave\IncentiveController@getWorkingtime']);
    });
    Route::group(['prefix' => 'leaveBalance'], function () {
        Route::get('/', ['as' => 'leaveBalance.index', 'uses' => 'Leave\ApplyForLeaveBalanceController@index']);
        Route::get('/create', ['as' => 'leaveBalance.create', 'uses' => 'Leave\ApplyForLeaveBalanceController@create']);
        Route::post('/store', ['as' => 'leaveBalance.store', 'uses' => 'Leave\ApplyForLeaveBalanceController@store']);
        Route::get('/{leaveBalance}/edit', ['as' => 'leaveBalance.edit', 'uses' => 'Leave\ApplyForLeaveBalanceController@edit']);
        Route::put('/{leaveBalance}', ['as' => 'leaveBalance.update', 'uses' => 'Leave\ApplyForLeaveBalanceController@update']);
        Route::delete('/{leaveBalance}/delete', ['as' => 'leaveBalance.delete', 'uses' => 'Leave\ApplyForLeaveBalanceController@destroy']);
    });
    Route::group(['prefix' => 'leaveType'], function () {
        Route::get('/', ['as' => 'leaveType.index', 'uses' => 'Leave\LeaveTypeController@index']);
        Route::get('/create', ['as' => 'leaveType.create', 'uses' => 'Leave\LeaveTypeController@create']);
        Route::post('/store', ['as' => 'leaveType.store', 'uses' => 'Leave\LeaveTypeController@store']);
        Route::get('/{leaveType}/edit', ['as' => 'leaveType.edit', 'uses' => 'Leave\LeaveTypeController@edit']);
        Route::put('/{leaveType}', ['as' => 'leaveType.update', 'uses' => 'Leave\LeaveTypeController@update']);
        Route::delete('/{leaveType}/delete', ['as' => 'leaveType.delete', 'uses' => 'Leave\LeaveTypeController@destroy']);
    });

    Route::group(['prefix' => 'applyForLeave'], function () {
        Route::get('/', ['as' => 'applyForLeave.index', 'uses' => 'Leave\ApplyForLeaveController@index']);
        Route::get('/create', ['as' => 'applyForLeave.create', 'uses' => 'Leave\ApplyForLeaveController@create']);
        Route::post('/store', ['as' => 'applyForLeave.store', 'uses' => 'Leave\ApplyForLeaveController@store']);
        Route::post('getEmployeeLeaveBalance', 'Leave\ApplyForLeaveController@getEmployeeLeaveBalance');
        Route::post('applyForTotalNumberOfDays', 'Leave\ApplyForLeaveController@applyForTotalNumberOfDays'); 
        Route::get('cancel/{id}', ['as' => 'applyForLeave.cancel', 'uses' => 'Leave\ApplyForLeaveController@cancel']);
        Route::post('getEmployeeLeaveStatus', 'Leave\ApplyForLeaveController@getEmployeeLeaveStatus');
    });
    Route::group(['prefix' => 'permissionMaster'], function () {
        // Route::get('/', ['as' => 'permissionMaster.index', 'uses' => 'Leave\PermissionMasterController@index']);
        Route::get('/', [PermissionMasterController::class, 'index'])->name('permissionMaster.index');
        Route::post('/import', ['as' => 'permissionMaster.import', 'uses' => 'Leave\PermissionMasterController@import']);
        Route::get('/create', ['as' => 'permissionMaster.create', 'uses' => 'Leave\PermissionMasterController@create']);
        Route::post('/store', ['as' => 'permissionMaster.store', 'uses' => 'Leave\PermissionMasterController@store']);
        Route::get('/{permissionMaster}/edit', ['as' => 'permissionMaster.edit', 'uses' => 'Leave\PermissionMasterController@edit']);
        Route::put('/{permissionMaster}', ['as' => 'permissionMaster.update', 'uses' => 'Leave\PermissionMasterController@update']);
        Route::delete('/{permissionMaster}/delete', ['as' => 'permissionMaster.delete', 'uses' => 'Leave\PermissionMasterController@destroy']);
    });
    Route::group(['prefix' => 'applyForPermission'], function () {
        Route::get('/', ['as' => 'applyForPermission.index', 'uses' => 'Leave\ApplyForPermissionController@index']);
        Route::get('/create', ['as' => 'applyForPermission.create', 'uses' => 'Leave\ApplyForPermissionController@create']);
        Route::post('/store', ['as' => 'applyForPermission.store', 'uses' => 'Leave\ApplyForPermissionController@store']);
        Route::get('/request', ['as' => 'applyForPermission.permissionRequest', 'uses' => 'Leave\ApplyForPermissionController@permissionrequest']);
        Route::post('applyForTotalNumberOfPermissions', 'Leave\ApplyForPermissionController@applyForTotalNumberOfPermissions');
    });
    Route::group(['prefix' => 'applyForRestrictedHoliday'], function () {
        Route::get('/', ['as' => 'applyForRestrictedHoliday.index', 'uses' => 'Leave\ApplyForRestrictedHolidayController@index']);
        Route::get('/create', ['as' => 'applyForRestrictedHoliday.create', 'uses' => 'Leave\ApplyForRestrictedHolidayController@create']);
        Route::post('/store', ['as' => 'applyForRestrictedHoliday.store', 'uses' => 'Leave\ApplyForRestrictedHolidayController@store']);
        Route::post('getEmployeeLeaveBalance', 'Leave\ApplyForRestrictedHolidayController@getEmployeeLeaveBalance');
        Route::post('applyForTotalNumberOfDays', 'Leave\ApplyForRestrictedHolidayController@applyForTotalNumberOfDays');
        Route::get('/{applyForRestrictedHoliday}', ['as' => 'applyForRestrictedHoliday.show', 'uses' => 'Leave\ApplyForRestrictedHolidayController@show']);
    });

    Route::group(['prefix' => 'earnLeaveConfigure'], function () {
        Route::get('/', ['as' => 'earnLeaveConfigure.index', 'uses' => 'Leave\EarnLeaveConfigureController@index']);
        Route::post('updateEarnLeaveConfigure', 'Leave\EarnLeaveConfigureController@updateEarnLeaveConfigure');
    });

    Route::group(['prefix' => 'paidLeaveConfigure'], function () {
        Route::get('/', ['as' => 'paidLeaveConfigure.index', 'uses' => 'Leave\PaidLeaveConfigureController@index']);
        Route::post('updatePaidLeaveConfigure', 'Leave\PaidLeaveConfigureController@updatePaidLeaveConfigure');
    });

    Route::group(['prefix' => 'requestedApplication'], function () {
        Route::get('restrictedHolidayList', ['as' => 'restrictedHolidayList.index', 'uses' => 'Leave\ApplyForRestrictedHolidayController@restrictedHolidayList']);
        Route::get('/{requestedApplication}/RhviewDetails', ['as' => 'requestedApplication.RhviewDetails', 'uses' => 'Leave\ApplyForRestrictedHolidayController@RhviewDetails']);
        Route::put('/Rh/{requestedApplication}', ['as' => 'requestedApplication.RhUpdate', 'uses' => 'Leave\ApplyForRestrictedHolidayController@RhUpdate']);

        Route::get('/', ['as' => 'requestedApplication.index', 'uses' => 'Leave\RequestedApplicationController@index']);
        Route::get('/{requestedApplication}/viewDetails', ['as' => 'requestedApplication.viewDetails', 'uses' => 'Leave\RequestedApplicationController@viewDetails']);
        Route::get('/{requestedApplication}/viewDetailsFunctionalHead', ['as' => 'requestedApplicationFunctionalHead.viewDetailsFunctionalHead', 'uses' => 'Leave\RequestedApplicationController@viewDetailsFunctionalHead']);
        Route::put('/{requestedApplication}', ['as' => 'requestedApplication.update', 'uses' => 'Leave\RequestedApplicationController@update']);
    });
    Route::group(['prefix' => 'requestedPermissionApplication'], function () {
        Route::get('/', ['as' => 'requestedPermissionApplication.index', 'uses' => 'Leave\RequestedPermissionApplicationController@index']);
        Route::get('/{requestedPermissionApplication}/viewDetails', ['as' => 'requestedPermissionApplication.viewDetails', 'uses' => 'Leave\RequestedPermissionApplicationController@viewDetails']);
        Route::get('/{requestedPermissionApplication}/viewDetailsPermissionFunctionalHead', ['as' => 'requestedPermissionApplicationFunctionalHead.viewDetailsPermissionFunctionalHead', 'uses' => 'Leave\RequestedPermissionApplicationController@viewDetailsFunctionalHead']);
        Route::put('/{requestedPermissionApplication}', ['as' => 'requestedPermissionApplication.update', 'uses' => 'Leave\RequestedPermissionApplicationController@update']);
    });
    Route::get('/commonPermissionMaster', ['as' => 'commonPermissionMaster', 'uses' => 'Leave\PermissionMasterController@index']);
    Route::get('leaveReport', ['as' => 'leaveReport.leaveReport', 'uses' => 'Leave\ReportController@employeeLeaveReport']);
    Route::post('leaveReport', ['as' => 'leaveReport.leaveReport', 'uses' => 'Leave\ReportController@employeeLeaveReport']);
    Route::match(['get', 'post'], 'leaveBalanceReport', ['as' => 'leaveReport.leaveBalanceReport', 'uses' => 'Leave\ReportController@leaveBalanceReport']);
    Route::get('downloadLeaveReport', 'Leave\ReportController@downloadLeaveReport');

    Route::get('summaryReport', ['as' => 'summaryReport.summaryReport', 'uses' => 'Leave\ReportController@summaryReport']);
    Route::post('summaryReport', ['as' => 'summaryReport.summaryReport', 'uses' => 'Leave\ReportController@summaryReport']);

    Route::get('myLeaveReport', ['as' => 'myLeaveReport.myLeaveReport', 'uses' => 'Leave\ReportController@myLeaveReport']);
    Route::post('myLeaveReport', ['as' => 'myLeaveReport.myLeaveReport', 'uses' => 'Leave\ReportController@myLeaveReport']);
    Route::get('downloadMyLeaveReport', 'Leave\ReportController@downloadMyLeaveReport');

    Route::get('/weeklyHolidayTemplate', ['as' => 'weeklyHoliday.weeklyHolidayTemplate', 'uses' => 'Leave\WeeklyHolidayController@weeklyHolidayTemplate']);

    Route::post('approveOrRejectSupervisorLeaveApplication', 'Leave\RequestedApplicationController@approveOrRejectSupervisorLeaveApplication');
    Route::post('approveOrRejectFunctionalHeadLeaveApplication', 'Leave\RequestedApplicationController@approveOrRejectFunctionalHeadLeaveApplication');
    Route::post('approveOrRejectLeaveApplication', 'Leave\RequestedApplicationController@approveOrRejectLeaveApplication');
    Route::post('approveOrRejectPermissionApplication', 'Leave\RequestedPermissionApplicationController@approveOrRejectPermissionApplication');
    Route::post('approveOrRejectFunctionalHeadPermissionApplication', 'Leave\RequestedPermissionApplicationController@approveOrRejectFunctionalHeadPermissionApplication');

    // Route::group(['prefix' => 'requestedPaidLeaveApplication'], function () {
    //     Route::get('/', ['as' => 'requestedPaidLeaveApplication.index', 'uses' => 'Leave\RequestedPaidLeaveApplicationController@index']);
    //     Route::get('/{requestedPaidLeaveApplication}/viewDetails', ['as' => 'requestedPaidLeaveApplication.viewDetails', 'uses' => 'Leave\RequestedPaidLeaveApplicationController@viewDetails']);
    //     Route::put('/{requestedPaidLeaveApplication}', ['as' => 'requestedPaidLeaveApplication.update', 'uses' => 'Leave\RequestedPaidLeaveApplicationController@update']);
    // });

    // Route::group(['prefix' => 'paidLeaveReport'], function () {
    // Route::get('paidLeaveReport', ['as' => 'paidLeaveReport.paidLeaveReport', 'uses' => 'Leave\PaidLeaveReportController@employeePaidLeaveReport']);
    // Route::post('paidLeaveReport', ['as' => 'paidLeaveReport.paidLeaveReport', 'uses' => 'Leave\PaidLeaveReportController@employeePaidLeaveReport']);
    // Route::get('downloadPaidLeaveReport', 'Leave\PaidLeaveReportController@downloadPaidLeaveReport');
    // Route::get('paidLeaveSummaryReport', ['as' => 'paidLeaveReport.paidLeaveSummaryReport', 'uses' => 'Leave\PaidLeaveReportController@paidLeaveSummaryReport']);
    // Route::post('paidLeaveSummaryReport', ['as' => 'paidLeaveReport.paidLeaveSummaryReport', 'uses' => 'Leave\PaidLeaveReportController@paidLeaveSummaryReport']);
    // Route::get('downloadPaidLeaveSummaryReport', 'Leave\PaidLeaveReportController@downloadPaidLeaveSummaryReport');
    // Route::get('myPaidLeaveReport', ['as' => 'paidLeaveReport.myPaidLeaveReport', 'uses' => 'Leave\PaidLeaveReportController@myPaidLeaveReport']);
    // Route::post('myPaidLeaveReport', ['as' => 'paidLeaveReport.myPaidLeaveReport', 'uses' => 'Leave\PaidLeaveReportController@myPaidLeaveReport']);
    // Route::get('downloadMyPaidLeaveReport', 'Leave\PaidLeaveReportController@downloadMyPaidLeaveReport');
    // });

    // Route::group(['prefix' => 'assignEmployeeLeave'], function () {
    //     Route::get('/', ['as' => 'assignEmployeeLeave.index', 'uses' => 'Leave\AccessController@index']);
    // });


});
