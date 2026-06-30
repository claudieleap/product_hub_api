<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadmapDeletedSeed extends Model
{
    protected $table = 'roadmap_deleted_seeds';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'seed_id';

    protected $fillable = [
        'seed_id',
    ];
}
