<?php

namespace App\Http\Controllers;

use App\Models\AssetHistory;
use App\Models\Minlog;
use Illuminate\Http\Request;
use DOMDocument;
use App\Models\AssetAccessories;
use App\Models\Asset;

class AssetController extends Controller
{

    public function welcome (){
        $aesst = Asset::get();
        $aesst = count($aesst);
        $history = AssetHistory::count();
        $historySync = AssetHistory::where( 'sync' , '=' , '1')->count();

        return view('welcome' , ['aesst' => $aesst , 'history' => $history , 'historySync' => $historySync ]);
    }

    public function listing(Request $request){
        define( 'WP_MEMORY_LIMIT', '2560M' );
        set_time_limit(0);
        $pageTitle = "cryptopunks";
        $selectedDate = $request->input('date');
        $selectedAssets = $request->input('assets');
        $on = true;
        if( $selectedAssets == null){
            $on = false;
            $selectedAssets = [];
        }
        if( $selectedDate != null) {
            $date = date('Y-m-d', strtotime($selectedDate));
        }else{
            $date = date('Y-m-d', time() );
        }
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $assets = Asset::whereHas('accessoires', function($q) use($selectedAssets) {
            $q->whereIn('asset_accessories.name', $selectedAssets);
        })->get();

        $options = AssetAccessories::all();
        foreach ($assets as $asset ){
             $as = $asset->dateCondition($date)->get();
             $asset->price = -1 ;
             foreach ($as as $s ) {
                 if ( ($s->type == 'Bid') or ($s->type == 'Bid *')){

                 } elseif ( ($s->type == 'Bid Withdrawn') ) {

                 } elseif ( $s->type == 'Offered') {
                     $asset->price = $s->eth;
                     break;
                 } elseif ( $s->type == 'Sold') {
                      break;
                 } else{
                   //  var_dump($s->type);die();
                   //  $asset->price = -1 ;
                    break;
                 }
             }
          }
        foreach ($assets as $key => $asset){
             if( $asset->price == -1){
                 $assets->forget($key);
             }
         }
        $assets = $assets->sortBy('price');

        return view('listing' , ['pageTitle' => $pageTitle , 'asssets' => $assets , 'options' => $options ,
            'selectedDate' => $date , 'selectedAssets' => $selectedAssets , 'on' => $on
        ]);

    }

    public function month2(Request $request){
      //  define( 'WP_MEMORY_LIMIT', '2560M' );
        $pageTitle = "cryptopunks";
        $dateinit = date('Y-m-d', time() );
        $selectedAssets = $request->input('assets');
        if( $selectedAssets == null){
            $selectedAssets = ['Beanie'];
        }else{
            var_dump($selectedAssets);
         }


        $assets = Asset::whereHas('accessoires', function($q) use($selectedAssets) {
            $q->whereIn('asset_accessories.name', $selectedAssets);
        })->get();

     //   var_dump(count($assets));
     //   die();


        $assetsArr = [];
        foreach ($assets as $key => $asset){
            $date = $dateinit;
            $priceArr = [];
            for( $i = 0 ; $i < 31 ; $i++){
             $nbr = ( $i == 0) ? 0 : 1 ;
             $date = date('Y-m-d H:i:s', strtotime($date . ' -'.$nbr.' day'));
             $as = $asset->dateCondition($date)->get();
             $priceArr[$date] = ['price' => -1 , 'punk' => $asset , 'date' => $date];
             foreach ($as as $s ) {
                 if ( ($s->type == 'Bid') or ($s->type == 'Bid *')){

                 } elseif ( ($s->type == 'Bid Withdrawn') ) {

                 } elseif ( $s->type == 'Offered') {
                     $priceArr[$date]  = ['price' => $s->eth , 'punk' => $asset , 'date' => $date] ;
                     break;
                 } elseif ( $s->type == 'Sold') {
                     break;
                 } else{
                     break;
                 }
             }
         }
            $assetsArr [$asset->num] = $priceArr;
        }

        $minArr = [];
        $date = $dateinit;
        for( $i = 0 ; $i < 31 ; $i++){
            $nbr = ( $i == 0) ? 0 : 1 ;
            $date = date('Y-m-d H:i:s', strtotime($date . ' -'.$nbr.' day'));
            $minArr[$date]  =  null;
            foreach ($assetsArr as $arr){
                if ( $arr[$date]['price'] != -1 ){
                    if( $minArr[$date] == null){
                        $minArr[$date] = $arr[$date];
                    }else{
                        $minArr[$date] = (( $minArr[$date]['price'] > $arr[$date]['price'] ) ? $arr[$date] : $minArr[$date] ) ;
                    }
                }

            }

        }
        $options = AssetAccessories::all();
        return view('month' , ['pageTitle' => $pageTitle , 'minArr' => $minArr , 'options' => $options]);
    }

    public function month(Request $request){
        $pageTitle = "Orange Side";
        $dateinit = date('Y-m-d', time() );
        $selectedAssets = $request->input('assets');
        $nbrDays = 31;

        if( $selectedAssets == null){
            $selectedAssets = ['Beanie'];
        }else{
            var_dump($selectedAssets);
        }

        $result = [];
        $names = $selectedAssets;
        foreach ( $names as $name) {
            $dateinit = date('Y-m-d', time());
            $dateinit = date('Y-m-d', strtotime($dateinit . ' -' . 3 . ' day'));
            $date = $dateinit;
            for ($i = 0; $i < $nbrDays; $i++) {
                $nbr = ($i == 0) ? 0 : 1;
                $date = date('Y-m-d H:i:s', strtotime($date . ' -' . $nbr . ' day'));
                $datekey = date('Y-m-d', strtotime($date . ' -' . $nbr . ' day'));
                $min = null;
                    $assets = Asset::whereHas('accessoires', function ($q) use ($name) {
                        $q->where('asset_accessories.name', '=', $name);
                    })
                        ->get();



                foreach ($assets as $asset ){
                    $as = $asset->dateCondition($date)->get();
                    $asset->price = -1 ;

                    echo $asset->num  . ' -> ' .  $asset->price ." -> " . count($as).'<br>';
                    echo '-------------------------<br>';
                    foreach ($as as $s ) {
                        echo $s->id . ' - ' . $s->txn  . ' ' .$s->type . ' - ' . $s->eth . '<br>';
                    }
                    foreach ($as as $s ) {
                       if ( $s->type == 'Offered') {
                            $asset->price = $s->eth;
                            break;
                        } elseif ( $s->type == 'Sold') {
                            break;
                        } else{
                            break;
                        }
                    }


                }




                foreach ($assets as $key => $asset){
                    if( $asset->price == -1){
                        $assets->forget($key);
                    }
                }

                    $min = null;
                    $ass = null;
                    foreach ($assets as $asset) {
                  //      echo '<pre>';
                 //       echo var_dump($asset->last_price->eth);
                        if( $min == null){
                            $min = $asset->price;
                            $ass = $asset;
                        }else{
                            if( $min > $asset->price){
                                $min = $asset->price;
                                $ass = $asset;
                            }
                        }
                    }

                    if( $min != null) {
                        $result[$datekey] = ['min' => ( $min == null) ? -1 : $min , 'ass' => $ass ];
                    }
            }
        }
        $options = AssetAccessories::all();

        return view('month' , ['pageTitle' => $pageTitle , 'result' => $result , 'options' => $options]);
    }

    public function cryptopunks(){
        $assets = Asset::limit(250)->simplePaginate(15);
        return view('cryptopunks' , ['assets' => $assets ]);
    }

    public function cryptopunk(Request $request){
        $id = $request->input('id');
        $asset = Asset::where('num' , '=' , $id)->first();

        if( $asset instanceof Asset){
            return view('cryptopunk' , ['asset' => $asset ]);
        }
        $assets = Asset::limit(250)->simplePaginate(15);
        return view('cryptopunks' , ['assets' => $assets ]);
    }

    public function test(){
        $nbrDays = 10;
        $name = 'Beanie';
        $names = array("Beanie", "Choker", "Pilot Helmet", "Tiara", "Orange Side", "Buck Teeth", "Welding Goggles", "Pigtails", "Pink With Hat", "Top Hat", "Spots", "Rosy Cheeks", "Blonde Short", "Wild White Hair", "Cowboy Hat", "Wild Blonde", "Straight Hair Blonde", "Big Beard", "Red Mohawk", "Half Shaved", "Blonde Bob", "Vampire Hair", "Clown Hair Green", "Straight Hair Dark", "Straight Hair", "Silver Chain", "Dark Hair", "Purple Hair", "Gold Chain", "Medical Mask", "Tassle Hat", "Fedora", "Police Cap", "Clown Nose", "Smile", "Cap Forward", "Hoodie", "Front Beard Dark", "Frown", "Purple Eye Shadow", "Handlebars", "Blue Eye Shadow", "Green Eye Shadow", "Vape", "Front Beard", "Chinstrap", "3D Glasses", "Luxurious Beard", "Mustache", "Normal Beard Black", "Normal Beard", "Eye Mask", "Goat", "Do-rag", "Shaved Head", "Muttonchops", "Peak Spike", "Pipe", "VR", "Cap", "Small Shades", "Clown Eyes Green", "Clown Eyes Blue", "Headband", "Crazy Hair", "Knitted Cap", "Mohawk Dark", "Mohawk", "Mohawk Thin", "Frumpy Hair", "Wild Hair", "Messy Hair", "Eye Patch", "Stringy Hair", "Bandana", "Classic Shades", "Shadow Beard", "Regular Shades", "Horned Rim Glasses", "Big Shades", "Nerd Glasses", "Black Lipstick", "Mole", "Purple Lipstick", "Hot Lipstick", "Cigarette", "Earring");
        $time_start = microtime(true);
        $result = '';
        set_time_limit(0);

        $names = AssetAccessories::get();
        foreach ( $names as $name) {
            $name = $name->name;
            $dateinit = date('Y-m-d', time());
            $dateinit = date('Y-m-d', strtotime($dateinit . ' +' . 1 . ' day'));

            $date = $dateinit;

            for ($i = 0; $i < $nbrDays; $i++) {
                $nbr = ($i == 0) ? 0 : 1;
                $date = date('Y-m-d H:i:s', strtotime($date . ' -' . $nbr . ' day'));
                $datekey = date('Y-m-d', strtotime($date . ' -' . $nbr . ' day'));
                $min = null;
                $minlogtest = Minlog::where('accessorie' , '=', $name)
                    ->where('date' , '=' , $datekey )->first();
                    $assets = Asset::whereHas('accessoires', function ($q) use ($name) {
                        $q->where('asset_accessories.name', '=', $name);
                    })->get();
                    foreach ($assets as $asset) {
                        $as = $asset->dateCondition($date)->get();
                        $asset->price = -1;
                        foreach ($as as $s) {
                            if (($s->type == 'Bid') or ($s->type == 'Bid *')) {

                            } elseif (($s->type == 'Bid Withdrawn')) {

                            } elseif ($s->type == 'Offered') {
                                $asset->price = $s->eth;
                                break;
                            } elseif ($s->type == 'Sold') {
                                break;
                            } else {
                                break;
                            }
                        }
                    }
                    foreach ($assets as $key => $asset) {
                        if ($asset->price == -1) {
                            $assets->forget($key);
                        }
                    }
                    $min = null;
                    $ass = null;
                    foreach ($assets as $asset) {
                        if ($min == null) {
                            $min = $asset->price;
                            $ass = $asset;
                        } else {
                            if ($min > $asset->price) {
                                $min = $asset->price;
                                $ass = $asset;
                            }
                        }
                    }
                    if ($min != null) {
                        $log = Minlog::where('date' , '=' , $datekey)->where('accessorie' , '=' ,$name)->first();
                        if( $log instanceof  Minlog){
                            $log->value = $min;
                            $log->save();
                        }else{
                            $log = new Minlog();
                            $log->date = $datekey;
                            $log->accessorie = $name;
                            $log->value = $min;
                            $log->save();
                        }
                    }

            }
        }
    }

    public function history(){

        $names = [
            "Beanie",
            "Choker",
            "Pilot Helmet",
            "Tiara",
            "Orange Side",
            "Buck Teeth",
            "Welding Goggles",
            "Pigtails",
            "Pink With Hat",
            "Top Hat",
            "Spots",
            "Rosy Cheeks",
            "Blonde Short",
            "Wild White Hair",
            "Cowboy Hat",
            "Wild Blonde",
            "Straight Hair Blonde",
            "Big Beard",
            "Red Mohawk",
            "Half Shaved",
            "Blonde Bob",
            "Vampire Hair",
            "Clown Hair Green",
            "Straight Hair Dark",
            "Straight Hair",
            "Silver Chain",
            "Dark Hair",
            "Purple Hair",
            "Gold Chain",
            "Medical Mask",
            "Tassle Hat",
            "Fedora",
            "Police Cap",
            "Clown Nose",
            "Smile",
            "Cap Forward",
            "Hoodie",
            "Front Beard Dark",
            "Frown",
            "Purple Eye Shadow",
            "Handlebars",
            "Blue Eye Shadow",
            "Green Eye Shadow",
            "Vape",
            "Front Beard",
            "Chinstrap",
            "3D Glasses",
            "Luxurious Beard",
            "Mustache",
            "Normal Beard Black",
            "Normal Beard",
            "Eye Mask",
            "Goat",
            "Do-rag",
            "Shaved Head",
            "Muttonchops",
            "Peak Spike",
            "Pipe",
            "VR",
            "Cap",
            "Small Shades",
            "Clown Eyes Green",
            "Clown Eyes Blue",
            "Headband",
            "Crazy Hair",
            "Knitted Cap",
            "Mohawk Dark",
            "Mohawk",
            "Mohawk Thin",
            "Frumpy Hair",
            "Wild Hair",
            "Messy Hair",
            "Eye Patch",
            "Stringy Hair",
            "Bandana",
            "Classic Shades",
            "Shadow Beard",
            "Regular Shades",
            "Horned Rim Glasses",
            "Big Shades",
            "Nerd Glasses",
            "Black Lipstick",
            "Mole",
            "Purple Lipstick",
            "Hot Lipstick",
            "Cigarette",
            "Earring"
        ];
        $dataArr = [];


        foreach ($names as $name){
            $dataArr[$name] = [];
        }
        $minLog = Minlog::whereIn('accessorie', $names)->get();
        foreach ($minLog as $log){
            $dataArr[$log->accessorie][$log->date] = $log->value;
        }

        /*
        foreach ( $names as $name) {
            $minLog = Minlog::where('accessorie', '=', $name)
                ->orderBy('date', 'ASC')->get();
            $dataArr[$name] = $minLog;
        }
        */

        return view('history' , [ 'names' => $names, 'minLogs' => $dataArr ]);

    }

    public function theTypes(){

        $names = [
            'Alien',
            'Ape',
            'Zombie',
            'Female',
            'Male'
        ] ;
        $dataArr = [];
        foreach ($names as $name){
            $dataArr[$name] = [];
        }
        $minLog = Minlog::whereIn('accessorie', $names)->get();
        foreach ($minLog as $log){
            $dataArr[$log->accessorie][$log->date] =
                [ 'date' => $log->date, 'value' => $log->value ,
                    'sold' => $log->nbrsold , 'soldavg' => $log->sommesold , 'forsell' => $log->forsell
                    ];
        }

        return view('types' , [ 'names' => $names, 'minLogs' => $dataArr ]);

    }

}
