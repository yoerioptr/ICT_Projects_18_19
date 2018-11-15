<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelsPerTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotels_per__trip', function (Blueprint $table) {
            $table->increments('hotels_per_trip_id');
            $table->integer('trip_id')->unsigned();
            $table->integer('hotel_id')->unsigned();
            $table->timestamps();

            $table->foreign('trip_id')->references('trip_id')->on('trips');
            $table->foreign('hotel_id')->references('hotel_id')->on('hotels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hotels_per__trips');
    }
}
