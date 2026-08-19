<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Establishment extends Model
{
    protected $table = 'establishments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'cnpj',
        'cpf',
        'fantasia',
        'contact_name',
        'razao_social',
        'kind',
        'projects',
        'especialidades',
        'classificacao',
        'grupo_econ',
        'municipio',
        'uf',
        'bairro',
        'endereco',
        'num_endereco',
        'complemento',
        'cep',
        'ddd',
        'telefone',
        'email',
        'nat_nd',
        'pf_pj',
        'stage_id',
        'order_index',
        'onboarding_phase_id',
        'onboarding_order_index',
        'onboarding_responsavel_id',
        'observacao',
    ];

    protected $casts = [
        'especialidades' => 'array',
        'projects' => 'array',
        'order_index' => 'integer',
        'onboarding_order_index' => 'integer',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(EstablishmentComment::class, 'establishment_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(EstablishmentUnit::class, 'establishment_id')->orderBy('order_index');
    }

    public function convenios(): HasMany
    {
        return $this->hasMany(EstablishmentConvenio::class, 'establishment_id')->orderBy('order_index');
    }

    public function cells(): HasMany
    {
        return $this->hasMany(EstablishmentCell::class, 'establishment_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(EstablishmentAppointment::class, 'establishment_id');
    }
}
