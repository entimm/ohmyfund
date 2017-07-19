<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 20)->nullable()->comment('基金代码');
            $table->date('date')->nullable()->comment('日期');
            $table->integer('unit')->nullable()->comment('单位净值');
            $table->integer('total')->nullable()->comment('累计净值');
            $table->integer('rate')->nullable()->comment('日增长率');
            $table->string('buy_status')->nullable()->comment('申购状态');
            $table->string('sell_status')->nullable()->comment('赎回状态');
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
        Schema::dropIfExists('statistics');
    }
}
