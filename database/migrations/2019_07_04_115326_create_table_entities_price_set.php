<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEntitiesPriceSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sc_space_price_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('price_template_id')->nullable();
            $table->integer('space_id')->unsigned()->nullable();
            $table->timestamp('applicable_from')->nullable();
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
        Schema::dropIfExists('sc_entities_price_templates');
    }
}
