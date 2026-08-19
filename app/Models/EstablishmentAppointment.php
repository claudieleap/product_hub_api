<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstablishmentAppointment extends Model
{
    protected $table = 'establishment_appointments';

    protected $fillable = [
        'establishment_id', 'date', 'time', 'modality', 'location', 'payment_link',
        'responsavel_ids', 'created_by_user_id', 'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'responsavel_ids' => 'array',
    ];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }
}
