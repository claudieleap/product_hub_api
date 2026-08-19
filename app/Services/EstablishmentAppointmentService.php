<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Establishment;
use App\Models\EstablishmentAppointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EstablishmentAppointmentService
{
    /**
     * Ordem fixa do funil comercial — usada só pra decidir se um agendamento
     * "avança" o estágio (nunca recua um estágio já mais adiantado).
     */
    private const STAGE_ORDER = [
        'inbox', 'qualificado', 'fremium_aceito', 'proposta_apresentada', 'onboardado_fremium', 'levantada_mao',
    ];

    private const MEETING_STAGE = 'fremium_aceito';

    public function listInRange(string $start, string $end): array
    {
        return EstablishmentAppointment::query()
            ->join('establishments', 'establishments.id', '=', 'establishment_appointments.establishment_id')
            ->whereBetween('establishment_appointments.date', [$start, $end])
            ->orderBy('establishment_appointments.date')
            ->orderBy('establishment_appointments.time')
            ->get([
                'establishment_appointments.id',
                'establishment_appointments.establishment_id',
                'establishment_appointments.date',
                'establishment_appointments.time',
                'establishment_appointments.modality',
                'establishment_appointments.location',
                'establishment_appointments.payment_link',
                'establishment_appointments.responsavel_id',
                'establishments.fantasia',
                'establishments.razao_social',
            ])
            ->map(fn ($row) => [
                'id' => $row->id,
                'establishmentId' => $row->establishment_id,
                'establishmentName' => $row->fantasia ?: $row->razao_social,
                'date' => $row->date->format('Y-m-d'),
                'time' => $row->time,
                'modality' => $row->modality,
                'location' => $row->location,
                'paymentLink' => $row->payment_link,
                'responsavelId' => $row->responsavel_id,
            ])
            ->all();
    }

    public function create(string $establishmentId, User $user, array $input): array
    {
        $establishment = Establishment::find($establishmentId);

        if (! $establishment) {
            throw new NotFoundException('Estabelecimento', $establishmentId);
        }

        return DB::transaction(function () use ($establishment, $user, $input) {
            $appointment = $establishment->appointments()->create([
                'date' => $input['date'],
                'time' => $input['time'],
                'modality' => $input['modality'],
                'location' => $input['location'] ?? null,
                'payment_link' => $input['paymentLink'] ?? null,
                'responsavel_id' => $input['responsavelId'] ?? null,
                'created_by_user_id' => $user->id,
                'notes' => $input['notes'] ?? null,
            ]);

            $this->maybeAdvanceStage($establishment);

            return [
                'id' => $appointment->id,
                'establishmentId' => $establishment->id,
                'date' => $appointment->date->format('Y-m-d'),
                'time' => $appointment->time,
                'modality' => $appointment->modality,
                'location' => $appointment->location,
                'paymentLink' => $appointment->payment_link,
                'responsavelId' => $appointment->responsavel_id,
                'stageId' => $establishment->stage_id,
            ];
        });
    }

    private function maybeAdvanceStage(Establishment $establishment): void
    {
        if ($establishment->stage_id === null) {
            return;
        }

        $currentIndex = array_search($establishment->stage_id, self::STAGE_ORDER, true);
        $meetingIndex = array_search(self::MEETING_STAGE, self::STAGE_ORDER, true);

        if ($currentIndex === false || $currentIndex < $meetingIndex) {
            $establishment->stage_id = self::MEETING_STAGE;
            $establishment->save();
        }
    }
}
