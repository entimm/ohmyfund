<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdateWayToStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function (Blueprint $table) {
            /*
             * 目前有2种更新次表的方式
             * 1. 一种是通过拉取单个基金历史记录的方式，此方式需要爬大量链接
             * 2. 一种是通过拉取基金排名的方式，只需要拉取一次便可更新当天所有记录，但是可呢会有遗漏,且基金也不全
             */
            $table->tinyInteger('update_way')->default('1')->comment('更新方法');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->dropColumn('update_way');
        });
    }
}
