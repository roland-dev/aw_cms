<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdOperationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_operation_types', function (Blueprint $table) {
            $table->charset = "utf8";
            $table->collation = "utf8_unicode_ci";
            $table->increments('id')->comment('广告业务类型ID');
            $table->string("code",32)->unique()->comment('广告业务类型code');
            $table->string("name",16)->comment('广告业务类型name');
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
        Schema::dropIfExists('ad_operation_types');
    }
}
