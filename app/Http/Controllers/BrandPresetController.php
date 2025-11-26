<?php

namespace App\Http\Controllers;

use App\Models\BrandPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\GeneratedImage;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseIsRedirected;

class BrandPresetController extends Controller
{
    // create presets from generated image
    public function store(Request $request){

        try{

            $validated = $request->validate([

            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'shot_type' => 'required|in:lifestyle,hero,flat_lay,context,white_background,potrait,landscape,instagram_post,square,instagram_story',
            'structured_prompt' => 'required|array'
         ]);

         $preset = BrandPreset::create([

            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'structured_prompt' => $validated['structured_prompt'],
            'shot_type' => $validated['shot_type'],
         ]);

         return response()->json([

            'message' => 'Brand Preset Created Sucessfully',
            'data' => $preset
         ], 201);

        }catch(\Exception $e){

            return response()->json([
                "messsage" => "Brand Preset Failed to store",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // get all users presets
    public function index(Request $request){
        $preset = BrandPreset::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            
            'message' => 'All Presets gottn succesfully',
            'data' => $preset
        ], 200);
    }

    // Get a single presets
    public function show(Request $request, $id){

        $preset = BrandPreset::where('id', $id)
        ->where('user_id', $request->user()->id)
        ->firstOrFail();

        return response()->json($preset);
    }

    //update presets
    public function update(Request $request, $id){

        //query the database
        $preset = BrandPreset::where('id', $id)
        ->where('user_id', $request->user()->id)
        ->firstOrFail();

        //validate the data
        $validated = $request->validate([

            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'structured_prompt' => 'sometimes|array'
        ]);

        $preset->update($validated);

        return response()->json([

            'message' => 'Brand Preset Updated successfully',
            'data' => $preset
        ], 200);
    }   

    // delete preset
    public function destroy(Request $request, $id){

        $preset = BrandPreset::where('id', $id)
        ->where('user_id', $request->user()->id)
        ->firstOrFail();

        $preset->delete();

        return response()->json([

            'message' => 'Preset successfully deleted'
        ]);
    }

    //Apply preset to generate image

   public function applyPreset(Request $request)
{
    // Validate incoming request
    $validated = $request->validate([
        'preset_id' => 'required|exists:brand_presets,id',
        'product_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        'product_description' => 'nullable|string|max:255'
    ]);

    // Get preset
    $preset = BrandPreset::where('id', $validated['preset_id'])
        ->where('user_id', $request->user()->id)
        ->firstOrFail();

    // Store product image
    $productPath = $request->file('product_image')->store('products', 'public');

    // Get product description
    $productDesc = $validated['product_description'] ?? 'product';

    // Build new prompt using preset's style + new product
    $newPrompt = $this->buildPromptFromPreset($preset, $productDesc);

    // Call Bria API
    $apiKey = env('BRIA_API_KEY');

    try {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'api_token' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://engine.prod.bria-api.com/v2/image/generate', [
                'prompt' => $newPrompt,
                'num_results' => 1,
                'sync' => true,
            ]);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Bria API error',
                'details' => $response->json()
            ], $response->status());
        }
        
        $briaData = $response->json();

        // Save to database with preset reference
        $generatedImage = GeneratedImage::create([
            'user_id' => $request->user()->id,
            'brand_preset_id' => $preset->id,
            'product_image_path' => $productPath,
            'user_intent' => $productDesc,
            'structured_prompt' => json_decode($briaData['result']['structured_prompt'], true),
            'generated_image_url' => $briaData['result']['image_url'],
            'shot_type' => $preset->shot_type,
            'angle' => $preset->structured_prompt['angle'] ?? 'default',
            'style' => $preset->structured_prompt['style'] ?? 'default',
            'bria_request_id' => $briaData['request_id'],
            'metadata' => $briaData
        ]);

        return response()->json([
            'message' => 'Image generated successfully with preset',
            'data' => $generatedImage,
            'image_url' => $briaData['result']['image_url']
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to generate image',
            'message' => $e->getMessage()
        ], 500);
    }
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
