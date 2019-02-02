<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengeBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenge_badges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('challenge_id')->unsigned();
            $table->timestamps();

            $table->index('user_id');
            $table->index('challenge_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('challenge_id')->references('id')->on('challenges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenge_badges');
    }
}
