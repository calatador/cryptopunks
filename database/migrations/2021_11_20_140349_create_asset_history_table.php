<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_history', function (Blueprint $table) {
            $table->id();
            $table->integer('asset_id');
            $table->string('type')->nullable();
            $table->string('From')->nullable();
            $table->string('to')->nullable();
            $table->double('eth');
            $table->double('usd');
            $table->dateTime('txn');
            $table->string('track');
            $table->string('trackurl')->nullable();
            $table->integer('sync')->default('0');
            $table->timestamps();
       //    $table->foreign('asset_id')->references('id')->on('assets');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_history');
    }
}
