<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImageWithBriaAI;
use Illuminate\Http\Request;
use App\Models\BrandPreset;
use App\Models\GeneratedImage;
use Illuminate\Support\Facades\Http;

class BatchProcessingController extends Controller
{
    public function batchGenerate(Request $request){

        // Validate the request
        $validated = $request->validate([
            'preset_id' => 'required|exists:brand_presets,id',
            'product_images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',  // 
            'product_descriptions' => 'nullable|array'
        ]);

        //verify which user owns the preset
        $preset = BrandPreset::where('id', $validated['preset_id'])
        ->where('user_id', $request->user()->id)
        ->firstOrFail();

        $files = $request->file('product_images');
        $imagePaths = [];
        $description = [];

        //store all images first
        foreach($files as $index => $imageFile){

            $path = $imageFile->store('products', 'public');
            $imagePaths[] = $path;
            $description = $validated['product_description'][$index] ?? 'product';
        }

        // dispatch job to queue
        ProcessImageWithBriaAI::dispatch(

            $request->user()->id,
            $preset->id,
            $imagePaths,
            $description
        );

        return response()->json([

            'message' => 'Batch Generation Started! Processing Batch In Background',
            'total_images' => count($imagePaths),
            'status' => 'processing'
        ], 202); // Accepted: Processing

    }

        // Helper method to build prompt from preset
    private function buildPromptFromPreset($preset, $productDescription)
    {
        // Get shot type config
        $shotConfig = config("shot_types.{$preset->shot_type}");
        
        if (!$shotConfig) {
            return "Professional product photography of {$productDescription}";
        }

        // Build prompt using preset's shot type settings + new product
        return sprintf(
            "Professional %s photography of %s. Shot with %s, %s, %s composition. %s mood and atmosphere.",
            $shotConfig['name'],
            $productDescription,
            $shotConfig['camera_angle'],
            $shotConfig['lighting'],
            $shotConfig['composition'],
            $shotConfig['mood']
        );
    }


    
}
