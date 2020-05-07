<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->increments("id")->comment('广告ID');
            $table->string("location_code",32)->comment('广告位类型Code');
            $table->string("media_code",32)->comment('广告媒体类型Code');
            $table->string("operation_code",32)->comment('广告业务类型Code');
            $table->integer("operation_id")->default(0)->comment('广告来源ID 默认：0')->nullable();
            $table->string("title",255)->comment('广告名称');
            $table->string("img_src",300)->comment('图片地址');
            $table->string("relatively_file_path", 300)->comment('图片相对路径')->nullable();
            $table->string("url_link",300)->comment('链接地址');
            $table->timestamp("start_at")->comment('展示开始时间');
            $table->timestamp("end_at")->comment('展示结束时间');
            $table->integer("sort_num")->default(0)->comment('排序序号');
            $table->tinyInteger("disabled")->comment('是否禁用 0：否 1：是');
            $table->integer("creator_id")->comment("创建人");
            $table->integer("updated_user_id")->comment('最后修改人');
            $table->tinyInteger("need_popup")->comment('是否需要弹出框')->nullable();
            $table->string("popup_poster_url", 300)->comment('弹出海报地址（为空则与post_url相同处理)')->nullable();
            $table->string('relatively_popup_file_path', 300)->comment('弹出图片相对路径')->nullable();
            $table->string("jump_type", 32)->default('common_web')->comment('web: h5 webview; pdf: pdf viewer; video: 视频播放器; stream: 视频流; battle: 炒股大赛原生模块; broker: 券商开户原生模块...');
            $table->string("jump_params", 100)->comment('提供给原生跳转使用的参数')->nullable();
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
        Schema::dropIfExists('ads');
    }
}
