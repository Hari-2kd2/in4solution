<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'api', 'prefix' => 'mobile'], function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('register', 'Api\AuthController@register');
    Route::post('logout', 'Api\AuthController@logout');
    Route::get('sample', 'Api\AuthController@sample');
    Route::get('refresh', 'Api\AuthController@refresh');

    Route::group(['prefix' => 'attendance'], function () {
        Route::post('employee_attendance_list', 'Api\AttendanceController@apiattendancelist');
        Route::get('my_attendance_report', 'Api\AttendanceReportController@myAttendanceReport');
        Route::get('download_my_attendance', 'Api\AttendanceReportController@downloadMyAttendance');
        Route::get('monthly_attendance', 'Api\AttendanceReportController@findAttendanceSummaryReport');
        Route::get('monthly_dropdown_list', 'Api\AttendanceReportController@monthlyDropdownList');
    });

    Route::group(['prefix' => 'leave'], function () {
        Route::get('index', 'Api\ApplyForLeaveController@index');
        Route::get('create', 'Api\ApplyForLeaveController@create');
        Route::get('dateChanged', 'Api\ApplyForLeaveController@dateChanged');
        Route::post('store', 'Api\ApplyForLeaveController@store');
        Route::get('cancel', ['as' => 'applyForLeave.cancel', 'uses' => 'Api\ApplyForLeaveController@cancel']);
        Route::post('update', 'Api\ApplyForLeaveController@update');
    });

    // Route::group(['middleware' => 'jwt.verify'], static function( $router){
        Route::group(['prefix' => 'payroll'], function () {
            Route::get('/payroll_list', 'Api\PayslipController@myPayroll');
            Route::get('/downloadPayslip', 'Api\PayslipController@downloadPayslip');
        });
    // });

    Route::group(['prefix' => 'leaveApplication'], function () {  // admin management
        Route::get('index', 'Api\RequestedApplicationController@index');
        Route::get('view', 'Api\RequestedApplicationController@view');
        Route::post('update', 'Api\RequestedApplicationController@update');

        Route::get('rhIndex', 'Api\RequestedApplicationController@rhIndex');
        Route::get('rhView', 'Api\RequestedApplicationController@rhView');
        Route::post('rhUpdate', 'Api\RequestedApplicationController@rhUpdate');
   
        Route::get('permissionIndex', 'Api\RequestedApplicationController@permissionIndex');
        Route::get('permissionView', 'Api\RequestedApplicationController@permissionView');
        Route::post('permissionUpdate', 'Api\RequestedApplicationController@permissionUpdate');
    });

    Route::group(['prefix' => 'leaveRhApplication'], function () {
        Route::get('index', 'Api\RequestedApplicationController@index');
        Route::get('view', 'Api\RequestedApplicationController@view');
        Route::post('update', 'Api\RequestedApplicationController@update');
    });

    Route::group(['prefix' => 'rh'], function () {
        Route::get('index', 'Api\ApplyForRhController@index');
        Route::get('create', 'Api\ApplyForRhController@create');
        Route::post('store', 'Api\ApplyForRhController@store');
    });
    Route::group(['prefix' => 'permission'], function () {
        Route::get('index', 'Api\ApplyForPermissionController@index');
        Route::get('create', 'Api\ApplyForPermissionController@create');
        Route::get('dateChanged', 'Api\ApplyForPermissionController@dateChanged');
        Route::post('store', 'Api\ApplyForPermissionController@store');
    });
    
    // Route::group(['prefix' => 'holiday'], function () {
    //     Route::get('index',   'Api\RestrictedHolidayController@index');
    //     Route::post('store', 'Api\RestrictedHolidayController@store');
    // });
    Route::group(['middleware' => 'jwt.verify'], static function( $router){
        Route::post('change_password', 'Api\AuthController@changePassword');
    });
    Route::post('forgetpassword', 'Api\AuthController@forgetPassword');
});
