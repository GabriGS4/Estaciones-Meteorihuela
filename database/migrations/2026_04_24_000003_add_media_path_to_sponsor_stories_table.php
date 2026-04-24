<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsor_stories', function (Blueprint $table) {
            $table->string('media_path')->nullable()->after('media_url');
        });
    }

    public function down(): void
    {
        Schema::table('sponsor_stories', function (Blueprint $table) {
            $table->dropColumn('media_path');
        });
    }
};
