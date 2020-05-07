<?php

namespace Matrix\Console\Commands;

use Exception;
use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateHistoryCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendlogdetailhistory:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update history records from tj_wx_send_log_detail';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $yiZhaoZhiSheng = [];
        $jiaZhiJueJin = [];
        $shunShiJuJi = [];
        $zhuanJiaKanPan = [];
        $guangGeLunShi = [];
        $extra = [];

        DB::beginTransaction();

        try{
            //更新log_detail表数据
            $detailRecordList = DB::select("SELECT * FROM `tj_wx_send_log_detail` WHERE `category` = 'openning'");

            foreach($detailRecordList as $detailRecord){
                $title = data_get($detailRecord, 'title');

                //if(!empty(strpos($title, '价值掘金'))){
                //    $jiaZhiJueJin[] = [
                //        'detail_id' => data_get($detailRecord, 'detail_id'),
                //        'title' => data_get($detailRecord, 'title'),
                //    ];
                //}else if(!empty(strpos($title, '顺势狙击'))){
                //    $shunShiJuJi[] = [
                //        'detail_id' => data_get($detailRecord, 'detail_id'),
                //        'title' => data_get($detailRecord, 'title'),
                //    ];
                //}else if(!empty(strpos($title, '一昭制胜'))){
                //    $yiZhaoZhiSheng[] = [
                //        'detail_id' => data_get($detailRecord, 'detail_id'),
                //        'title' => data_get($detailRecord, 'title'),
                //    ];
                //}else{
                //    $extra[] = [
                //        'detail_id' => data_get($detailRecord, 'detail_id'),
                //        'title' => data_get($detailRecord, 'title'),
                //    ];
                //}
                if(!empty(strpos($title, '专家看盘'))){
                    $zhuanJiaKanPan[] = [
                        'detail_id' => data_get($detailRecord, 'detail_id'),
                        'title' => data_get($detailRecord, 'title'),
                    ];
                }else if(!empty(strpos($title, '光哥论市'))){
                    $guangGeLunShi[] = [
                        'detail_id' => data_get($detailRecord, 'detail_id'),
                        'title' => data_get($detailRecord, 'title'),
                    ];
                }else{
                    $extra[] = [
                        'detail_id' => data_get($detailRecord, 'detail_id'),
                        'title' => data_get($detailRecord, 'title'),
                    ];
                }
            }

            //Log::info("价值掘金:".json_encode($jiaZhiJueJin));
            //Log::info("顺势狙击:".json_encode($shunShiJuJi));
            //Log::info("一昭制胜:".json_encode($yiZhaoZhiSheng));
            Log::info("专家看盘:".json_encode($zhuanJiaKanPan));
            Log::info("光哥论市:".json_encode($guangGeLunShi));
            Log::info("其他:".json_encode($extra));

            //$jiaZhiJueJinDetailIds = array_column($jiaZhiJueJin, 'detail_id');
            //$shunShiJuJiDetailIds = array_column($shunShiJuJi, 'detail_id');
            //$yiZhaoZhiShengDetailIds = array_column($yiZhaoZhiSheng, 'detail_id');
            $zhuanJiaKanPanDetailIds = array_column($zhuanJiaKanPan, 'detail_id');
            $guangGeLunShiDetailIds = array_column($guangGeLunShi, 'detail_id');

            //$jiaZhiJueJinDetailIdsStr = implode(',', $jiaZhiJueJinDetailIds);
            //$shunShiJuJiDetailIdsStr = implode(',', $shunShiJuJiDetailIds);
            //$yiZhaoZhiShengDetailIdsStr = implode(',', $yiZhaoZhiShengDetailIds);
            $zhuanJiaKanPanDetailIdsStr = implode(',', $zhuanJiaKanPanDetailIds);
            $guangGeLunShiDetailIdsStr = implode(',', $guangGeLunShiDetailIds);

            //if(!empty($jiaZhiJueJinDetailIdsStr)){
            //    DB::UPDATE("UPDATE `tj_wx_send_log_detail` SET `category` = 'jiazhijuejin' WHERE `detail_id` IN (".$jiaZhiJueJinDetailIdsStr.")");
            //    Log::info("UPDATE `tj_wx_send_log_detail` SET `category` = 'jiazhijuejin' WHERE `detail_id` IN (".$jiaZhiJueJinDetailIdsStr.")");
            //}

            //if(!empty($shunShiJuJiDetailIdsStr)){
            //    DB::UPDATE("UPDATE `tj_wx_send_log_detail` SET `category` = 'shunshijuji' WHERE `detail_id` IN (".$shunShiJuJiDetailIdsStr.")");
            //    Log::info("UPDATE `tj_wx_send_log_detail` SET `category` = 'shunshijuji' WHERE `detail_id` IN (".$shunShiJuJiDetailIdsStr.")");
            //}

            //if(!empty($yiZhaoZhiShengDetailIdsStr)){
            //    DB::UPDATE("UPDATE `tj_wx_send_log_detail` SET `category` = 'yizhaozhisheng' WHERE `detail_id` IN (".$yiZhaoZhiShengDetailIdsStr.")");
            //    Log::info("UPDATE `tj_wx_send_log_detail` SET `category` = 'yizhaozhisheng' WHERE `detail_id` IN (".$yiZhaoZhiShengDetailIdsStr.")");
            //}

            if(!empty($zhuanJiaKanPanDetailIdsStr)){
                DB::UPDATE("UPDATE `tj_wx_send_log_detail` SET `category` = 'wanjianfupan' WHERE `detail_id` IN (".$zhuanJiaKanPanDetailIdsStr.")");
                Log::info("UPDATE `tj_wx_send_log_detail` SET `category` = 'wanjianfupan' WHERE `detail_id` IN (".$zhuanJiaKanPanDetailIdsStr.")");
            }

            if(!empty($guangGeLunShiDetailIdsStr)){
                DB::UPDATE("UPDATE `tj_wx_send_log_detail` SET `category` = 'guanggelunshi' WHERE `detail_id` IN (".$guangGeLunShiDetailIdsStr.")");
                Log::info("UPDATE `tj_wx_send_log_detail` SET `category` = 'guanggelunshi' WHERE `detail_id` IN (".$guangGeLunShiDetailIdsStr.")");
            }


            //更新feed表数据
            //if(!empty($jiaZhiJueJinDetailIdsStr)){
            //    DB::UPDATE("UPDATE `feed` SET `category_key` = 'jiazhijuejin' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$jiaZhiJueJinDetailIdsStr.")");
            //    Log::info("UPDATE `feed` SET `category_key` = 'jiazhijuejin' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$jiaZhiJueJinDetailIdsStr.")");
            //}

            //if(!empty($shunShiJuJiDetailIdsStr)){
            //    DB::UPDATE("UPDATE `feed` SET `category_key` = 'shunshijuji' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$shunShiJuJiDetailIdsStr.")");
            //    Log::info("UPDATE `feed` SET `category_key` = 'shunshijuji' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$shunShiJuJiDetailIdsStr.")");
            //}

            //if(!empty($yiZhaoZhiShengDetailIdsStr)){
            //    DB::UPDATE("UPDATE `feed` SET `category_key` = 'yizhaozhisheng' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$yiZhaoZhiShengDetailIdsStr.")");
            //    Log::info("UPDATE `feed` SET `category_key` = 'yizhaozhisheng' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$yiZhaoZhiShengDetailIdsStr.")");
            //}

            if(!empty($zhuanJiaKanPanDetailIdsStr)){
                DB::UPDATE("UPDATE `feed` SET `category_key` = 'wanjianfupan' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$zhuanJiaKanPanDetailIdsStr.")");
                Log::info("UPDATE `feed` SET `category_key` = 'wanjianfupan' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$zhuanJiaKanPanDetailIdsStr.")");
            }

            if(!empty($guangGeLunShiDetailIdsStr)){
                DB::UPDATE("UPDATE `feed` SET `category_key` = 'guanggelunshi' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$guangGeLunShiDetailIdsStr.")");
                Log::info("UPDATE `feed` SET `category_key` = 'guanggelunshi' WHERE `category_key` = 'openning' AND `feed_type` = 2 AND `source_id` IN (".$guangGeLunShiDetailIdsStr.")");
            }

            DB::commit();
            echo '更新成功';
        }catch(Exception $e){
            DB::rollback();
            echo '更新失败';
            Log::info($e->getMessage(), [$e]);
        }
    }
}
