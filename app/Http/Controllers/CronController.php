<?php

namespace App\Http\Controllers;

use App\Models\AssetHistory;
use App\Models\Options;
use Illuminate\Http\Request;
use DOMDocument;
use App\Models\AssetAccessories;
use App\Models\Asset;
use DOMXPath;
use CloudflareBypass\CFCurlImpl;
use CloudflareBypass\Model\UAMOptions;


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


        public function syncPrice2(){
            $historys = AssetHistory::where( 'sync' , '=' , 0)
                ->where('type' , '=' , 'Bid Withdrawn')->get();
            foreach ($historys as $h){
                $url =  "https://api.etherscan.io/api?module=account&action=txlistinternal&txhash=".$h->track."&apikey=5U3EZ84PQ1PQZV1SV6VWJ9W514XPXEYA58";

                    $curl_handle = curl_init();
                    curl_setopt($curl_handle, CURLOPT_URL, $url);
                    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
                    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
                    $data = curl_exec($curl_handle);
                    curl_close($curl_handle);
                    echo  $data;
                    $a = str_contains($data, 'timeStamp');
                    if ($a) {

                        $data = json_decode($data);
                        $timeStemp = null;
                        foreach ($data->result as $result ){
                            $timeStemp = $result->timeStamp;
                            break;
                        }
                        if( $timeStemp != null){
                            $date = date('Y-m-d H:i:s', $timeStemp);
                            $h->txn = $date;
                            $h->sync = 1;
                            $h->update();
                        }

                    }




            }
        }



    public function syncPrice()
    {
        $historys = AssetHistory::where('sync', '=', 0)->get();
         foreach ($historys as $h) {
            $url = $h->trackurl;

                      $ch = curl_init($url);

// Want to cache clearance cookies ?
//curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
//curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");

             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($ch, CURLINFO_HEADER_OUT, true);
             curl_setopt($ch, CURLOPT_HTTPHEADER,
                 array(
                     "Upgrade-Insecure-Requests: 1",
                     "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36",
                     "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
                     "Accept-Language: en-US,en;q=0.9"
                 ));

             $cfCurl = new CFCurlImpl();

             $cfOptions = new UAMOptions();
             $cfOptions->setVerbose(true);
// $cfOptions->setDelay(5);

             try {
                 $page = $cfCurl->exec($ch, $cfOptions);

                 // Want to get clearance cookies ?
                 //$cookies = curl_getinfo($ch, CURLINFO_COOKIELIST);

             } catch (ErrorException $ex) {
                 echo "Unknown error -> " . $ex->getMessage();
             }









            var_dump($data);
            die();

            $data = explode("<i class='far fa-clock small mr-1'></i>", $data);
            if (isset($data[1])) {
                $data = explode("</div>", $data[1]);
                $data = explode("(", $data[0]);
                $data = explode(")", $data[1]);
                $data = explode(" +", $data[0]);
                $date = date('Y-m-d H:i:s', strtotime($data[0]));
                $h->txn = $date;
                $h->sync = 1;

                $h->update();
            }
        }
    }





}
