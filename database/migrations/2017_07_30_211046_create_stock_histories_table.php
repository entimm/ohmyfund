<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('symbol');

            $table->double('open', 10, 4);
            $table->double('high', 10, 4);
            $table->double('low', 10, 4);
            $table->double('close', 10, 4);

            $table->bigInteger('volume');
            $table->bigInteger('lot_volume');

            $table->double('percent', 10, 4)->nullable();
            $table->double('turnrate', 10, 4)->nullable();

            $table->double('ma5', 10, 4)->nullable();
            $table->double('ma10', 10, 4)->nullable();
            $table->double('ma20', 10, 4)->nullable();
            $table->double('ma30', 10, 4)->nullable();

            $table->double('chg', 10, 4)->nullable();
            $table->double('dif', 10, 4)->nullable();
            $table->double('dea', 10, 4)->nullable();
            $table->double('macd', 10, 4)->nullable();

            $table->tinyInteger('type');
            $table->date('date');
            $table->timestamps();

            $table->unique(['symbol', 'date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_histories');
    }
}
