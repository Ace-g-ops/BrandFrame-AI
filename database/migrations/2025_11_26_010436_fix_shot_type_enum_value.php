<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change column type to varchar first
        DB::statement('ALTER TABLE brand_presets ALTER COLUMN shot_type TYPE VARCHAR(255)');

        // Drop old check constraint if exists
        DB::statement('ALTER TABLE brand_presets DROP CONSTRAINT IF EXISTS brand_presets_shot_type_check');

        // Add new check constraint
        DB::statement("
            ALTER TABLE brand_presets
            ADD CONSTRAINT brand_presets_shot_type_check
            CHECK (shot_type IN (
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
            ))
        ");
    }

    public function down(): void
    {
        // Drop the check constraint
        DB::statement('ALTER TABLE brand_presets DROP CONSTRAINT IF EXISTS brand_presets_shot_type_check');

        DB::statement('ALTER TABLE brand_presets ALTER COLUMN shot_type TYPE VARCHAR(255)');
    }
};
