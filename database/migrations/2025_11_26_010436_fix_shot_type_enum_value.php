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
        // Update the shot_type enum to include new values
        Schema::table('brand_presets', function (Blueprint $table) {
            $table->enum('shot_type', [
                'lifestyle', 
                'hero', 
                'flat_lay', 
                'context', 
                'white_background',
                'portrait', 
                'landscape',
                'instagram_post',
                'square',
                'instagram_story'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the shot_type enum to its original values
        Schema::table('brand_presets', function (Blueprint $table) {
            $table->enum('shot_type', [
                'lifestyle', 
                'hero', 
                'flat_lay', 
                'context', 
                'white_background'
            ])->change();
        });
    }
};
