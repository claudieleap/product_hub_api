<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentUnit extends Model
{
    protected $table = 'establishment_units';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'establishment_id', 'name', 'order_index'];

    protected $casts = [
        'order_index' => 'integer',
    ];
}
