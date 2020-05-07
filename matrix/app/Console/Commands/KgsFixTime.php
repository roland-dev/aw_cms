<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Matrix\Models\KgsHistory;
use Matrix\Models\Twitter;
use Matrix\Models\Feed;

class KgsFixTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kgs:fixtime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix the kgs twitter created_at.';

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
        $roomIdList = config('kgs.room_id');
        $twitterList = Twitter::whereIn('category_code', array_values($roomIdList))->where('created_at', '>', '2019-05-21 17:00:00')->get();
        $historyList = KgsHistory::whereIn('_id', $twitterList->pluck('source_id')->toArray())->get()->toArray();
        $tsList = array_column($historyList, 'timestamp', '_id');
        $twitterList->each(function ($item, $key) use ($tsList) {
            $ts = (int)array_get($tsList, $item->source_id);
            $showTime = date('Y-m-d H:i:s', $ts / 1000);
            $item->created_at = $showTime;
            $item->updated_at = (string)date('Y-m-d H:i:s');
            $item->save();
        });

        $feedList = Feed::where('feed_type', 11)->where('add_time', '>', '2019-05-21 17:00:00')->get();
        $twitterIdList = $feedList->pluck('source_id')->toArray();
        $twitterList = Twitter::whereIn('id', $twitterIdList)->get()->toArray();
        $twitterTimeList = array_column($twitterList, 'created_at', 'id');
        $feedList->each(function ($item, $key) use ($twitterTimeList) {
            $ts = (string)array_get($twitterTimeList, $item->source_id);
            if (!empty($ts)) {
                $item->add_time = $ts;
                $item->save();
            }
        });
    }
}
