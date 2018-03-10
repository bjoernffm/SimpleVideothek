<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid');
            $table->string('title');  
            $table->unsignedInteger('left');
            $table->unsignedInteger('right');
            $table->string('status');
            $table->string('type');
            $table->string('thumbnail')->nullable($value = true);
            $table->string('file')->nullable($value = true);
            $table->unsignedInteger('length')->nullable($value = true);
            $table->unsignedInteger('published')->nullable($value = true);
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
        Schema::dropIfExists('media');
    }
}
