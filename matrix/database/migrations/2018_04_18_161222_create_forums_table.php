<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forums', function (Blueprint $table) {
            $table->increments('id')->comment('论坛ID');
            $table->string("theme",255)->comment('论坛主题');
            $table->string("img_src",255)->comment('图片地址');
            $table->string("relatively_file_path", 255)->comment('图片相对路径')->nullable();
            $table->string("url_key", 50)->comment('展示互动id')->nullable();
            $table->string("url_link", 500)->comment('展示互动播放url');
            $table->timestamp("forum_at")->comment('论坛直播日期');
            $table->timestamp("visible_at")->comment('论坛展示日期');
            $table->integer("duration")->comment('论坛时长(min)');
            $table->string("teacher",100)->comment('主讲嘉宾');
            $table->string("abstract", 500)->comment('论坛简介');
            $table->integer("creator_id")->comment("创建人");
            $table->integer("updated_user_id")->comment('最后修改人');
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
        Schema::dropIfExists('forums');
    }
}
