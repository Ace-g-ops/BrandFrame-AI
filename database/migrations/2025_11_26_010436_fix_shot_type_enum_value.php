<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Change column type to VARCHAR first
        DB::statement('ALTER TABLE brand_presets ALTER COLUMN shot_type TYPE VARCHAR(255)');

        // 2️⃣ Drop any existing check constraint
        DB::statement('ALTER TABLE brand_presets DROP CONSTRAINT IF EXISTS brand_presets_shot_type_check');

        // 3️⃣ Add new CHECK constraint for allowed enum values
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
        // Drop the constraint
        DB::statement('ALTER TABLE brand_presets DROP CONSTRAINT IF EXISTS brand_presets_shot_type_check');

        // Optionally, revert type if needed
        DB::statement('ALTER TABLE brand_presets ALTER COLUMN shot_type TYPE VARCHAR(255)');
    }
};
