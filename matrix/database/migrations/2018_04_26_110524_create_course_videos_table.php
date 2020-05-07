<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('picture_path', 500)->comment('封面图片');
            $table->tinyInteger('is_display')->comment('视频是否在app显示');
            $table->string('access');
            $table->string('watch');
            $table->string('end');
            $table->integer('course_id');
            $table->integer('video_signin_id');
            $table->tinyInteger('active')->default(1);
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
        Schema::dropIfExists('course_videos');
    }
}
