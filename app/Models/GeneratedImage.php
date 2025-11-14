<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedImage extends Model
{
    protected $fillable = [

        'user_id',
        'brand_preset_id',
        'product_image_path',
        'user_intent',
        'structured_prompt',
        'style',
        'angle',
        'generated_image_url',
        'shot_type',
        'bria_request_id',
        'metadata',
    ];

    protected $casts = [
        'structured_prompt' => 'array',
        'metadata' => 'array',
    ];

   
    public function user(){

        return $this->belongsTo(User::class);
    }

    public function brandPreset(){

        return $this->belongsTo(BrandPreset::class);
    }
}
