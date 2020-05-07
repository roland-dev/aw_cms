<?php

namespace Matrix\Console\Commands;

use Log;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Matrix\Models\Ad;

class MigrationOfPropaganda extends Command
{
    protected $packageCodeNodeMap = [
        "180" => ['basic'],
        "148" => ['vy_service', 'vy_1111_service', 'vy_qnb_1111_service', 'vy_qnb_service'],
        "123" => ['xy_jdb_service', 'xy_jdb_spf_service', 'xy_qnb_service'],
        "124" => ['ky_qnb_service', 'ky_qnb_spf_service'],
        "23" => ['zy_qnb_service', 'zy_jcb_service', 'zy_lnb_service', 'zy_snb_service'],
        "204" => ['user_sales_service', 'user_baoxian_service', 'user_director_service', 'user_it_service', 'user_manager_service', 'user_risk_service', 'user_tougu_service', 'user_yanjiu_service', 'user_yongjin_service'],
        "156" => ['vy_1111_expire', 'vy_qnb_1111_expire', 'vy_qnb_expire', 'vy_expire'],
        "24" => ['xy_jdb_expire', 'xy_jdb_spf_expire', 'xy_qnb_expire'],
        "25" => ['ky_qnb_expire', 'ky_qnb_spf_expire'],
        "26" => ['zy_qnb_expire', 'zy_jcb_expire', 'zy_lnb_expire', 'zy_snb_expire']
    ];

    protected $adSourceType = [
        '0' => 'ad',
        '1' => 'forum'
    ];

    protected $defaultForumUrl = '/forum/forum.html?id=';

    protected $terminalCodes = [
        'pc', 'ios', 'android'
    ];

    

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrationOfPropaganda {defaultCreator=fuxuena}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command description\n";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->description .= "php artisan command:migrationOfPropaganda fuxuena ";
        $this->description .= "php artisan command:migrationOfPropaganda defaultCreator=fuxuena";
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
        $defaultCreator = $this->argument('defaultCreator');
        DB::beginTransaction();
        try {
            $defaultUserId = DB::table('ucenters')
                                ->where('enterprise_userId', $defaultCreator)
                                ->value('user_id');

            $cmsUsers = DB::table('ucenters')
                            ->pluck('user_id', 'enterprise_userid');

            $forumsData = DB::select('select * from forum');

            // old forum_id => forums->id
            $forumIdMapping = [];

            foreach($forumsData as $forumData) {
                $forum = [];
                $forum['theme'] = $forumData->title;
                $forum['img_src'] = $forumData->poster_url;
                $forum['url_key'] = $forumData->gensee_key;
                $forum['url_link'] = $forumData->gensee_url;
                $forum['forum_at'] = $forumData->forum_date;
                $forum['visible_at'] = $forumData->visible_date;
                $forum['duration'] = intval($forumData->duration);
                $forum['teacher'] = $forumData->author;
                $forum['abstract'] = $forumData->summary;
                $forum['creator_id'] = $forumData->creator ? $cmsUsers[$forumData->creator] : $defaultUserId;
                $forum['updated_user_id'] = $forumData->creator ? $cmsUsers[$forumData->creator] : $defaultUserId;
                $forum['created_at'] = $forumData->add_time;
                $forum['updated_at'] = $forumData->update_time;

                // insert
                $forumId = DB::table('forums')
                            ->insertGetId($forum);
                
                $forumIdMapping[$forumData->forum_id] = $forumId;

                // 权限节点
                $nodeArr = [];

                if (empty($forumData->target_users)) {
                    $nodeArr = [180];
                } else {
                    $targetUsersStr = rtrim($forumData->target_users, '"]');
                    $targetUsersStr = ltrim($targetUsersStr, '["');
                    $nodeArr = explode('","', $targetUsersStr);
                }

                foreach ($nodeArr as $node) {
                    if (empty($this->packageCodeNodeMap[$node])) {
                        continue;
                    }
                    $contentGuardsArr = [];
                    foreach ($this->packageCodeNodeMap[$node] as $packageCode) {
                        $contentGuardsArr[] = [
                            'service_code' => $packageCode,
                            'uri' => '/api/v2/propaganda/forum/{forumId}',
                            'param1' => $forumId
                        ];
                    }
                    DB::table('content_guards')
                        ->insert($contentGuardsArr);
                }
            }

            $adsData = DB::select('select * from advertise');
            
            foreach ($adsData as $adData) {
                $ad = [];
                $ad['id'] = $adData->ad_id;
                $ad['location_code'] = "banner";
                $ad['media_code'] = 'image';
                $ad['operation_code'] = $this->adSourceType[$adData->source_type];
                $ad['operation_id'] = $adData->source_type === 1 ? $forumIdMapping[$adData->source_id] : 0;
                $ad['title'] = $adData->title;
                $ad['img_src'] = $adData->poster_url;
                if (strpos($adData->jump_url, 'http') !== 0) {
                    $oldForumId = self::getQueryParam($adData->jump_url, 'id');
                    $urlLink = $this->defaultForumUrl . $forumIdMapping[$oldForumId];
                } else {
                    $urlLink = $adData->jump_url;
                }
                $ad['url_link'] = $urlLink;
                $ad['start_at'] = $adData->start_time;
                $ad['end_at'] = $adData->end_time;
                $ad['disabled'] = 0;                
                $ad['creator_id'] = $adData->creator ? $cmsUsers[$adData->creator] : $defaultUserId;
                $ad['updated_user_id'] = $adData->creator ? $cmsUsers[$adData->creator] : $defaultUserId;
                $ad['need_popup'] = $adData->need_popup && $adData->need_popup !== '' ? $adData->need_popup : 0;
                $ad['popup_poster_url'] = $adData->popup_poster_url;
                $ad['jump_type'] = $adData->jump_type;
                $ad['jump_params'] = $adData->jump_params;
                $ad['created_at'] = $adData->add_time;
                $ad['updated_at'] = $adData->update_time;

                // insert
                DB::table('ads')
                    ->insert($ad);

                // 添加 ad_terminals 
                $adTerminalsArr = [];
                foreach ($this->terminalCodes as $terminalCode) {
                    $adTerminalsArr[] = [
                        'ad_id' => $adData->ad_id,
                        'terminal_code' => $terminalCode,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
                DB::table('ad_terminals')
                    ->insert($adTerminalsArr);

                // 权限节点
                $nodeArr = [];

                if (empty($forumData->target_users)) {
                    $nodeArr = [180];
                } else {
                    $targetUsersStr = rtrim($adData->target_users, '"]');
                    $targetUsersStr = ltrim($targetUsersStr, '["');
                    $nodeArr = explode('","', $targetUsersStr);
                }

                foreach ($nodeArr as $node) {
                    if (empty($this->packageCodeNodeMap[$node])) {
                        continue;
                    }
                    $contentGuardsArr = [];
                    foreach ($this->packageCodeNodeMap[$node] as $packageCode) {
                        $contentGuardsArr[] = [
                            'service_code' => $packageCode,
                            'uri' => '/api/v2/propaganda/ad/{adId}',
                            'param1' => $adData->ad_id
                        ];
                    }
                    DB::table('content_guards')
                        ->insert($contentGuardsArr);
                }


                if ($adData->is_delete === 1) {
                    Ad::where('id', $adData->ad_id)->delete();
                }
            }
            $this->line("迁移成功");
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error("MigrationOfPropaganda: ", [$e]);
            $this->error("迁移失败");
        }
    }

    private function getQueryParam($url, $key) {
        $arr = parse_url($url);
        $arr_query = self::convertUrlQuery($arr['query']);
        return $arr_query[$key];
    }

    private function convertUrlQuery($query){
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }
}
