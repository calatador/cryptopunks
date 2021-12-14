<?php

namespace App\Console\Commands;

use App\Http\Controllers\CronController;

use App\Models\Baycassets;
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://ipfs.io/ipfs/Qme57kZ2VuVzcj5sC3tVHFgyyEgBTmAnyTK45YVNxKf6hi');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        $json_data =  curl_exec($ch);
        $json_data = json_decode($json_data);
        ini_set('max_execution_time', 0);
        $datas = null;
        foreach ( $json_data as $key => $json) {
            if ( $key == 'collection') {
                $datas = $json;
            }
        }

        foreach ($datas as $key => $data){
            $in = Baycassets::where('tokenId' , '=' , $key)->firs();
            if( $in instanceof Baycassets){
                $in = new Baycassets();
                $in->tokenId = $data->tokenId;
                $in->image = $data->image;
                $in->imageHash = $data->imageHash;
                $in->save();
            }else{
                $in = new Baycassets();
                $in->tokenId = $data->tokenId;
                $in->image = $data->image;
                $in->imageHash = $data->imageHash;
                $in->save();
            }


        }


        return Command::SUCCESS;
    }
}
