<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablePriceVariations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ci_price_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('price_template_id')->nullable();
            $table->integer('month_of_year')->nullable();
            $table->integer('day_of_week')->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->float('increment_value')->nullable();
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
        Schema::dropIfExists('ci_price_variations');
    }
}
