<?php

namespace App\Http\Controllers;

use App\Models\AssetHistory;
use App\Models\Options;
use Illuminate\Http\Request;
use DOMDocument;
use App\Models\AssetAccessories;
use App\Models\Asset;

class CronController extends Controller
{

    public function syncAssets(){
        set_time_limit(0);
        //initAssets
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/gabrielhicks/cryptoPunksAPI/main/cryptoPunkData.json');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        $json_data =  curl_exec($ch);
        $json_data = json_decode($json_data);
        ini_set('max_execution_time', 0);
            foreach ( $json_data as $key => $json) {
            $id = intval($key);
            AssetAccessories::setAccessories($json->accessories);
            AssetAccessories::setAccessories([$json->type]);
            Asset::assetFirstInit($json, $id , $json->type );
            }
            echo 'done';
        }

        public function syncHistory(){
        $option = Options::where('option' , '=' , 'syncHistorySatus' )->first();
        if ($option instanceof Options){
            $option->values = 'working';
            $option->update();
        }else{
            $option = new Options();
            $option->option = 'syncHistorySatus';
            $option->values = 'working';
            $option->save();
        }

        $optionSatus = Options::where('option' , '=' , 'syncHistoryId')->first();
        if( !$optionSatus instanceof $option){
            $optionSatus = new Options();
            $optionSatus->option = 'syncHistoryId';
            $optionSatus->values = '0';
            $optionSatus->save();
        }

            set_time_limit(0);
            //initAssets
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, 'https://raw.githubusercontent.com/gabrielhicks/cryptoPunksAPI/main/cryptoPunkData.json');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            $json_data =  curl_exec($ch);
            $json_data = json_decode($json_data);
            ini_set('max_execution_time', 0);
                foreach ( $json_data as $key => $json) {
                    $id = intval($key);
                    Asset::assetSecInit($json, $id);
                //    $optionSatus->values = $id;
                //    $optionSatus->save();

                }
                echo 'done';

            $option = Options::where('option' , '=' , 'syncHistorySatus' )->first();
            if ($option instanceof Options){
                $option->values = 'done';
                $option->update();
            }else{
                $option = new Options();
                $option->option = 'done';
                $option->values = 'working';
                $option->save();
            }
    }


        public function syncPrice(){
        $arr = ['Sold' , 'Offered' , 'Transfer' , 'Claimed' , 'Offer Withdrawn' , '(Unwrap)' , '(Wrap)'];
            $historys = AssetHistory::where( 'sync' , '=' , 0)->get();
            $i = 0;
            foreach ($historys as $h){
                $i++;
                $url =  $h->trackurl;
                $context = stream_context_create(
                    array(
                        "http" => array(
                            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                        )
                    )
                );

                do {
                    $agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

                    $curl_handle = curl_init();
                    curl_setopt($curl_handle, CURLOPT_URL, $url);
                    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl_handle, CURLOPT_USERAGENT, $agent);
                    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
                    $data = curl_exec($curl_handle);
                    curl_close($curl_handle);

                    echo $data;
                    die();
                    $a = str_contains($data, 'far fa-clock small mr-1');
                    if (!$a) {
                        echo 'oups';
                        sleep(20);
                    }
                } while (!$a);



                $data = explode( "<i class='far fa-clock small mr-1'></i>" , $data );
                if( isset($data[1] )){
                $data = explode( "</div>" , $data[1] );
                $data = explode( "(" , $data[0] );
                $data = explode( ")" , $data[1] );
                $data = explode( " +" , $data[0] );
                $date = date('Y-m-d H:i:s', strtotime($data[0]));
                $h->txn = $date;
                $h->sync = 1;
                $h->update();
                }
            }
        }




}
