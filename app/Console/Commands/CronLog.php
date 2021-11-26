<?php

namespace App\Console\Commands;

use App\Http\Controllers\AssetController;
use App\Models\AssetAccessories;
use Illuminate\Console\Command;

class CronLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:log';

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
     * @return int
     */
    public function handle()
    {
        $a = new AssetController();
        $a->test();
        return Command::SUCCESS;
    }
}
