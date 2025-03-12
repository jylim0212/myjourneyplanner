<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJourneyLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('journey_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')
                  ->constrained()
                  ->onDelete('cascade'); // Automatically delete linked locations
            $table->string('location');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('journey_locations');
    }
}
