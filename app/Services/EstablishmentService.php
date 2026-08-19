<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Establishment;
use App\Models\EstablishmentComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EstablishmentService
{
    private const FIELD_MAP = [
        'cnpj' => 'cnpj',
        'cpf' => 'cpf',
        'fantasia' => 'fantasia',
        'contactName' => 'contact_name',
        'razaoSocial' => 'razao_social',
        'kind' => 'kind',
        'projects' => 'projects',
        'especialidades' => 'especialidades',
        'classificacao' => 'classificacao',
        'grupoEcon' => 'grupo_econ',
        'municipio' => 'municipio',
        'uf' => 'uf',
        'bairro' => 'bairro',
        'endereco' => 'endereco',
        'numEndereco' => 'num_endereco',
        'complemento' => 'complemento',
        'cep' => 'cep',
        'ddd' => 'ddd',
        'telefone' => 'telefone',
        'email' => 'email',
        'natNd' => 'nat_nd',
        'pfPj' => 'pf_pj',
        'stageId' => 'stage_id',
        'orderIndex' => 'order_index',
        'onboardingPhaseId' => 'onboarding_phase_id',
        'onboardingOrderIndex' => 'onboarding_order_index',
        'onboardingResponsavelId' => 'onboarding_responsavel_id',
        'observacao' => 'observacao',
    ];

    public function list(): array
    {
        $nextAppointments = $this->nextAppointmentsByEstablishment();

        return Establishment::query()
            ->orderBy('stage_id')
            ->orderBy('order_index')
            ->get()
            ->map(fn (Establishment $establishment) => $this->toArray($establishment, $nextAppointments[$establishment->id] ?? null))
            ->all();
    }

    public function find(string $id): Establishment
    {
        $establishment = Establishment::find($id);

        if (! $establishment) {
            throw new NotFoundException('Estabelecimento', $id);
        }

        return $establishment;
    }

    public function create(array $input): array
    {
        $id = $input['id'] ?? $input['cnpj'] ?? $input['cpf'] ?? ('est-'.Str::random(12));

        $establishment = new Establishment(['id' => $id]);

        foreach (self::FIELD_MAP as $inputKey => $column) {
            if (array_key_exists($inputKey, $input)) {
                $establishment->{$column} = $input[$inputKey];
            }
        }

        $establishment->kind = $establishment->kind ?: 'clinica';
        $establishment->projects = $establishment->projects ?? [];
        $establishment->order_index = $input['orderIndex'] ?? 0;
        $establishment->onboarding_order_index = $input['onboardingOrderIndex'] ?? 0;
        $establishment->observacao = $establishment->observacao ?? '';
        $establishment->save();

        return $this->toArray($establishment);
    }

    public function update(string $id, array $input): array
    {
        $establishment = $this->find($id);

        foreach (self::FIELD_MAP as $inputKey => $column) {
            if (array_key_exists($inputKey, $input)) {
                $establishment->{$column} = $input[$inputKey];
            }
        }

        $establishment->save();

        return $this->toArray($establishment);
    }

    public function delete(string $id): void
    {
        $this->find($id)->delete();
    }

    public function detail(string $id): array
    {
        $establishment = $this->find($id)->load(['units', 'convenios', 'cells']);

        return [
            ...$this->toArray($establishment),
            'units' => $establishment->units->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->all(),
            'convenios' => $establishment->convenios->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all(),
            'cells' => $establishment->cells->map(fn ($cell) => [
                'convenioId' => $cell->convenio_id,
                'unitId' => $cell->unit_id,
                'ativo' => $cell->ativo,
                'modo' => $cell->modo,
                'portalLogin' => $cell->portal_login,
                'portalSenha' => $cell->portal_senha,
                'detalhe' => $cell->detalhe,
                'conciliado' => $cell->conciliado,
                'ops' => $cell->ops ?? [],
            ])->all(),
        ];
    }

    public function listComments(string $id): array
    {
        return $this->find($id)->comments()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (EstablishmentComment $comment) => $this->commentToArray($comment))
            ->all();
    }

    public function addComment(string $id, User $user, string $text): array
    {
        $establishment = $this->find($id);

        $comment = $establishment->comments()->create([
            'user_id' => $user->id,
            'author_name' => $user->name,
            'comment' => $text,
        ]);

        return $this->commentToArray($comment);
    }

    private function commentToArray(EstablishmentComment $comment): array
    {
        return [
            'id' => $comment->id,
            'authorName' => $comment->author_name,
            'comment' => $comment->comment,
            'createdAt' => $comment->created_at?->toIso8601String(),
        ];
    }

    /** @return array<string, array{date: string, time: string, responsavelIds: array<int, string>}>> */
    private function nextAppointmentsByEstablishment(): array
    {
        $rows = DB::table('establishment_appointments')
            ->whereDate('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('time')
            ->get(['establishment_id', 'date', 'time', 'responsavel_ids']);

        $result = [];
        foreach ($rows as $row) {
            if (isset($result[$row->establishment_id])) {
                continue;
            }
            $result[$row->establishment_id] = [
                'date' => $row->date,
                'time' => $row->time,
                'responsavelIds' => $row->responsavel_ids ? json_decode($row->responsavel_ids, true) : [],
            ];
        }

        return $result;
    }

    private function toArray(Establishment $establishment, ?array $nextAppointment = null): array
    {
        return [
            'id' => $establishment->id,
            'cnpj' => $establishment->cnpj,
            'cpf' => $establishment->cpf,
            'fantasia' => $establishment->fantasia,
            'contactName' => $establishment->contact_name,
            'razaoSocial' => $establishment->razao_social,
            'kind' => $establishment->kind,
            'projects' => $establishment->projects ?? [],
            'especialidades' => $establishment->especialidades ?? [],
            'classificacao' => $establishment->classificacao,
            'grupoEcon' => $establishment->grupo_econ,
            'municipio' => $establishment->municipio,
            'uf' => $establishment->uf,
            'bairro' => $establishment->bairro,
            'endereco' => $establishment->endereco,
            'numEndereco' => $establishment->num_endereco,
            'complemento' => $establishment->complemento,
            'cep' => $establishment->cep,
            'ddd' => $establishment->ddd,
            'telefone' => $establishment->telefone,
            'email' => $establishment->email,
            'natNd' => $establishment->nat_nd,
            'pfPj' => $establishment->pf_pj,
            'stageId' => $establishment->stage_id,
            'orderIndex' => $establishment->order_index,
            'onboardingPhaseId' => $establishment->onboarding_phase_id,
            'onboardingOrderIndex' => $establishment->onboarding_order_index,
            'onboardingResponsavelId' => $establishment->onboarding_responsavel_id,
            'observacao' => $establishment->observacao,
            'updatedAt' => $establishment->updated_at?->toIso8601String(),
            'nextAppointment' => $nextAppointment ?? ($this->nextAppointmentsByEstablishment()[$establishment->id] ?? null),
        ];
    }
}
