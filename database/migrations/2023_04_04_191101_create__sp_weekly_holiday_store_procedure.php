<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSPWeeklyHolidayStoreProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS SP_getWeeklyHoliday; CREATE  PROCEDURE SP_getWeeklyHoliday()
        BEGIN
        select day_name , employee_id from  weekly_holiday where status=1;
        END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS SP_getWeeklyHoliday');
    }
}
