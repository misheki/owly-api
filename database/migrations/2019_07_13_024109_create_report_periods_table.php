<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportPeriodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->string('title'); //15th of the month, 30th of the month
            $table->smallInteger('run_on'); //date of the month to run report
            $table->smallInteger('period_start_date');  
            $table->smallInteger('period_start_month'); // 0 if current month, -1 if previous month and so on
            $table->smallInteger('period_end_date');
            $table->smallInteger('period_end_month'); // 0 if current, -1 if previous and so on
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
        Schema::dropIfExists('report_periods');
    }
}
