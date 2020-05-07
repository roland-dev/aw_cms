<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdTerminalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_terminals', function (Blueprint $table) {
            $table->charset = "utf8";
            $table->collation = "utf8_unicode_ci";
            $table->increments('id')->comment('记录ID');
            $table->integer('ad_id')->comment('广告ID');
            $table->string("terminal_code", 32)->comment('展示终端Code');
            $table->softDeletes();
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
        Schema::dropIfExists('ad_terminals');
    }
}
