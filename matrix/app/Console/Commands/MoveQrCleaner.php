<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class MoveQrCleaner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:moveqrcleaner {moudle=default}';

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
        $this->description .= "php artisan command:moveqrcleaner commission1st";
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
        $moudle = $this->argument('moudle');
        $opKey = "matrix_cache:moveqr_file_cache_".$moudle."_*";
        $opKeyList = Redis::command('keys', [$opKey]);
        echo "moveqr: $moudle has ".count($opKeyList)." keys.\n";
        if (count($opKeyList) > 0) {
            foreach ($opKeyList as $printKey) {
                echo "key: $printKey\n";
            }
            echo "\n\n";
            $removeRes = Redis::command('del', [$opKeyList]);
            echo "$removeRes keys already removed!\n";
        } else {
            echo "No one removed!\n";
        }
    }
}
