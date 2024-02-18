<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeavePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */


    public function up()
    {
        Schema::create('leave_permission', function (Blueprint $table) {
            $table->increments('leave_permission_id');
            $table->integer('employee_id');
            $table->integer('branch_id');
            $table->date('leave_permission_date');
            $table->string('permission_duration');
            $table->text('leave_permission_purpose');
            $table->tinyInteger('status')->comment('status(1,2) = Pending,Reject')->default(1);
            $table->integer('department_head')->nullable();
            $table->tinyInteger('department_approval_status')->default(0);
            $table->string('from_time')->nullable();
            $table->string('to_time')->nullable();
            $table->text('head_remarks')->nullable();
            $table->date('approve_date')->nullable()->index();
            $table->date('reject_date')->nullable()->index();
            $table->integer('approve_by')->nullable()->index();
            $table->integer('reject_by')->nullable()->index();
            $table->integer('functional_head_approve_date')->nullable()->index();
            $table->integer('functional_head_reject_date')->nullable()->index();
            $table->integer('functional_head_approved_by')->nullable()->index();
            $table->integer('functional_head_reject_by')->nullable()->index();
            $table->string('status')->default('1')->index()->comment = "status(1,2,3) = Pending,Approve,Reject";
            $table->string('functional_head_status')->default('1')->index()->comment = "status(1,2,3) = Pending,Approve,Reject";
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_permission');
    }
}
