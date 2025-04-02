<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->timestamp('generated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropColumn('generated_at');
        });
    }
};
