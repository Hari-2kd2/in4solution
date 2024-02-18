<?php

use App\Components\CamAttendance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;


Route::get('/clear', function () {
    $cache = Artisan::call('cache:clear');
    $view = Artisan::call('view:clear');
    $route = Artisan::call('route:clear');
    $config = Artisan::call('config:cache');
    return redirect('/'); //Return anything
});

// front page route

Route::get('/', 'Front\WebController@index');
Route::get('job/{id}/{slug?}', 'Front\WebController@jobDetails')->name('job.details');
Route::post('job-application', 'Front\WebController@jobApply')->name('job.application');
Route::get('admin/pushSwitch', function (Request $request) {
    DB::table('sync_to_live')->where('id', $request->id)->update(['status' => $request->status]);
});
// front page route

Route::get('demo/{name}/{password}', 'User\LoginController@demoLogin')->name('demo-login');
Route::get('login', 'User\LoginController@index');
Route::post('login', 'User\LoginController@Auth');
Route::post('newPassword', 'User\ChangePasswordController@newPassword');

Route::get('mail', 'User\HomeController@mail');

Route::group(['prefix' => '2fa'], function () {
    Route::get('/', function () {
        return view('2fa.index');
    })->name('2fa');
    Route::get('/enable', 'Google2FAController@enableTwoFactor')->name('2fa.enable');
    Route::get('/disable', 'Google2FAController@disableTwoFactor')->name('2fa.disable');
    Route::get('/validate', 'User\LoginController@getValidateToken')->name('2fa.validate');
    Route::post('/validate', ['middleware' => 'throttle:5', 'uses' => 'User\LoginController@postValidateToken'])->name('2fa.index');
});



Route::group(['middleware' => ['preventbackbutton', 'auth']], function () {
    Route::get('filter', 'Attendance\FilterController@index');
    Route::get('monthlist', 'Attendance\FilterController@monthlist');
    Route::get('results', ['as' => 'search.result', 'uses' => 'Attendance\FilterController@results']);

    Route::get('/test', 'User\HomeController@test')->name('test');
    Route::get('/leaveTest', 'User\HomeController@leaveTest')->name('leaveTest');
    Route::get('/leaveTest/{id}', 'User\HomeController@leaveInfo')->name('leaveInfo');
    Route::match(['get', 'post'], '/leaveCredits', 'User\HomeController@leaveCredits')->name('leaveCredits');

    Route::get('dashboard', 'User\HomeController@index')->name('dashboard');
    Route::get('profile', 'User\HomeController@profile');
    Route::get('profile/{id}/edit', ['as' => 'profile.edit', 'uses' => 'User\HomeController@editProfile']);
    Route::post('profile/{id}', ['as' => 'profile.store', 'uses' => 'User\HomeController@storeProfile']);
    Route::get('profile/{id}/reject', ['as' => 'profile.reject', 'uses' => 'User\HomeController@rejectProfile']);
    Route::get('profile/{id}/accept', ['as' => 'profile.accept', 'uses' => 'User\HomeController@acceptProfile']);
    Route::get('profile/{id}/view', ['as' => 'profile.view', 'uses' => 'User\HomeController@viewProfile']);
    Route::get('/logout', 'User\LoginController@logout')->name('logout');
    Route::get('search', 'User\UserController@search')->name('search');

    Route::get('training/{id}/read', ['as' => 'training.read', 'uses' => 'AwardNoticeAndTraining\EmployeeTrainingController@markAsRead']);

    Route::resource('user', 'User\UserController', ['parameters' => ['user' => 'user_id']]);
    Route::resource('userRole', 'User\RoleController', ['parameters' => ['userRole' => 'role_id']]);
    Route::resource('rolePermission', 'User\RolePermissionController', ['parameters' => ['rolePermission' => 'id']]);
    Route::post('rolePermission/get_all_menu', 'User\RolePermissionController@getAllMenu');
    Route::resource('changePassword', 'User\ChangePasswordController', ['parameters' => ['changePassword' => 'id']]);

    // notification
    Route::get('/send-notifications', 'Notification\NotificationController@sendNotifications')->name('send.notifications');
    Route::get('/notifications', 'Notification\NotificationController@notifications')->name('notifications');
    Route::post('/notifications', 'Notification\NotificationController@notifications')->name('notifications');
    Route::get('/mark-as-read', 'Notification\NotificationController@markNotification')->name('admin.markNotification');
    Route::post('/mark-as-read', 'Notification\NotificationController@markNotification')->name('admin.markNotification');
});

Route::get('local/{language}', function ($language) {
    session(['my_locale' => $language]);
    return redirect()->back();
});

Route::get('attendanceLogs', [Controller::class, 'log']);

Route::get('getEmployees', function () {
    return CamAttendance::getEmployeeLists();
});

Route::get('sample', [Controller::class, 'sample']);
Route::post('logs', 'Attendance\DeviceConfigurationController@logs');
Route::get('logs', 'Attendance\DeviceConfigurationController@logs');

Route::post('/store-branch', 'User\LoginController@storeBranch')->name('store-branch');
