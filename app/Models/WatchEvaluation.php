<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchEvaluation extends Model
{
    protected $fillable = [
        'name', 'email', 'phone_prefix', 'phone', 'country', 'city', 'phone_prefix', 'phone',
        'brand', 'model', 'reference', 'manufacture_year',
        'condition', 'box_papers', 'ai_response',
        'estimated_value_min', 'estimated_value_max', 'locale',
    ];

    protected function casts(): array
    {
        return [
            'ai_response' => 'array',
        ];
    }
}
