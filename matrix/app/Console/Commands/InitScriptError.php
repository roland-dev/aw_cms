<?php

namespace Matrix\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitScriptError extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:initScriptError';

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
        $this->description .= "php artisan command:initScriptError";
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
        DB::beginTransaction();
        try {
            DB::table('forums')->delete();
            DB::table('ads')->delete();
            DB::table('content_guards')
                ->where('uri', '/api/v2/propaganda/forum/{forumId}')
                ->orWhere('uri', '/api/v2/propaganda/ad/{adId}')
                ->delete();
            DB::table('ad_terminals')
                ->delete();
            DB::commit();
            echo "初始化成功";
        } catch (Exception $e) {
            DB::rollback();
            $e->getMessage();
            echo "初始化失败";
        }
        
    }
}
