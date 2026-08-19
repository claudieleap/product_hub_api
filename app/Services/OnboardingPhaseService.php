<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Establishment;
use App\Models\OnboardingPhase;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OnboardingPhaseService
{
    public function list(): array
    {
        return OnboardingPhase::query()
            ->orderBy('order_index')
            ->get()
            ->map(fn (OnboardingPhase $phase) => $this->toArray($phase))
            ->all();
    }

    public function create(string $title): array
    {
        $maxOrder = OnboardingPhase::max('order_index') ?? -1;

        $phase = OnboardingPhase::create([
            'id' => 'phase-'.Str::random(10),
            'title' => $title,
            'hint' => '',
            'order_index' => $maxOrder + 1,
        ]);

        return $this->toArray($phase);
    }

    public function update(string $id, array $input): array
    {
        $phase = OnboardingPhase::find($id);

        if (! $phase) {
            throw new NotFoundException('Fase', $id);
        }

        if (array_key_exists('title', $input)) $phase->title = $input['title'];
        if (array_key_exists('hint', $input)) $phase->hint = $input['hint'];
        if (array_key_exists('orderIndex', $input)) $phase->order_index = $input['orderIndex'];

        $phase->save();

        return $this->toArray($phase);
    }

    public function delete(string $id): void
    {
        $phase = OnboardingPhase::find($id);

        if (! $phase) {
            throw new NotFoundException('Fase', $id);
        }

        if (OnboardingPhase::count() <= 1) {
            throw new InvalidArgumentException('Não é possível remover a última fase.');
        }

        $fallback = OnboardingPhase::where('id', '!=', $id)->orderBy('order_index')->first();

        Establishment::where('onboarding_phase_id', $id)
            ->update(['onboarding_phase_id' => $fallback->id]);

        $phase->delete();
    }

    private function toArray(OnboardingPhase $phase): array
    {
        return [
            'id' => $phase->id,
            'title' => $phase->title,
            'hint' => $phase->hint,
            'orderIndex' => $phase->order_index,
        ];
    }
}
