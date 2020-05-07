<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_locations', function (Blueprint $table) {
            $table->charset = "utf8";
            $table->collation = "utf8_unicode_ci";
            $table->increments('id')->comment('广告类型ID');
            $table->string("code",32)->unique()->comment('广告类型code');
            $table->string("name",16)->comment('广告类型name');
            $table->integer('num')->comment('广告位数量');
            $table->string('size')->comment('推荐尺寸');
            $table->string('popup_img_size')->comment('弹出广告推荐尺寸')->nullable();
            $table->string("terminal_code", 32)->comment('展示终端Code');
            $table->tinyInteger("disabled")->default(0)->comment('是否禁用 0：否 1：是');
            $table->integer('default_ad_id')->comment('默认广告ID');
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
        Schema::dropIfExists('ad_locations');
    }
}
