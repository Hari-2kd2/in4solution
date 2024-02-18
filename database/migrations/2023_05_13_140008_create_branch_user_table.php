<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_user', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->index();
            $table->bigInteger('branch_id')->unsigned()->index();
            $table->foreign('user_id')->references('user_id')->on('user')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('branch_id')->references('branch_id')->on('branch')->onUpdate('cascade')->onDelete('cascade');
            $table->primary(['user_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        // Schema::table('branch_user', function (Blueprint $table) {
        //     $table->dropForeign('branch_user_user_id_foreign');
        //     $table->dropForeign('branch_user_branch_id_foreign');
        //     $table->dropColumn('user_id');
        //     $table->dropColumn('branch_id');
        // });

        Schema::dropIfExists('branch_user');
    }
}
