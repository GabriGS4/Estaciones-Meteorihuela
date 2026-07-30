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
        Schema::table('sponsors', function (Blueprint $table) {
            $table->unsignedInteger('extra_story_views')->default(0)->after('order');
            $table->unsignedInteger('extra_link_clicks')->default(0)->after('extra_story_views');
            $table->unsignedInteger('extra_unique_devices')->default(0)->after('extra_link_clicks');
        });

        Schema::table('sponsor_stories', function (Blueprint $table) {
            $table->unsignedInteger('extra_views')->default(0)->after('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsors', function (Blueprint $table) {
            $table->dropColumn(['extra_story_views', 'extra_link_clicks', 'extra_unique_devices']);
        });

        Schema::table('sponsor_stories', function (Blueprint $table) {
            $table->dropColumn(['extra_views']);
        });
    }
};
