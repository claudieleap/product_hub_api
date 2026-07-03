<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadmapItem extends Model
{
    protected $table = 'roadmap_items';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'roadmap_type',
        'id',
        'product_id',
        'priority',
        'backlog_priority',
        'title',
        'notes',
        'metrics',
        'dev_status',
        'delivered_at',
        'item_created_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'item_created_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];
}
