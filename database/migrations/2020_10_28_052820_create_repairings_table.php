<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepairingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repairings', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id')->nullable();
            $table->string('date')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('actual_price_value')->nullable();
            $table->string('price_value')->nullable();
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
        Schema::dropIfExists('repairings');
    }
}
