<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BrandPreset extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'structured_prompt',
        'description',
        'shot_type',
    ];

    protected $casts = [
        'structured_prompt' => 'array',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function generatedImages(){
        return $this->hasMany(GeneratedImage::class);
    }
}
