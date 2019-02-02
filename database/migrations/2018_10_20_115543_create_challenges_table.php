<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from')->unsigned();
            $table->integer('to')->unsigned();
            $table->string('group');
            $table->string('name');
            $table->string('description');
            $table->timestamps();

            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('challenges');
    }
}
