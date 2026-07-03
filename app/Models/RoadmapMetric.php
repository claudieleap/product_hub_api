<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadmapMetric extends Model
{
    protected $table = 'roadmap_metrics';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'group_id',
        'label',
        'sort_order',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(RoadmapMetricGroup::class, 'group_id');
    }
}
