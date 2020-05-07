<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;
use Matrix\Contracts\OpenApiContract;
use Matrix\Exceptions\MatrixException;
use Exception;
use Log;

class CustomAppCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customapp:create {name : Custom App Name} {remark? : Custom App Remark}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a openapi user app';

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
        $name = $this->argument('name');
        $remark = (string)$this->argument('remark');

        try {
            $customAppInfo = $openApi->generateCustomApp($name, $remark)->show();
            collect($customAppInfo)->each(function ($item, $key) {
                echo "$key:\t$item\n";
            });
        } catch (MatrixException $e) {
            Log::error("Custom App $name already exists.", [$e]);
            echo "Custom App $name already exists.\n";
        } catch (Exception $e) {
            Log::error("Custom App $name unknow error.", [$e]);
            echo "Unknow error.\n";
        }
    }
}
