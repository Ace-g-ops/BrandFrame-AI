<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class testBriaController extends Controller
{
    public function testGenerate(Request $request){

        $apiKey = env('BRIA_API_KEY'); // Ensure your API key is stored in the .env file. get the api key

        $response = Http::withHeaders([ // setting up headers

            'api_token' => $apiKey, // set the api token in headers
            'Content-Type' => 'application/json', // setting up headers like in postman
        ])->post('https://engine.prod.bria-api.com/v2/image/generate', [ // post request to bria api
            'prompt' => $request->prompt ?? 'Professional product photo of a coffee mug on a wooden table', // prompt for image generation
            'num_results' => 1, // Number of images to generate
            'sync' => true, // Synchronous request to get immediate results
        ]);

       if($response->successful()){ // check if response is successful

            return response()->json([ // return json response
                'data' => $response->json(), // return the json data from bria api
            ], 200); // http status code 200
       } else {
            return response()->json([ // return json response
                'message' => 'Failed to generate image', // error message
                'error' => $response->json(), // return the error from bria api
            ], $response->status()); // return the status code from bria api
       }
    } 
}
