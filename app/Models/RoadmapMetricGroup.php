<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoadmapMetricGroup extends Model
{
    protected $table = 'roadmap_metric_groups';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'label',
        'sort_order',
    ];

    public function metrics(): HasMany
    {
        return $this->hasMany(RoadmapMetric::class, 'group_id')->orderBy('sort_order');
    }
}
