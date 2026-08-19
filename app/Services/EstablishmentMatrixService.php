<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\EstablishmentCell;
use App\Models\EstablishmentConvenio;
use App\Models\EstablishmentUnit;

class EstablishmentMatrixService
{
    public function createUnit(string $establishmentId, string $unitId, string $name): array
    {
        $order = EstablishmentUnit::where('establishment_id', $establishmentId)->count();

        $unit = EstablishmentUnit::create([
            'id' => $unitId,
            'establishment_id' => $establishmentId,
            'name' => $name,
            'order_index' => $order,
        ]);

        return ['id' => $unit->id, 'name' => $unit->name];
    }

    public function renameUnit(string $unitId, string $name): array
    {
        $unit = $this->findUnit($unitId);
        $unit->name = $name;
        $unit->save();

        return ['id' => $unit->id, 'name' => $unit->name];
    }

    public function deleteUnit(string $unitId): void
    {
        $this->findUnit($unitId)->delete();
    }

    public function createConvenio(string $establishmentId, string $convenioId, string $name): array
    {
        $order = EstablishmentConvenio::where('establishment_id', $establishmentId)->count();

        $convenio = EstablishmentConvenio::create([
            'id' => $convenioId,
            'establishment_id' => $establishmentId,
            'name' => $name,
            'order_index' => $order,
        ]);

        return ['id' => $convenio->id, 'name' => $convenio->name];
    }

    public function renameConvenio(string $convenioId, string $name): array
    {
        $convenio = $this->findConvenio($convenioId);
        $convenio->name = $name;
        $convenio->save();

        return ['id' => $convenio->id, 'name' => $convenio->name];
    }

    public function deleteConvenio(string $convenioId): void
    {
        $this->findConvenio($convenioId)->delete();
    }

    public function upsertCell(string $establishmentId, string $convenioId, string $unitId, array $input): array
    {
        $cell = EstablishmentCell::firstOrNew([
            'convenio_id' => $convenioId,
            'unit_id' => $unitId,
        ]);

        $cell->establishment_id = $establishmentId;

        if (array_key_exists('ativo', $input)) $cell->ativo = $input['ativo'];
        if (array_key_exists('modo', $input)) $cell->modo = $input['modo'];
        if (array_key_exists('portalLogin', $input)) $cell->portal_login = $input['portalLogin'];
        if (array_key_exists('portalSenha', $input)) $cell->portal_senha = $input['portalSenha'];
        if (array_key_exists('detalhe', $input)) $cell->detalhe = $input['detalhe'];
        if (array_key_exists('conciliado', $input)) $cell->conciliado = $input['conciliado'];
        if (array_key_exists('ops', $input)) $cell->ops = $input['ops'];

        $cell->save();

        return [
            'convenioId' => $cell->convenio_id,
            'unitId' => $cell->unit_id,
            'ativo' => $cell->ativo,
            'modo' => $cell->modo,
            'portalLogin' => $cell->portal_login,
            'portalSenha' => $cell->portal_senha,
            'detalhe' => $cell->detalhe,
            'conciliado' => $cell->conciliado,
            'ops' => $cell->ops ?? [],
        ];
    }

    private function findUnit(string $unitId): EstablishmentUnit
    {
        $unit = EstablishmentUnit::find($unitId);

        if (! $unit) {
            throw new NotFoundException('Unidade', $unitId);
        }

        return $unit;
    }

    private function findConvenio(string $convenioId): EstablishmentConvenio
    {
        $convenio = EstablishmentConvenio::find($convenioId);

        if (! $convenio) {
            throw new NotFoundException('Convênio', $convenioId);
        }

        return $convenio;
    }
}
