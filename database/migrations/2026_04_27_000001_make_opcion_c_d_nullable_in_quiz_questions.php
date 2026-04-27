<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('opcion_c')->nullable()->change();
            $table->string('opcion_d')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('opcion_c')->nullable(false)->change();
            $table->string('opcion_d')->nullable(false)->change();
        });
    }
};
