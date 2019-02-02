<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoStatisticRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_statistic_records', function (Blueprint $table) {
            $table->uuid('id');
            $table->integer('user_id')->unsigned();
            $table->integer('media_id')->unsigned();
            $table->double('from', 10, 5)->comment('in seconds');
            $table->double('to', 10, 5)->comment('in seconds');
            $table->timestamps();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_statistic_records');
    }
}
