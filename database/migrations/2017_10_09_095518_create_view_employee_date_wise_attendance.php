<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreateViewGetEmployeeInOutData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('CREATE VIEW view_employee_date_wise_attendance as select
        `ms_sql`.`ID` AS `finger_print_id`, min(`ms_sql`.`datetime`) AS `in_time`,
        if((count(`ms_sql`.`datetime`) > 1),max(`ms_sql`.`datetime`),\'\') AS `out_time`,
        date_format(`ms_sql`.`datetime`,\'%Y-%m-%d\') AS `date`,
        timediff(max(`ms_sql`.`datetime`),min(`ms_sql`.`datetime`)) AS `working_time`
        from `ms_sql` group by date_format(`ms_sql`.`datetime`,\'%Y-%m-%d\'),`ms_sql`.`ID` 
        order by `ms_sql`.`datetime` , `ms_sql`.`ID`');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP VIEW IF EXISTS view_employee_date_wise_attendance');
    }
}
