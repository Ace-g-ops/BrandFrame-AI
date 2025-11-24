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
            'shot_type' => 'required|in:lifestyle,hero,flat_lay,context,white_background',
            'structured_prompt' => 'required|array'
         ]);

         $preset = BrandPreset::create([

            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'structured_prompt' => $validated['structured_prompt']
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

    public function applyPreset(Request $request){

        //validate incoming request
        $validated = $request->validate([

            'preset_id' => 'required|exists:brand-presets,id',
            'product_image' => 'required|mimes:jpeg,png,jpg|max:5120',
            'product_description' => 'nullable|string|max:255'
        ]);

        //get preset
        $preset = BrandPreset::where('id', $validated['preset_id'])
        ->where('user_id', $request->user()->id)
        ->firstOrFail();

        //store product image
        $productPath = $request->file('product_image')->store('products', 'public');

        //call bria api with preset's structured prompt

        $apiKey = env('BRIA_API_KEY');

          try{

           
        $response = Http::withHeaders([ // setting up headers

            'api_token' => $apiKey, // set the api token in headers
            'Content-Type' => 'application/json', // setting up headers like in postman
        ])->post('https://engine.prod.bria-api.com/v2/image/generate', [ // post request to bria api
            'prompt' => $preset->structured_prompt,// prompt for image generation
            'num_results' => 1, // Number of images to generate
            'sync' => true, // Synchronous request to get immediate results
        ]);

        if(!$response->sucessful()){

            return response()->json([

                'message' => 'Bria API error',
                'details' => $response->json()
            ], $response->status());
        }
        
        $briaData = $response->json();

        //save to database
        $generatedImage = GeneratedImage::create([

            'user_id' => $request->user()->id,
            'product_image_path' => $productPath,
            'user_intent' => $validated['description'] ?? 'product',
            'structured_prompt' => json_decode($briaData['result']['structured_prompt'], true),
            'generated_image_url' => $briaData['result']['image_url'],
            'shot_type' => $preset->shot_type,
            'angle' => $preset->structured_prompt['style'] ?? 'default',
            'style' => $preset->structured_prompt['angle'] ?? 'default',
            'bria_request_id'=> $briaData['request_id'],
            'metadata' => $briaData
        ]);

        return response()->json([

            'message' => 'Image generated succesfully',
            'data' => $generatedImage,
            'image_url' => $briaData['result']['image_url']
        ], 201);

    }catch (\Exception $e){

        return response()->json([

            'error' => 'Failed to generate image',
            'message' => $e->getMessage()
        ], 500);
    };

    }
}

