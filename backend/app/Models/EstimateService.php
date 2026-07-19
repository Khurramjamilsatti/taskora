<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateService extends Model
{
    protected $fillable = [
        'slug',
        'label',
        'base_price',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
