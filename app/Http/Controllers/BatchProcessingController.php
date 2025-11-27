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
            'product_images' => 'required|image|mimes:jpeg,png,jpg|min:1,max:10',
            'product_images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'product_description' => 'nullable|string|max:255'
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

                $response = HTTP::withHeaders([

                    'api_token' => $apiKey,
                    'Content_type' => 'application/json'
                ])->post('https://engine.prod.bria-api.com/v2/image/generate', [
                    'prompt' => $preset->structured_prompt,
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
                    'structured_prompt' => json_encode($briaData['result']['structured_prompt'], true),
                    'generated_image_url' => $briaData['result']['image_url'],
                    'shot_type' => $preset->shot_type,
                    'style' => $preset->structured_prompt['style'] ?? 'default',
                    'angle' => $preset->structured_prompt['angle'] ?? 'default',
                    'bria_request_id' => $briaData['request_id'],
                    'metadat' => $briaData
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
}
