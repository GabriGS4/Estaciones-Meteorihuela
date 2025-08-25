<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hourly_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->dateTime('obs_time_utc');
            $table->dateTime('obs_time_local');
            $table->decimal('solar_radiation_high', 8, 2)->nullable();
            $table->decimal('uv_high', 4, 2)->nullable();
            $table->smallInteger('winddir_avg')->nullable();
            $table->tinyInteger('humidity_high')->nullable();
            $table->tinyInteger('humidity_low')->nullable();
            $table->tinyInteger('humidity_avg')->nullable();
            $table->decimal('temp_high', 5, 2)->nullable();
            $table->decimal('temp_low', 5, 2)->nullable();
            $table->decimal('temp_avg', 5, 2)->nullable();
            $table->decimal('windspeed_high', 5, 2)->nullable();
            $table->decimal('windspeed_low', 5, 2)->nullable();
            $table->decimal('windspeed_avg', 5, 2)->nullable();
            $table->decimal('windgust_high', 5, 2)->nullable();
            $table->decimal('windgust_low', 5, 2)->nullable();
            $table->decimal('windgust_avg', 5, 2)->nullable();
            $table->decimal('dewpt_high', 5, 2)->nullable();
            $table->decimal('dewpt_low', 5, 2)->nullable();
            $table->decimal('dewpt_avg', 5, 2)->nullable();
            $table->decimal('windchill_high', 5, 2)->nullable();
            $table->decimal('windchill_low', 5, 2)->nullable();
            $table->decimal('windchill_avg', 5, 2)->nullable();
            $table->decimal('heatindex_high', 5, 2)->nullable();
            $table->decimal('heatindex_low', 5, 2)->nullable();
            $table->decimal('heatindex_avg', 5, 2)->nullable();
            $table->decimal('pressure_max', 6, 2)->nullable();
            $table->decimal('pressure_min', 6, 2)->nullable();
            $table->decimal('pressure_trend', 6, 2)->nullable();
            $table->decimal('precip_rate', 6, 2)->nullable();
            $table->decimal('precip_total', 6, 2)->nullable();
            $table->smallInteger('qc_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_observations');
    }
};
