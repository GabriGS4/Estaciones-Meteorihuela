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
        Schema::create('quiz_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_jugador');
            $table->integer('puntos');
            $table->integer('total_preguntas');
            $table->decimal('porcentaje', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_rankings');
    }
};
