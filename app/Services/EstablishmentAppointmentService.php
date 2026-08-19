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
                'establishment_appointments.responsavel_ids',
                'establishment_appointments.notes',
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
                'responsavelIds' => $row->responsavel_ids ?? [],
                'notes' => $row->notes,
            ])
            ->all();
    }

    public function listForEstablishment(string $establishmentId): array
    {
        return EstablishmentAppointment::query()
            ->where('establishment_id', $establishmentId)
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->get()
            ->map(fn ($appointment) => [
                'id' => $appointment->id,
                'establishmentId' => $appointment->establishment_id,
                'date' => $appointment->date->format('Y-m-d'),
                'time' => $appointment->time,
                'modality' => $appointment->modality,
                'location' => $appointment->location,
                'paymentLink' => $appointment->payment_link,
                'responsavelIds' => $appointment->responsavel_ids ?? [],
                'notes' => $appointment->notes,
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
                'responsavel_ids' => $input['responsavelIds'] ?? [],
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
                'responsavelIds' => $appointment->responsavel_ids ?? [],
                'notes' => $appointment->notes,
                'stageId' => $establishment->stage_id,
            ];
        });
    }

    public function update(string $establishmentId, string $appointmentId, array $input): array
    {
        $appointment = ctype_digit($appointmentId)
            ? EstablishmentAppointment::where('establishment_id', $establishmentId)->find((int) $appointmentId)
            : null;

        if (! $appointment) {
            throw new NotFoundException('Agendamento', $appointmentId);
        }

        $map = [
            'date' => 'date',
            'time' => 'time',
            'modality' => 'modality',
            'location' => 'location',
            'paymentLink' => 'payment_link',
            'responsavelIds' => 'responsavel_ids',
            'notes' => 'notes',
        ];

        foreach ($map as $inputKey => $column) {
            if (array_key_exists($inputKey, $input)) {
                $appointment->{$column} = $input[$inputKey];
            }
        }

        $appointment->save();

        return [
            'id' => $appointment->id,
            'establishmentId' => $appointment->establishment_id,
            'date' => $appointment->date->format('Y-m-d'),
            'time' => $appointment->time,
            'modality' => $appointment->modality,
            'location' => $appointment->location,
            'paymentLink' => $appointment->payment_link,
            'responsavelIds' => $appointment->responsavel_ids ?? [],
            'notes' => $appointment->notes,
        ];
    }

    public function delete(string $establishmentId, string $appointmentId): void
    {
        $appointment = ctype_digit($appointmentId)
            ? EstablishmentAppointment::where('establishment_id', $establishmentId)->find((int) $appointmentId)
            : null;

        if (! $appointment) {
            throw new NotFoundException('Agendamento', $appointmentId);
        }

        $appointment->delete();
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
