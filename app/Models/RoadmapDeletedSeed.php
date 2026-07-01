<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadmapDeletedSeed extends Model
{
    protected $table = 'roadmap_deleted_seeds';

    public $incrementing = false;

    protected $fillable = [
        'roadmap_type',
        'seed_id',
    ];
}
