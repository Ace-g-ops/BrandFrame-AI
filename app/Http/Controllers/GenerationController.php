<?php

namespace App\Http\Controllers;

use App\Http\Helpers\PromptBuilder;
use App\Models\GeneratedImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GenerationController extends Controller
{
    public function generate(Request $request) {

        //validate incoming requests 
        $validated = $request->validate([

            'product_image' => 'required|image|mimes:png,jpg,jpeg|max:5120',
            'shot_type' => 'required|in:lifestyle, hero, falt_lay, context,white_background',
            'product_description' => 'nullable|string|max:400'
        ]);

        //store product image

        $productPath = $request->file('product_image')->store('products', 'public');

        //build prompts

        $productDesc = $validated['product_description'] ?? 'product';
        $prompt = PromptBuilder::buildPrompt($validated['shot_type'], $productDesc);

        // Call Bria API
        $apiKey = env('BRIA_API_KEY');

        try{

           
        $response = Http::withHeaders([ // setting up headers

            'api_token' => $apiKey, // set the api token in headers
            'Content-Type' => 'application/json', // setting up headers like in postman
        ])->post('https://engine.prod.bria-api.com/v2/image/generate', [ // post request to bria api
            'prompt' => $prompt,// prompt for image generation
            'num_results' => 1, // Number of images to generate
            'sync' => true, // Synchronous request to get immediate results
        ]);
        
        $briaData = $response->json();

        //save to database
        $geeneratedImage = GeneratedImage::create([

            'user_id' => $request->user()->id,
            'product_image_path' => $productPath,
            'user_intent' => $productDesc,
            'structured_prompt' => json_decode($briaData['result']['structured_prompt'], true),
            'generated_image_url' => $briaData['result']['image_url'],
            'shot_type' => $validated['shot_type'],
            'bria_request_id'=> $briaData['request_id'],
            'metadata' => $briaData
        ]);

        return response()->json([

            'message' => 'Image generated succesfully',
            'data' => $geeneratedImage,
            'image_url' => $briaData['result']['image_url']
        ], 201);

    }catch (\Exception $e){

        return response()->json([

            'error' => 'Failed to generate image',
            'message' => $e->getMessage()
        ], 500);
    };

}

//get all users generated images 

public function index(Request $request){

    $images = GeneratedImage::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->get();

    return response()->json($images);
}

//get a single generated image
public function show(Request $request, string $id) {

    $image = GeneratedImage::where('id', $id)
    ->where('user_id', $request->user()->id)
    ->firstOrFail();

    return response()->json($image);

}

//delete generated image
public function destroy(Request $request, $id){

    $image = GeneratedImage::where('id', $id)
    ->Arr::where('user_id', $request->user()->id)
    ->firstOrFail();

    $image->delete();

    return response()->json([

        'message' => 'Image succesfully deleted'
    ]);

}
}
