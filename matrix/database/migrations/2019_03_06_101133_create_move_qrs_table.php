<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoveQrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('move_qrs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->default('')->comment('活码唯一标识随机码');
            $table->string('move_qr_group_code')->default('')->comment('静态码唯一标识随机码');
            $table->string('title')->default('')->comment('活码二维码组标题');
            $table->string('filename')->default('')->comment('活码二维码文件名');
            $table->string('remark')->default('')->comment('活码二维码备注');
            $table->integer('sort')->default(0)->comment('活码二维码排序');
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
        Schema::dropIfExists('move_qrs');
    }
}
