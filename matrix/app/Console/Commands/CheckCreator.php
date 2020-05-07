<?php

namespace Matrix\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckCreator extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkCreator';

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
        $this->description .= "php artisan command:checkCreator";
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
        try {
            $forumCreators = DB::select("select creator from forum where creator  is  not null");
            $creators = [];
            foreach($forumCreators as $forumCreator) {
                if (!in_array($forumCreator->creator, $creators)) {
                    $creators[] = $forumCreator->creator;
                }
            }
            

            $advertiseCreators = DB::select("select creator from advertise where creator is not null");
            foreach($advertiseCreators as $advertiseCreator) {
                if (!in_array($advertiseCreator->creator, $creators)) {
                    $creators[] = $advertiseCreator->creator;
                }
            }
            
            
            $ucenterRet = DB::select("select enterprise_userid from cms_ucenters");
            $uCreators = [];
            foreach($ucenterRet as $ucenter) {
                if (!in_array($ucenter->enterprise_userid, $uCreators)) {
                    $uCreators[] = $ucenter->enterprise_userid;
                }
            }

            $creatorOfUndefined = [];
            foreach ($creators as $creator) {
                if (!in_array($creator, $uCreators)) {
                    $creatorOfUndefined[]['enterprise_userid'] = $creator;
                }
            }

            if (count($creatorOfUndefined) > 0) {
                echo "检查成功, 广告论坛表 creator 迁移缺失 支持数据\n";
                $headers = ['enterprise_userid'];
                $this->table($headers, $creatorOfUndefined);
            } else {
                echo "检查成功，可以直接进行迁移数据";
            }
        } catch (Exception $e) {
            $e->getMessage();
            echo "检查失败";
        }
    }
}
