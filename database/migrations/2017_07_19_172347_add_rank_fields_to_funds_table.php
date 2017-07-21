<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRankFieldsToFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->integer('unit')->default('0');
            $table->integer('total')->default('0');;
            $table->integer('rate')->default('0');;

            $table->integer('in_1week')->default('0');;
            $table->integer('in_1month')->default('0');;
            $table->integer('in_3month')->default('0');;
            $table->integer('in_6month')->default('0');;
            $table->integer('current_year')->default('0');;
            $table->integer('in_1year')->default('0');;
            $table->integer('in_2year')->default('0');;
            $table->integer('in_3year')->default('0');;
            $table->integer('in_5year')->default('0');;
            $table->integer('since_born')->default('0');;

            $table->date('born_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('funds', function (Blueprint $table) {
            $table->dropColumn('unit');
            $table->dropColumn('total');
            $table->dropColumn('rate');

            $table->dropColumn('in_1week');
            $table->dropColumn('in_1month');
            $table->dropColumn('in_3month');
            $table->dropColumn('in_6month');
            $table->dropColumn('current_year');
            $table->dropColumn('in_1year');
            $table->dropColumn('in_2year');
            $table->dropColumn('in_3year');
            $table->dropColumn('in_5year');
            $table->dropColumn('since_born');

            $table->dropColumn('born_date');
        });
    }
}
