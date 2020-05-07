<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category_code')->default('');
            $table->string('sub_category_code')->default('');
            $table->string('title')->default('');
            $table->string('summary')->default('');
            $table->string('description')->default('');
            $table->text('content');
            $table->string('audio_url')->default('');
            $table->integer('teacher_id')->default(0);
            $table->integer('modify_user_id')->default(0);
            $table->tinyInteger('show')->default(0);
            $table->string('cover_url')->default('');
            $table->bigInteger('read')->unsigned()->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
