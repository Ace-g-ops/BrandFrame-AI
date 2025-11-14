<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function uploadProduct(Request $request ){

        // validate incoming image requests
          $validated = $request->validate([

        'product_image' => 'required|image|mimes:png,jpg,jpeg|max:5120'
    ]);

    // store image in storage

    $path = $request->file('product_image')->store('products', 'public');

    // return to path

    return response()->json([

        'message' => 'Product Image Uploaded',
        'path' => $path,
        'url' => asset('storage/' . $path)
    ], 201);

    }
}
