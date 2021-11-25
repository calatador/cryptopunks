<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetAccessoriesLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('_asset__accessories_link', function (Blueprint $table) {
            $table->id();

            $table->integer('num');
  /*
            $table->foreign('num')
                ->references('num')
                ->on('assets')->onDelete('cascade');
*/

            $table->string('name');
            /*
            $table->foreign('name')
                ->references('name')
                ->on('asset_accessories')->onDelete('cascade');
*/
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_asset__accessories_link');
    }
}
