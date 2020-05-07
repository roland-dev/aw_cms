<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Matrix\Models\Twitter;
use Matrix\Models\ArticleLike;
use Matrix\Models\LikeStatistic;
use Matrix\Models\KgsHistory;
use Matrix\Models\KgsVote;
use Exception;
use Log;
use DB;

class KgsFixVoteData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kgs:fix-vote-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the kgs sync vote wrong data.';

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
        $startTime = '2019-05-21 17:00:00';
        $endTime = '2019-05-21 23:59:59';

        $articleLikeList = ArticleLike::whereBetween('created_at', [$startTime, $endTime])
            ->where('type', 'twitter')->where('user_type', '<>', '')->where('udid', '')->where('session_id', '')->get();
        $articleIdList = $articleLikeList->pluck('article_id')->unique()->toArray();

        $twitterList = Twitter::whereIn('id', $articleIdList)->get();
        $msgIdList = $twitterList->pluck('source_id')->toArray();

        DB::beginTransaction();
        try {
            ArticleLike::where('type', 'twitter')->whereIn('article_id', $articleIdList)->delete();
            LikeStatistic::where('type', 'twitter')->whereIn('article_id', $articleIdList)->delete();
            KgsVote::whereIn('msgId', $msgIdList)->update(['cmsSync' => NULL]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('重置点赞数据错误.', [$e]);
        }

        Log::info('已清除错误数据，可以开始重新同步.');
    }
}
