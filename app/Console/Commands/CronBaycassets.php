<?php

namespace App\Console\Commands;

use App\Http\Controllers\CronController;

use Illuminate\Console\Command;

class CronBaycassets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:baycassets';

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
        echo 'baycassets';
        return Command::SUCCESS;
    }
}
