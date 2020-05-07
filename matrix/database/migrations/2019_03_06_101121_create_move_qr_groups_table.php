<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoveQrGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('move_qr_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->default('')->comment('唯一标识随机码');
            $table->string('title')->default('')->comment('固定二维码组标题');
            $table->integer('max_fans')->default(0)->comment('活码最大访问次数');
            $table->string('remark')->default('')->comment('固定二维码备注');
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
        Schema::dropIfExists('move_qr_groups');
    }
}
