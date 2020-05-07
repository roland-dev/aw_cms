<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_notices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->default('');
            $table->string('content')->default('');
            $table->tinyInteger('target')->default(0);
            $table->integer('user_id')->default(0);
            $table->string('open_id')->default('');
            $table->tinyInteger('read')->default(0);
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
        Schema::dropIfExists('system_notices');
    }
}
