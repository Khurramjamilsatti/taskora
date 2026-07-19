<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateFrequency extends Model
{
    protected $fillable = [
        'slug',
        'label',
        'factor',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'factor' => 'float',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
