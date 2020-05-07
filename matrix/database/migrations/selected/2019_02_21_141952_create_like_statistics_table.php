<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLikeStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('like_statistics', function (Blueprint $table) {
            $table->integer('article_id')->default(0)->comment('记录id');
            $table->string('type')->comment('记录类型');
            $table->integer('like_sum')->default(0)->comment('点赞总数');
            $table->integer('customer_like_sum')->default(0)->comment('客户点赞总数');
            $table->integer('staff_like_sum')->default(0)->comment('员工点赞总数');
            $table->primary(['article_id', 'type']);
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
        Schema::dropIfExists('like_statistics');
    }
}
