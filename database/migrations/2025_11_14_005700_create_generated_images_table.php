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
        Schema::create('generated_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_preset_id')->nullable()->constrained()->onDelete('set null');
            $table->string('product_image_path'); // Path to the generated product image
            $table->text('user_intent'); // Description of what the user intended "lifestyle photo of a instagram"
            $table->json('structured_prompt'); //the json from Bria API
            $table->string('angle'); // e.g., top-down, side view, close-up
            $table->string('style')->nullable(); // e.g., modern, rustic, vintage
            $table->string('generated_image_url'); // URL of the generated image from Bria
            $table->string('shot_type'); // e.g., lifestyle, studio, flatlay, context
            $table->string('bria_request_id')->nullable(); // ID returned by Bria API for tracking
            $table->json('metadata')->nullable(); // Additional metadata related to the image generation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_images');
    }
};
