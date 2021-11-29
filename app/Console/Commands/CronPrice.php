<?php

namespace App\Console\Commands;

use App\Http\Controllers\CronController;
use App\Models\Asset;
use App\Models\AssetAccessories;
use App\Models\Minlog;
use Illuminate\Console\Command;

class CronPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:price';

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
        $a = new CronController();
        $a->syncPrice();

        return Command::SUCCESS;
    }
}
