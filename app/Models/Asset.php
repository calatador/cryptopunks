<?php

namespace App\Models;

use App\Models\AssetAccessories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DOMDocument;


class Asset extends Model
{
    use HasFactory;
    public $price;

    public function accessoires()
    {
        return $this->belongsToMany(
            AssetAccessories::class,
            '_asset__accessories_link',
            'num',
            'name');
    }
    public function history()
    {
        return $this->hasMany(AssetHistory::class, 'asset_id', 'num')
            ->orderBy('id' , 'DESC');
    }
    public function dateCondition($date) {
        return $this->history()
           ->where('txn','<=', $date)
            //Bid Withdrawn
            //Bid
          ->whereIn('type' , [
                "Sold",
                "Offered",
                "Transfer",
                "Claimed",
                "Offer Withdrawn",
                "(Unwrap)",
                "(Wrap)"
            ])
           ->orderBy('id' ,'DESC');
    }

    public function last_price()
    {
        return $this->hasOne(AssetHistory::class , 'asset_id' , 'num')
            ->whereIn('type' , ['Sold' , 'Offered' , 'Transfer' , 'Offer Withdrawn' , 'Claimed'
            ]);
            //->latest( 'txn');
      //  ->orderBy('id')->limit(1)->latest( 'txn');
    }

    static function assetFirstInit($data , $id , $t){
        $asset = Asset::where('num' , '=' , $id)->first();
        if( $asset instanceof Asset){

        }else{
            $asset = new Asset();
            $asset->num = $id;
            $asset->name = 'punk_'. $id;
            $asset->image = $data->image;
            $asset->save();
            foreach ( $data->accessories as $acc){
                $accessoire = AssetAccessories::where('name' , '=' , $acc)->first();
                $asset->accessoires()->attach($accessoire->id);
            }
            $accessoire = AssetAccessories::where('name' , '=' , $t)->first();

            $asset->accessoires()->attach($accessoire->id);
        }
    }

    static function assetSecInit($data , $id){
        $asset = Asset::where('num' , '=' , $id)->first();
        $historys = $asset->getLiveHistory();



        foreach (array_reverse($historys) as $item){
                $his = AssetHistory::where('track' , '=' , $item[6] )->where('asset_id' , '=' , $id)->first();
                if( !$his instanceof AssetHistory){
                    echo '-';
                    if( isset($item[3]['eth'])){
                        $history = new AssetHistory();
                        $history->asset_id = $asset->num ;
                        $history->type  = $item[0] ;
                        $history->From = $item[1] ;
                        $history->to = $item[2] ;
                        $history->eth = $item[3]['eth'];
                        $history->usd = $item[3]['usd'];
                        $date_input = date('Y-m-d' , strtotime($item[4]) );
                        $history->txn = $date_input;
                        // $history->txn = $item[5];
                        $history->track = $item[6];
                        $history->trackurl = $item[7];

                        $history->save();
                    }else{
                        $history = new AssetHistory();
                        $history->asset_id = $asset->num ;
                        $history->type  = $item[0] ;
                        $history->From = $item[1] ;
                        $history->to = $item[2] ;
                        $history->eth = -1;
                        $history->usd = -1;
                        $date_input = date('Y-m-d' , strtotime($item[4]) );
                        $history->txn = $date_input;
                        //  $history->txn = $item[5];
                        $history->track = $item[6];
                        $history->trackurl = $item[7];

                        $history->save();
                    }
                }
            }
    }


    public function getLiveHistory(){

        do {
            $curl_handle = curl_init();
            curl_setopt($curl_handle, CURLOPT_URL, 'https://www.larvalabs.com/cryptopunks/details/' . $this->num);
            curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
            $data = curl_exec($curl_handle);
            curl_close($curl_handle);

            $a = str_contains($data, 'Too Many Requests');
            if ($a) {
                echo $data;
                echo 'oups';
                sleep(5);
            }

        } while ($a);


        $first_step = explode( '<div id="punkHistory">' , $data );
        $second_step = explode("</div>" , $first_step[1] );
        $first_step = explode( '<div class="table-responsive">' , $data );
        $second_step = explode("</div>" , $first_step[1] );
        $table_html = $second_step[0];
        $DOM = new DOMDocument();
        $DOM->loadHTML($table_html);
        $Header = $DOM->getElementsByTagName('th');
        $Detail = $DOM->getElementsByTagName('td');
         foreach($Header as $NodeHeader)
        {
            $aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
        }
        $i = 0;
        $j = 0;
        $t = 0;
        foreach($Detail as $sNodeDetail)
        {
            $aDataTableDetailHTML[$j][] = trim($sNodeDetail->textContent);
            if( $t == 4){
                $href = $sNodeDetail->firstChild->getAttribute('href');
                $aDataTableDetailHTML[$j][] =  str_replace("https://etherscan.io/tx/a", "", "$href");
                $aDataTableDetailHTML[$j][] =  str_replace("https://etherscan.io/tx/", "", "$href");
                $aDataTableDetailHTML[$j][] = $href;

                $t = 0;
            }else{
                $t = $t + 1;
            }
            $i = $i + 1;
            $j = $i % count($aDataTableHeaderHTML) == 0 ? $j + 1 : $j;
        }
        foreach ($aDataTableDetailHTML as $key => $item){

             if( $item[3] != ''){
                $prices = explode(" ", $item[3]);
                $f1 = 1;
                 $prices[0] = substr($prices[0],0,-2);
                 $prices[1] = substr($prices[1],0,-1);
                 $prices[1] = substr($prices[1],2,strlen($prices[1]));

                 $pos = strpos($prices[0], 'K');
                if ($pos !== false) {
                     $prices[0] = str_replace( 'K' , '' , $prices[0]);
                    $f1 = 1000;
                 }
                $pos = strpos($prices[0], 'M');
                if ($pos !== false) {
                    $prices[0] = str_replace( 'M' , '' , $prices[0]);
                    $f1 = 1000000;
                 }
                 $pos = strpos($prices[0], 'Y');
                 if ($pos !== false) {
                     $prices[0] = str_replace( 'Y' , '' , $prices[0]);
                     $f1 = 1000000000;
                 }
                 $pos = strpos($prices[0], 'B');
                 if ($pos !== false) {
                     $prices[0] = str_replace( 'B' , '' , $prices[0]);
                     $f1 = 1000000000;
                 }

                 $pos = strpos($prices[0], 'T');
                 if ($pos !== false) {
                     $prices[0] = str_replace( 'T' , '' , $prices[0]);
                     $f1 = 1000000000000;
                 }
                 $pos = strpos($prices[0], 'Z');
                 if ($pos !== false) {
                     $prices[0] = str_replace( 'Z' , '' , $prices[0]);
                     $f1 = 1000000000000;
                 }
                 $pos = strpos($prices[0], 'E');
                 if ($pos !== false) {
                     $prices[0] = str_replace( 'E' , '' , $prices[0]);
                     $f1 = 1000000000000;
                 }
                 $pos = strpos($prices[0], 'P');
                 if ($pos !== false) {
                     $prices[0] = str_replace( 'P' , '' , $prices[0]);
                     $f1 = 1000000000000;
                 }

                 $f2 = 1;
                $pos = strpos($prices[1], 'K');
                if ($pos !== false) {
                    $prices[1] = str_replace( 'K' , '' , $prices[1]);
                    $f2 = 1000;
                 }
                $pos = strpos($prices[1], 'M');
                if ($pos !== false) {
                    $prices[1] = str_replace( 'M' , '' , $prices[1]);
                    $f2 = 1000000;
                 }
                 $pos = strpos($prices[1], 'Y');
                 if ($pos !== false) {
                     $prices[1] = str_replace( 'Y' , '' , $prices[1]);
                     $f2 = 1000000000;
                 }
                 $pos = strpos($prices[1], 'B');
                 if ($pos !== false) {
                     $prices[1] = str_replace( 'B' , '' , $prices[1]);
                     $f2 = 1000000000;
                 }
                 $pos = strpos($prices[1], 'T');
                 if ($pos !== false) {
                     $prices[1] = str_replace( 'T' , '' , $prices[1]);
                     $f2 = 1000000000000;
                 }
                 $pos = strpos($prices[1], 'Z');
                 if ($pos !== false) {
                     $prices[1] = str_replace( 'Z' , '' , $prices[1]);
                     $f2 = 1000000000000000;
                 }
                 $pos = strpos($prices[1], 'E');
                 if ($pos !== false) {
                     $prices[1] = str_replace( 'E' , '' , $prices[1]);
                     $f2 = 1000000000000000;
                 }
                 $pos = strpos($prices[1], 'P');
                 if ($pos !== false) {
                     $prices[1] = str_replace( 'P' , '' , $prices[1]);
                     $f2 = 1000000000000000;
                 }

                 $pos = strpos($prices[0], '<');
                 if ($pos !== false) {
                     $prices[0] = substr($prices[0], 1);;

                 }
                 $pos = strpos($prices[1], '<');
                 if ($pos !== false) {
                     $prices[1] = substr($prices[1], 1);;
                 }
                 $peth = str_replace( ',' , '' , $prices[0] );
                 echo '(' . $peth . "-" .  $prices[1] . ')';
                 $peth = $peth * $f1;
                 $peth = number_format($peth  , 2 , '.' ,'');
                 $pusd = str_replace( ',' , '' , $prices[1] );
                 $pusd = number_format($pusd * $f2 , 2 , '' ,'');

                $prices[0] = str_replace( ',' , '' , $prices[0]);
                $prices[1] = str_replace( ',' , '' , $prices[1]);
                $price = [
                    'eth' => $peth   ,
                    'usd' => $pusd
                ];
                $aDataTableDetailHTML[$key][3] = $price;
            }
        }
        return $aDataTableDetailHTML;
    }
}
