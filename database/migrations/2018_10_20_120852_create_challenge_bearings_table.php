<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeBearingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_bearings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('amount');
            $table->integer('total');
            $table->string('challenge_group');
            $table->timestamps();

            $table->index('challenge_group');
            $table->foreign('challenge_group')->references('group')->on('challenges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenge_bearings');
    }
}
