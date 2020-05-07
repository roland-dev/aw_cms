<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Matrix\Contracts\OpenApiContract;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class CustomAppUnlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customapp:unlock {code : Custom App Code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unlock a openapi user app';

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
    public function handle(OpenApiContract $openApi)
    {
        //
        $code = $this->argument('code');

        try {
            $customAppInfo = $openApi->getCustomApp($code)->unlock()->show();
            collect($customAppInfo)->each(function ($item, $key) {
                echo "$key:\t$item\n";
            });
        } catch (MatrixException $e) {
            Log::error("Custom App Error: ", [$e]);
            echo sprintf("Custom App Error [{$e->getCode()}] : {$e->getMessage()}\n");
        } catch (Exception $e) {
            Log::error("Custom App $code unknow error.", [$e]);
            echo "Unknow error.\n";
        }
    }
}
