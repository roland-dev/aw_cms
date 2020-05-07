<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoSigninsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_signins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('video_key');
            $table->integer('creator_user_id');
			$table->string('url');
            $table->integer('category');
            $table->integer('author');
            $table->string('title');
            $table->text('description')->nullable();
           // $table->timestamp('published_at')->nullable();
            $table->string('published_at')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->timestamps();
            $table->index('title'); 
            $table->index('created_at'); 
            $table->index('updated_at'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_signins');
    }
}
