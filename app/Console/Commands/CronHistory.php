<?php

namespace App\Console\Commands;

use App\Http\Controllers\CronController;
use App\Models\Asset;
use App\Models\AssetAccessories;
use App\Models\Minlog;
use Illuminate\Console\Command;

class CronHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:history';

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
        $c = new CronController();
        $hello = 0 ;
        while (true){
     //   $c->syncHistory();

        if( $hello == 0){
            $nbrDays = 365;
            $hello = 1;
        }else{
            $nbrDays = 5;
        }

            set_time_limit(0);

            $names = AssetAccessories::get();
            $t = [ 'Female'];
            $names = AssetAccessories::whereIn('name' , $t)->get();

            foreach ( $names as $name) {
                $name = $name->name;
                $dateinit = date('Y-m-d', time());
                $dateinit = date('Y-m-d', strtotime($dateinit . ' +' . 1 . ' day'));
                $date = $dateinit;
                for ($i = 0; $i < $nbrDays; $i++) {
                    $forSell = 0;
                    $somme = 0;

                    $nbr = ($i == 0) ? 0 : 1;
                    $date = date('Y-m-d H:i:s', strtotime($date . ' -' . $nbr . ' day'));
                    $datekey = date('Y-m-d', strtotime($date . ' -' . $nbr . ' day'));
                    $min = null;
                    $assets = Asset::whereHas('accessoires', function ($q) use ($name) {
                        $q->where('asset_accessories.name', '=', $name);
                    })->get();

                    foreach ($assets as $asset) {
                        $as = $asset->dateCondition($date)->get();
                        $asset->price = -1;
                        foreach ($as as $s) {
                            if ($s->type == 'Offered') {
                                $asset->price = $s->eth;
                                $forSell++;
                                $somme = $somme + $asset->price;
                                 break;
                            } else {
                                break;
                            }
                        }
                    }
                    echo $somme / $forSell ;
                    foreach ($assets as $key => $asset) {
                        if ($asset->price == -1) {
                            $assets->forget($key);
                        }
                    }
                    $min = null;
                    foreach ($assets as $asset) {
                        if ($min == null) {
                            $min = $asset->price;
                        } else {
                            if ($min > $asset->price) {
                                $min = $asset->price;
                            }
                        }
                    }
                    if ($min != null) {

                    }else{
                        $min = -1;
                    }
                        echo $datekey . ' -> ' .$min;
                        $log = Minlog::where('date' , '=' , $datekey)->where('accessorie' , '=' ,$name)->first();
                        if( $log instanceof  Minlog){

                            echo $log->id . ' updated';
                            $log->value = $min;
                            $log->forsell = $forSell;
                            $log->avg = number_format((float)($somme / $forSell), 2, '.', '');

                            $log->save();
                        }else{
                            $log = new Minlog();
                            $log->date = $datekey;
                            $log->accessorie = $name;
                            $log->value = $min;
                            $log->forsell = $forSell;
                            $log->avg = number_format((float)($somme / $forSell), 2, '.', '');
                            $log->save();
                        }
                }
            }


            sleep(1600);
        }
        return Command::SUCCESS;
    }
}
