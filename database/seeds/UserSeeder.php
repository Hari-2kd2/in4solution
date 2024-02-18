<?php

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $time = Carbon::now();

        DB::table('user')->truncate();
        DB::table('user')->insert(
            [
                ['role_id' => 1, 'branch_id' => null, 'user_name' => 'superadmin', 'password' => bcrypt('Admin@123'), 'remember_token' => Str::random(10), 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time],
                ['role_id' => 2, 'branch_id' => 1, 'user_name' => 'admin1', 'password' => bcrypt('Admin@123'), 'remember_token' => Str::random(10), 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time],
                ['role_id' => 2, 'branch_id' => 2, 'user_name' => 'admin2', 'password' => bcrypt('Admin@123'), 'remember_token' => Str::random(10), 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time],
                ['role_id' => 2, 'branch_id' => 3, 'user_name' => 'admin3', 'password' => bcrypt('Admin@123'), 'remember_token' => Str::random(10), 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time],
                ['role_id' => 2, 'branch_id' => 4, 'user_name' => 'admin4', 'password' => bcrypt('Admin@123'), 'remember_token' => Str::random(10), 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time],
            ]
        );

        DB::table('work_shift')->truncate();
        DB::table('work_shift')->insert(
            [
                ['shift_name' => 'Day', 'branch_id' => 1, 'start_time' => '08:30:00', 'end_time' => '17:00:00', 'late_count_time' => '08:35:00', 'created_at' => $time, 'updated_at' => $time],
                ['shift_name' => 'Day', 'branch_id' => 2, 'start_time' => '08:30:00', 'end_time' => '17:00:00', 'late_count_time' => '08:35:00', 'created_at' => $time, 'updated_at' => $time],
                ['shift_name' => 'Day', 'branch_id' => 3, 'start_time' => '08:30:00', 'end_time' => '17:00:00', 'late_count_time' => '08:35:00', 'created_at' => $time, 'updated_at' => $time],
                ['shift_name' => 'Day', 'branch_id' => 4, 'start_time' => '08:30:00', 'end_time' => '17:00:00', 'late_count_time' => '08:35:00', 'created_at' => $time, 'updated_at' => $time],
            ]
        );

        DB::table('employee')->truncate();
        DB::table('employee')->insert(
            [

                [
                    'user_id' => 1, 'branch_id' => null, 'finger_id' => '1001', 'department_id' => 1, 'designation_id' => 1, 'work_shift_id' => 1, 'first_name' => "SuperAdmin", 'pay_grade_id' => 1, 'supervisor_id' => 1,
                    'date_of_birth' => "1995-01-01", 'date_of_joining' => '2017-03-01', 'gender' => 'Male', 'phone' => '1838784536', 'status' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time
                ],
                [
                    'user_id' => 2, 'branch_id' => 1, 'finger_id' => '1002', 'department_id' => 1, 'designation_id' => 1, 'work_shift_id' => 1, 'first_name' => "Admin1", 'pay_grade_id' => 1, 'supervisor_id' => 1,
                    'date_of_birth' => "1995-01-01", 'date_of_joining' => '2017-03-01', 'gender' => 'Male', 'phone' => '1838784536', 'status' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time
                ],
                [
                    'user_id' => 3, 'branch_id' => 2, 'finger_id' => '1003', 'department_id' => 1, 'designation_id' => 1, 'work_shift_id' => 1, 'first_name' => "Admin2", 'pay_grade_id' => 1, 'supervisor_id' => 1,
                    'date_of_birth' => "1995-01-01", 'date_of_joining' => '2017-03-01', 'gender' => 'Male', 'phone' => '1838784536', 'status' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time
                ],
                [
                    'user_id' => 4, 'branch_id' => 3, 'finger_id' => '1004', 'department_id' => 1, 'designation_id' => 1, 'work_shift_id' => 1, 'first_name' => "Admin3", 'pay_grade_id' => 1, 'supervisor_id' => 1,
                    'date_of_birth' => "1995-01-01", 'date_of_joining' => '2017-03-01', 'gender' => 'Male', 'phone' => '1838784536', 'status' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time
                ],
                [
                    'user_id' => 5, 'branch_id' => 4, 'finger_id' => '1005', 'department_id' => 1, 'designation_id' => 1, 'work_shift_id' => 1, 'first_name' => "Admin4", 'pay_grade_id' => 1, 'supervisor_id' => 1,
                    'date_of_birth' => "1995-01-01", 'date_of_joining' => '2017-03-01', 'gender' => 'Male', 'phone' => '1838784536', 'status' => 1, 'status' => 1, 'created_by' => 1, 'updated_by' => 1, 'created_at' => $time, 'updated_at' => $time
                ],
            ]
        );
    }
}
