<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadmapCustomProduct extends Model
{
    protected $table = 'roadmap_custom_products';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'roadmap_type',
        'id',
        'title',
        'description',
        'icon',
        'accent',
        'is_hidden',
        'product_created_at',
    ];

    protected $casts = [
        'product_created_at' => 'datetime',
        'is_hidden' => 'boolean',
    ];
}
