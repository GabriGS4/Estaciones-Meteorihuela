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
        Schema::create('current_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->dateTime('obs_time_utc');
            $table->dateTime('obs_time_local');
            $table->string('software_type')->nullable();
            $table->decimal('solar_radiation', 8, 2)->nullable();
            $table->decimal('uv', 4, 2)->nullable();
            $table->smallInteger('winddir')->nullable();
            $table->tinyInteger('humidity')->nullable();
            $table->decimal('temp', 5, 2)->nullable();
            $table->decimal('heat_index', 5, 2)->nullable();
            $table->decimal('dewpt', 5, 2)->nullable();
            $table->decimal('wind_chill', 5, 2)->nullable();
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->decimal('wind_gust', 5, 2)->nullable();
            $table->decimal('pressure', 6, 2)->nullable();
            $table->decimal('precip_rate', 6, 2)->nullable();
            $table->decimal('precip_total', 6, 2)->nullable();
            $table->smallInteger('qc_status')->nullable();
            $table->timestamps();

            // 🔑 Un registro único por estación
            $table->unique('station_id', 'current_station_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_observations');
    }
};
