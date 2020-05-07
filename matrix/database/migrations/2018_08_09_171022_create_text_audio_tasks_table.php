<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTextAudioTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('text_audio_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->default('');
            $table->text('content');
            $table->tinyInteger('status')->default(0);
            $table->dateTime('process_time');
            $table->bigInteger('process_duration')->default(0);
            $table->string('path')->default('');
            $table->integer('user_id')->default(0);
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
        Schema::dropIfExists('text_audio_tasks');
    }
}
