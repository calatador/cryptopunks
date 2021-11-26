<?php

namespace App\Http\Controllers;

use App\Models\AssetHistory;
use App\Models\Options;
use Illuminate\Http\Request;
use DOMDocument;
use App\Models\AssetAccessories;
use App\Models\Asset;
use DOMXPath;

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
            $context = stream_context_create(
                array(
                    "http" => array(
                        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                    )
                )
            );
            echo '<pre>';
             $data = $this->cloudFlareBypass($url);

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


    function cloudFlareBypass($url){

        $useragent = "Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/W.X.Y.Z‡ Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";

        $ct = curl_init();

        curl_setopt_array($ct, Array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array("X-Requested-With: XMLHttpRequest"),
            CURLOPT_REFERER => $url,
            CURLOPT_USERAGENT =>  $useragent,
            CURLOPT_HEADER => false,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'schn=csrf'
        ));

        $html = curl_exec($ct);

        $dochtml = new DOMDocument();
        @$dochtml->loadHTML($html);
        $xpath = new DOMXpath($dochtml);

        // Auth
        if(isset($xpath->query("//input[@name='r']/@value")->item(0)->textContent)){
            $action = $url . $xpath->query("//form/@action")->item(0)->textContent;
            $r = $xpath->query("//input[@name='r']/@value")->item(0)->textContent;
            $jschl_vc = $xpath->query("//input[@name='jschl_vc']/@value")->item(0)->textContent;
            $pass = $xpath->query("//input[@name='pass']/@value")->item(0)->textContent;

            // Generate curl post data
            $post_data = array(
                'r' => $r,
                'jschl_vc' => $jschl_vc,
                'pass' => $pass,
                'jschl_answer' => ''
            );

            curl_close($ct); // Close curl

            return $html;

            $ct = curl_init();

            // Post cloudflare auth parameters
            curl_setopt_array($ct, Array(
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json, text/javascript, */*; q=0.01',
                    'Accept-Language: ro-RO,ro;q=0.8,en-US;q=0.6,en-GB;q=0.4,en;q=0.2',
                    'Referer: '. $url,
                    'Origin: '. $url,
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With: XMLHttpRequest'
                ),
                CURLOPT_URL => $action,
                CURLOPT_REFERER => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => $useragent,
                CURLOPT_POSTFIELDS => http_build_query($post_data)

            ));

            $html_reponse = curl_exec($ct);

            curl_close($ct); // Close curl

        }else{

            // Already auth
            return $html;

        }

    }



}
