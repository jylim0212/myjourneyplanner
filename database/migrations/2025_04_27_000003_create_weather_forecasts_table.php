<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('weather_forecasts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journey_id');
            $table->string('location');
            $table->date('forecast_date');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('temperature')->nullable();
            $table->integer('humidity')->nullable();
            $table->float('wind_speed')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->foreign('journey_id')->references('id')->on('journeys')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('weather_forecasts');
    }
};
