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
        Schema::create('ci_entities_price_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('price_template_id')->nullable();
            $table->morphs('entity');
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
        Schema::dropIfExists('ci_entities_price_templates');
    }
}
