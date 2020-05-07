<?php

namespace Matrix\Console\Commands;

use Illuminate\Console\Command;

class MadeMoveQrFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:mademoveqrfile {start=1} {end=30} {format=moveqr_%03d.jpg}';

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
        $this->description .= "php artisan command:makemoveqrfile 1 30";
        $this->description .= "php artisan command:makemoveqrfile start=1 end=30";
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
        $start = $this->argument('start');
        $end = $this->argument('end');

        $fileNameFormat = $this->argument('format');

        $fileNameList = [];
        for ($i = $start; $i <= $end; $i++) {
            $fileNameList[] = sprintf($fileNameFormat, $i);
        }

        $fileListStr = implode(',', $fileNameList);

        echo "$fileListStr\n";
    }
}
