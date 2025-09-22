<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedImage extends Model
{
    protected $fillable = [
        'phone_number',
        'original_image',
        'processed_image',
        'emotion_data',
    ];

    protected $casts = [
        'emotion_data' => 'array',
    ];
}
