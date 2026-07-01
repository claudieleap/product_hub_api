<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\RoadmapCustomProduct;
use App\Models\RoadmapDeletedSeed;
use App\Models\RoadmapItem;
use App\Support\RoadmapType;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoadmapService
{
    private const PRIORITIES = ['alta', 'media', 'baixa', 'perfumaria'];

    private const DEV_STATUSES = ['a_fazer', 'em_andamento', 'concluido'];

    public function getState(string $type): array
    {
        $type = RoadmapType::resolve($type);

        return [
            'roadmapType' => $type,
            'items' => RoadmapItem::query()
                ->where('roadmap_type', $type)
                ->orderBy('item_created_at')
                ->get()
                ->map(fn (RoadmapItem $item) => $this->itemToArray($item))
                ->values()
                ->all(),
            'customProducts' => RoadmapCustomProduct::query()
                ->where('roadmap_type', $type)
                ->orderBy('product_created_at')
                ->get()
                ->map(fn (RoadmapCustomProduct $product) => $this->productToArray($product))
                ->values()
                ->all(),
            'deletedSeedIds' => RoadmapDeletedSeed::query()
                ->where('roadmap_type', $type)
                ->pluck('seed_id')
                ->values()
                ->all(),
        ];
    }

    public function importState(string $type, array $payload): array
    {
        $type = RoadmapType::resolve($type);

        if (RoadmapItem::query()->where('roadmap_type', $type)->exists()
            || RoadmapCustomProduct::query()->where('roadmap_type', $type)->exists()
            || RoadmapDeletedSeed::query()->where('roadmap_type', $type)->exists()) {
            throw new InvalidArgumentException('O roadmap já possui dados. Importação bloqueada para evitar sobrescrita.');
        }

        DB::transaction(function () use ($type, $payload) {
            foreach ($payload['items'] ?? [] as $item) {
                $this->persistItem($type, $this->normalizeItemInput($item));
            }

            foreach ($payload['customProducts'] ?? [] as $product) {
                $this->persistProduct($type, $this->normalizeProductInput($product));
            }

            foreach ($payload['deletedSeedIds'] ?? [] as $seedId) {
                $this->trackDeletedSeed($type, (string) $seedId, false);
            }
        });

        return $this->getState($type);
    }

    /**
     * Mescla estado enviado pelo cliente (upsert por id). Não apaga itens ausentes no payload.
     */
    public function syncState(string $type, array $payload): array
    {
        $type = RoadmapType::resolve($type);

        DB::transaction(function () use ($type, $payload) {
            foreach ($payload['items'] ?? [] as $item) {
                $this->upsertItem($type, $item);
            }

            foreach ($payload['customProducts'] ?? [] as $product) {
                $this->upsertCustomProduct($type, $product);
            }

            foreach ($payload['deletedSeedIds'] ?? [] as $seedId) {
                $seedId = (string) $seedId;
                if ($seedId === '') {
                    continue;
                }

                $this->trackDeletedSeed($type, $seedId, true);
                RoadmapItem::query()
                    ->where('roadmap_type', $type)
                    ->where('id', $seedId)
                    ->delete();
            }
        });

        return $this->getState($type);
    }

    public function createItem(string $type, array $input): array
    {
        $type = RoadmapType::resolve($type);
        $data = $this->normalizeItemInput($input);

        if ($data['title'] === '') {
            throw new InvalidArgumentException('O título do item é obrigatório.');
        }

        if ($this->findItem($type, $data['id'])) {
            throw new InvalidArgumentException('Já existe um item com este identificador.');
        }

        $item = $this->persistItem($type, $data);

        return $this->itemToArray($item);
    }

    public function updateItem(string $type, string $id, array $input): array
    {
        $type = RoadmapType::resolve($type);
        $item = $this->findItem($type, $id);

        if (!$item) {
            throw new NotFoundException('Item do roadmap', $id);
        }

        $current = $this->itemToArray($item);
        $merged = array_merge($current, array_intersect_key($input, array_flip([
            'productId', 'priority', 'title', 'notes', 'metrics', 'devStatus',
        ])));

        $data = $this->normalizeItemInput(array_merge($merged, ['id' => $id]));

        if ($data['title'] === '') {
            throw new InvalidArgumentException('O título do item é obrigatório.');
        }

        $item->fill([
            'product_id' => $data['product_id'],
            'priority' => $data['priority'],
            'title' => $data['title'],
            'notes' => $data['notes'],
            'metrics' => $data['metrics'],
            'dev_status' => $data['dev_status'],
        ]);
        $item->save();

        return $this->itemToArray($item->fresh());
    }

    public function deleteItem(string $type, string $id): void
    {
        $type = RoadmapType::resolve($type);
        $item = $this->findItem($type, $id);

        if (!$item) {
            throw new NotFoundException('Item do roadmap', $id);
        }

        if (str_starts_with($id, 'rm-seed-')) {
            $this->trackDeletedSeed($type, $id, true);
        }

        $item->delete();
    }

    public function deleteItemsByProduct(string $type, string $productId): int
    {
        $type = RoadmapType::resolve($type);
        $items = RoadmapItem::query()
            ->where('roadmap_type', $type)
            ->where('product_id', $productId)
            ->get();

        $count = $items->count();

        foreach ($items as $item) {
            $this->deleteItem($type, $item->id);
        }

        return $count;
    }

    public function createCustomProduct(string $type, array $input): array
    {
        $type = RoadmapType::resolve($type);
        $data = $this->normalizeProductInput($input);

        if ($data['id'] === '') {
            throw new InvalidArgumentException('O identificador do módulo é obrigatório.');
        }

        if ($data['title'] === '') {
            throw new InvalidArgumentException('O título do módulo é obrigatório.');
        }

        if ($this->findProduct($type, $data['id'])) {
            throw new InvalidArgumentException('Já existe um módulo com este identificador.');
        }

        $product = $this->persistProduct($type, $data);

        return $this->productToArray($product);
    }

    public function updateCustomProduct(string $type, string $id, array $input): array
    {
        $type = RoadmapType::resolve($type);
        $product = $this->findProduct($type, $id);

        if (!$product) {
            throw new NotFoundException('Módulo do roadmap', $id);
        }

        $current = $this->productToArray($product);
        $merged = array_merge($current, array_intersect_key($input, array_flip([
            'title', 'description', 'icon', 'accent', 'isHidden',
        ])));

        $data = $this->normalizeProductInput(array_merge($merged, ['id' => $id]));

        if ($data['title'] === '') {
            throw new InvalidArgumentException('O título do módulo é obrigatório.');
        }

        $product->fill([
            'title' => $data['title'],
            'description' => $data['description'],
            'icon' => $data['icon'],
            'accent' => $data['accent'],
            'is_hidden' => $data['is_hidden'],
        ]);
        $product->save();

        return $this->productToArray($product->fresh());
    }

    public function deleteCustomProduct(string $type, string $id): void
    {
        $type = RoadmapType::resolve($type);
        $product = $this->findProduct($type, $id);

        if (!$product) {
            throw new NotFoundException('Módulo do roadmap', $id);
        }

        $this->deleteItemsByProduct($type, $id);
        $product->delete();
    }

    private function findItem(string $type, string $id): ?RoadmapItem
    {
        return RoadmapItem::query()
            ->where('roadmap_type', $type)
            ->where('id', $id)
            ->first();
    }

    private function findProduct(string $type, string $id): ?RoadmapCustomProduct
    {
        return RoadmapCustomProduct::query()
            ->where('roadmap_type', $type)
            ->where('id', $id)
            ->first();
    }

    private function upsertItem(string $type, array $input): void
    {
        $data = $this->normalizeItemInput($input);
        if ($data['title'] === '' || $data['product_id'] === '') {
            return;
        }

        $item = $this->findItem($type, $data['id']);
        if ($item) {
            $item->fill([
                'product_id' => $data['product_id'],
                'priority' => $data['priority'],
                'title' => $data['title'],
                'notes' => $data['notes'],
                'metrics' => $data['metrics'],
                'dev_status' => $data['dev_status'],
            ]);
            $item->save();

            return;
        }

        $this->persistItem($type, $data);
    }

    private function upsertCustomProduct(string $type, array $input): void
    {
        $data = $this->normalizeProductInput($input);
        if ($data['id'] === '' || $data['title'] === '') {
            return;
        }

        $product = $this->findProduct($type, $data['id']);
        if ($product) {
            $product->fill([
                'title' => $data['title'],
                'description' => $data['description'],
                'icon' => $data['icon'],
                'accent' => $data['accent'],
                'is_hidden' => $data['is_hidden'],
            ]);
            $product->save();

            return;
        }

        $this->persistProduct($type, $data);
    }

    private function persistItem(string $type, array $data): RoadmapItem
    {
        $item = new RoadmapItem([
            'roadmap_type' => $type,
            'id' => $data['id'],
            'product_id' => $data['product_id'],
            'priority' => $data['priority'],
            'title' => $data['title'],
            'notes' => $data['notes'],
            'metrics' => $data['metrics'],
            'dev_status' => $data['dev_status'],
            'item_created_at' => $data['item_created_at'],
        ]);
        $item->save();

        return $item;
    }

    private function persistProduct(string $type, array $data): RoadmapCustomProduct
    {
        $product = new RoadmapCustomProduct([
            'roadmap_type' => $type,
            'id' => $data['id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'icon' => $data['icon'],
            'accent' => $data['accent'],
            'is_hidden' => $data['is_hidden'],
            'product_created_at' => $data['product_created_at'],
        ]);
        $product->save();

        return $product;
    }

    private function trackDeletedSeed(string $type, string $seedId, bool $ignoreDuplicates): void
    {
        if ($seedId === '') {
            return;
        }

        $exists = RoadmapDeletedSeed::query()
            ->where('roadmap_type', $type)
            ->where('seed_id', $seedId)
            ->exists();

        if ($ignoreDuplicates && $exists) {
            return;
        }

        RoadmapDeletedSeed::query()->updateOrCreate(
            ['roadmap_type' => $type, 'seed_id' => $seedId],
            ['roadmap_type' => $type, 'seed_id' => $seedId],
        );
    }

    private function normalizeItemInput(array $input): array
    {
        $priority = $input['priority'] ?? 'media';

        if (!in_array($priority, self::PRIORITIES, true)) {
            throw new InvalidArgumentException('Prioridade inválida.');
        }

        $productId = (string) ($input['productId'] ?? $input['product_id'] ?? '');
        if ($productId === '') {
            throw new InvalidArgumentException('O módulo do item é obrigatório.');
        }

        $devStatus = $input['devStatus'] ?? $input['dev_status'] ?? null;
        if ($devStatus !== null && !in_array($devStatus, self::DEV_STATUSES, true)) {
            throw new InvalidArgumentException('Status de desenvolvimento inválido.');
        }

        $metrics = $input['metrics'] ?? [];
        if (!is_array($metrics)) {
            $metrics = [];
        }

        $createdAt = $input['createdAt'] ?? $input['item_created_at'] ?? null;

        return [
            'id' => (string) ($input['id'] ?? ('rm-' . now()->timestamp . '-' . substr(bin2hex(random_bytes(3)), 0, 5))),
            'product_id' => $productId,
            'priority' => $priority,
            'title' => trim((string) ($input['title'] ?? '')),
            'notes' => trim((string) ($input['notes'] ?? '')),
            'metrics' => array_values($metrics),
            'dev_status' => $devStatus ?: null,
            'item_created_at' => $createdAt ? now()->parse($createdAt) : now(),
        ];
    }

    private function normalizeProductInput(array $input): array
    {
        $createdAt = $input['createdAt'] ?? $input['product_created_at'] ?? null;

        return [
            'id' => (string) ($input['id'] ?? ''),
            'title' => trim((string) ($input['title'] ?? '')),
            'description' => trim((string) ($input['description'] ?? 'Módulo cadastrado no roadmap.')),
            'icon' => (string) ($input['icon'] ?? 'pi pi-box'),
            'accent' => (string) ($input['accent'] ?? '#1e4fe0'),
            'is_hidden' => (bool) ($input['isHidden'] ?? $input['is_hidden'] ?? false),
            'product_created_at' => $createdAt ? now()->parse($createdAt) : now(),
        ];
    }

    private function itemToArray(RoadmapItem $item): array
    {
        return [
            'id' => $item->id,
            'productId' => $item->product_id,
            'priority' => $item->priority,
            'title' => $item->title,
            'notes' => $item->notes ?? '',
            'metrics' => $item->metrics ?? [],
            'devStatus' => $item->dev_status,
            'createdAt' => optional($item->item_created_at)->toIso8601String()
                ?? optional($item->created_at)->toIso8601String(),
        ];
    }

    private function productToArray(RoadmapCustomProduct $product): array
    {
        return [
            'id' => $product->id,
            'title' => $product->title,
            'description' => $product->description ?? '',
            'icon' => $product->icon ?? 'pi pi-box',
            'accent' => $product->accent ?? '#1e4fe0',
            'isHidden' => (bool) $product->is_hidden,
            'custom' => true,
            'createdAt' => optional($product->product_created_at)->toIso8601String()
                ?? optional($product->created_at)->toIso8601String(),
        ];
    }
}
