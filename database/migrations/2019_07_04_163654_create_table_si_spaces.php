<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSiSpaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('si_spaces', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('base_price_unit')->nullable();
            $table->float('base_price_amount')->nullable();
            $table->string('base_price_currency')->nullable();
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
        Schema::dropIfExists('si_spaces');
    }
}
