<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\RoadmapMetric;
use App\Models\RoadmapMetricGroup;
use App\Support\RoadmapMetricsDefaults;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RoadmapMetricsService
{
    public function getCatalog(): array
    {
        $this->ensureSeeded();

        return RoadmapMetricGroup::query()
            ->with('metrics')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (RoadmapMetricGroup $group) => $this->groupToArray($group))
            ->values()
            ->all();
    }

    public function createGroup(array $input): array
    {
        $label = trim($input['label'] ?? '');
        if ($label === '') {
            throw new InvalidArgumentException('O nome do grupo é obrigatório.');
        }

        $id = $this->resolveGroupId($input['id'] ?? null, $label);
        $sortOrder = $this->nextGroupSortOrder();

        $group = RoadmapMetricGroup::query()->create([
            'id' => $id,
            'label' => $label,
            'sort_order' => $sortOrder,
        ]);

        return $this->groupToArray($group->load('metrics'));
    }

    public function updateGroup(string $id, array $input): array
    {
        $group = $this->findGroup($id);
        $label = trim($input['label'] ?? '');

        if ($label === '') {
            throw new InvalidArgumentException('O nome do grupo é obrigatório.');
        }

        $group->update(['label' => $label]);

        return $this->groupToArray($group->fresh('metrics'));
    }

    public function deleteGroup(string $id): void
    {
        $group = $this->findGroup($id);
        $group->delete();
    }

    public function createMetric(array $input): array
    {
        $groupId = trim($input['groupId'] ?? '');
        $label = trim($input['label'] ?? '');

        if ($groupId === '') {
            throw new InvalidArgumentException('O grupo da métrica é obrigatório.');
        }

        if ($label === '') {
            throw new InvalidArgumentException('O nome da métrica é obrigatório.');
        }

        $this->findGroup($groupId);

        $id = $this->resolveMetricId($input['id'] ?? null, $label, $groupId);
        $sortOrder = $this->nextMetricSortOrder($groupId);

        $metric = RoadmapMetric::query()->create([
            'id' => $id,
            'group_id' => $groupId,
            'label' => $label,
            'sort_order' => $sortOrder,
        ]);

        return $this->metricToArray($metric);
    }

    public function updateMetric(string $id, array $input): array
    {
        $metric = $this->findMetric($id);
        $label = trim($input['label'] ?? '');

        if ($label === '') {
            throw new InvalidArgumentException('O nome da métrica é obrigatório.');
        }

        $updates = ['label' => $label];

        if (array_key_exists('groupId', $input)) {
            $groupId = trim($input['groupId'] ?? '');
            if ($groupId === '') {
                throw new InvalidArgumentException('O grupo da métrica é obrigatório.');
            }

            $this->findGroup($groupId);
            $updates['group_id'] = $groupId;
        }

        $metric->update($updates);

        return $this->metricToArray($metric->fresh());
    }

    public function deleteMetric(string $id): void
    {
        $metric = $this->findMetric($id);
        $metric->delete();
    }

    private function ensureSeeded(): void
    {
        if (RoadmapMetricGroup::query()->exists()) {
            return;
        }

        foreach (RoadmapMetricsDefaults::groups() as $groupIndex => $groupData) {
            $group = RoadmapMetricGroup::query()->create([
                'id' => $groupData['id'],
                'label' => $groupData['label'],
                'sort_order' => $groupIndex,
            ]);

            foreach ($groupData['metrics'] as $metricIndex => $metricData) {
                RoadmapMetric::query()->create([
                    'id' => $metricData['id'],
                    'group_id' => $group->id,
                    'label' => $metricData['label'],
                    'sort_order' => $metricIndex,
                ]);
            }
        }
    }

    private function findGroup(string $id): RoadmapMetricGroup
    {
        $group = RoadmapMetricGroup::query()->find($id);

        if (! $group) {
            throw new NotFoundException('Grupo de métricas não encontrado.');
        }

        return $group;
    }

    private function findMetric(string $id): RoadmapMetric
    {
        $metric = RoadmapMetric::query()->find($id);

        if (! $metric) {
            throw new NotFoundException('Métrica não encontrada.');
        }

        return $metric;
    }

    private function resolveGroupId(?string $requestedId, string $label): string
    {
        if ($requestedId !== null && trim($requestedId) !== '') {
            $id = $this->slugify($requestedId, 48);
        } else {
            $id = $this->slugify($label, 48);
        }

        if (RoadmapMetricGroup::query()->where('id', $id)->exists()) {
            throw new InvalidArgumentException('Já existe um grupo com este identificador.');
        }

        return $id;
    }

    private function resolveMetricId(?string $requestedId, string $label, string $groupId): string
    {
        if ($requestedId !== null && trim($requestedId) !== '') {
            $id = $this->slugify($requestedId, 72);
        } else {
            $id = 'rm-m-'.$this->slugify($label, 64);
        }

        if (! str_starts_with($id, 'rm-m-')) {
            $id = 'rm-m-'.$id;
        }

        if (RoadmapMetric::query()->where('id', $id)->exists()) {
            $suffix = 2;
            $base = $id;

            while (RoadmapMetric::query()->where('id', "{$base}-{$suffix}")->exists()) {
                $suffix++;
            }

            $id = "{$base}-{$suffix}";
        }

        return $id;
    }

    private function slugify(string $value, int $maxLength): string
    {
        $slug = Str::slug($value, '-');

        return substr($slug !== '' ? $slug : 'metrica', 0, $maxLength);
    }

    private function nextGroupSortOrder(): int
    {
        return (int) RoadmapMetricGroup::query()->max('sort_order') + 1;
    }

    private function nextMetricSortOrder(string $groupId): int
    {
        return (int) RoadmapMetric::query()
            ->where('group_id', $groupId)
            ->max('sort_order') + 1;
    }

    private function groupToArray(RoadmapMetricGroup $group): array
    {
        return [
            'id' => $group->id,
            'label' => $group->label,
            'sortOrder' => $group->sort_order,
            'metrics' => $group->metrics
                ->map(fn (RoadmapMetric $metric) => $this->metricToArray($metric))
                ->values()
                ->all(),
        ];
    }

    private function metricToArray(RoadmapMetric $metric): array
    {
        return [
            'id' => $metric->id,
            'groupId' => $metric->group_id,
            'label' => $metric->label,
            'sortOrder' => $metric->sort_order,
        ];
    }
}
