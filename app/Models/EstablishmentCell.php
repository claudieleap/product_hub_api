<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentCell extends Model
{
    protected $table = 'establishment_cells';

    protected $fillable = [
        'establishment_id', 'convenio_id', 'unit_id',
        'ativo', 'modo', 'portal_login', 'portal_senha', 'detalhe', 'conciliado', 'ops',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'conciliado' => 'boolean',
        'ops' => 'array',
    ];
}
