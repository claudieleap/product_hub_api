<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentConvenio extends Model
{
    protected $table = 'establishment_convenios';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'establishment_id', 'name', 'order_index'];

    protected $casts = [
        'order_index' => 'integer',
    ];
}
