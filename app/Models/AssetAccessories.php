<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAccessories extends Model
{
    use HasFactory;

    static function setAccessories($arr){
        foreach ($arr as $item){
            $acc = AssetAccessories::where('name' , '=' , $item)->first();
            if (!$acc instanceof AssetAccessories){
                $acc = new AssetAccessories();
                $acc->name = $item;
                $acc->save();
            }
        }
    }

    public function assets()
    {
        return $this->belongsToMany(
            Asset::class,
            '_asset__accessories_link',
            'name',
            'num');
    }

}
