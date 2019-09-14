<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->string('worker_code')->unique(); //system generated ie AA12345
            $table->string('name');
            $table->string('code')->nullable(); //short or nick name
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->string('staff_id')->nullable();
            $table->string('qr'); // OWLY#<ORG CODE>#<WORKER CODE>
            $table->string('status'); // A for ACTIVE, I for INACTIVE
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workers');
    }
}
