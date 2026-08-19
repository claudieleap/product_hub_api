<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingPhase extends Model
{
    protected $table = 'onboarding_phases';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'title', 'hint', 'order_index'];

    protected $casts = [
        'order_index' => 'integer',
    ];
}
