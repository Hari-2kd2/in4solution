<?php

namespace App\Providers;

use App\Model\Employee;
use App\Components\Common;

use App\Model\EmployeeObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $TODAY = date('Y-m-d');
        Config::set('leave.past_day', 2);
        Config::set('leave.future_day', 60);
        Config::set('leave.weekly_holiday', 'Sunday');
        Config::set('leave.past_date', dateConvertDBtoForm(operateDays($TODAY, Config('leave.past_day'), '-')));
        Config::set('leave.future_date', dateConvertDBtoForm(operateDays($TODAY, Config('leave.future_day'))));
        Employee::observe(EmployeeObserver::class);
        Schema::defaultStringLength(191);
        Paginator::defaultSimpleView('vendor.pagination.default');
        Validator::extend("emails", function ($attribute, $value, $parameters) {
            $rules = ['email' => 'required|email'];
            $value = str_replace(PHP_EOL, ',', $value);
            $emails = array_map('trim', explode(',', $value)); //$value
            foreach ($emails as $email) {
                if (!$email) {
                    continue;
                }
                $data = ['email' => $email];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return false;
                }
            }
            return true;
        });

        Validator::extend("userids", function ($attribute, $value, $parameters) {
            $activeBranch = Common::activeBranch();
            $rules = ['id' => 'required|exists:employee,employee_id,branch_id,' . $activeBranch];
            // $value = str_replace(PHP_EOL,',', $value);
            $userids = array_map('trim', $value); //$value
            foreach ($userids as $id) {
                if (!$id) {
                    continue;
                }
                $data = ['id' => $id];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
