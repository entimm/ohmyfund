<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 20)->comment('基金代码');
            $table->date('date')->comment('日期');
            $table->integer('unit')->comment('单位净值');
            $table->integer('total')->comment('累计净值');
            $table->integer('rate')->comment('日增长率');
            $table->tinyInteger('buy_status')->comment('申购状态');
            $table->tinyInteger('sell_status')->comment('赎回状态');
            $table->timestamps();

            $table->unique(['code', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histories');
    }
}
