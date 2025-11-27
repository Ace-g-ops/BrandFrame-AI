<?php

namespace App\Http\Controllers;

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

        //get presets
        $preset = BrandPreset::where('id', $validated['preset_id'])
        ->where('user_id', $request->user()->id)
        ->firstOrFail();

        $apiKey = env('BRIA_API_KEY');
        $results = [];
        $failed = [];

        //loop through each iamges 
        foreach($validated['product_images'] as $index => $imageFile){

            try{

                //store image
                $productPath = $imageFile->store('products', 'public');
                
                //save the description(optional)
                $description = $validated['product_descriptions'][$index] ?? 'product';

                // Call Bria API
                $newPrompt = $this->buildPromptFromPreset($preset, $description);
                $response = HTTP::withHeaders([
                    'api_token' => $apiKey,
                    'Content_type' => 'application/json'
                ])->post('https://engine.prod.bria-api.com/v2/image/generate', [
                    'prompt' => $newPrompt,
                    'num_results' => 1,
                    'sync' => true
                ]);

                if(!$response->successful()){

                    $failed[] = [
                        'index' => $index,
                        'error' => 'Bria API errror',
                        'details' => $response->json()
                    ];
                    continue;
                }

                $briaData = $response->json();

                //save to database
                $generatedImage = GeneratedImage::create([

                    'user_id' => $request->user()->id,
                    'brand_preset_id' => $preset->id,
                    'product_image_path' => $productPath,
                    'user_intent' => $description,
                    'structured_prompt' => $briaData['result']['structured_prompt'], 
                    'generated_image_url' => $briaData['result']['image_url'],
                    'shot_type' => $preset->shot_type,
                    'style' => $preset->structured_prompt['style'] ?? 'default',
                    'angle' => $preset->structured_prompt['angle'] ?? 'default',
                    'bria_request_id' => $briaData['request_id'],
                    'metadata' => $briaData
                ]);

                $results[] = [
                    'index' => $index,
                    'status' => 'success',
                    'image' => $generatedImage,
                    'image_url' => $briaData['result']['image_url'],
                ];
            }catch(\Exception $e){

                $failed[] = [
                    'index' => $index,
                    'errror' => 'Exception',
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' =>'Batch generation completed',
            'total_uploaded' => count($validated['product_images']),
            'successful' => count($results),
            'failed' => count($failed),
            'results' => $results,
            'failed_details' => $failed
        ], 201);

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
