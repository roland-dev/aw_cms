<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTerminalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terminals', function (Blueprint $table) {
            $table->charset = "utf8";
            $table->collation = "utf8_unicode_ci";
            $table->increments('id')->comment('展示终端ID');
            $table->string("code",32)->unique()->comment('展示终端code');
            $table->string("name",16)->comment('展示终端name');
            $table->tinyInteger("disabled")->default(0)->comment('是否禁用 0：否 1：是');
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
        Schema::dropIfExists('show_terminals');
    }
}
