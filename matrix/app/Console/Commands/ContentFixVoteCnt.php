<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Matrix\Models\ArticleLike;
use Matrix\Models\LikeStatistic;
use Log;
use DB;

class ContentFixVoteCnt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:fix-vote-cnt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $articleLikeList = ArticleLike::select('type', 'article_id')->whereBetween('created_at', ['2019-05-01 00:00:00', '2019-05-31 23:59:59'])->get()->toArray();
        $articleList = array_values(collect($articleLikeList)->unique()->toArray());
        foreach ($articleList as $article) {
            $likeStatistic = LikeStatistic::where($article)->update([
                'like_sum' => ArticleLike::where($article)->count(),
                'customer_like_sum' => ArticleLike::where($article)->where('user_type', '<>', 'ROLE_STAFF')->count(),
                'staff_like_sum' => ArticleLike::where($article)->where('user_type', 'ROLE_STAFF')->count(),
            ]);

            Log::info("Update like statistic: $likeStatistic.", [$article]);
        }
    }
}
