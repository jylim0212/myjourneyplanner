<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('weather_api_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_key')->nullable();
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('weather_api_settings');
    }
};
