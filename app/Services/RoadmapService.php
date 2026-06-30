<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\RoadmapCustomProduct;
use App\Models\RoadmapDeletedSeed;
use App\Models\RoadmapItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoadmapService
{
    private const PRIORITIES = ['alta', 'media', 'baixa', 'perfumaria'];

    private const DEV_STATUSES = ['a_fazer', 'em_andamento', 'concluido'];

    public function getState(): array
    {
        return [
            'items' => RoadmapItem::query()
                ->orderBy('item_created_at')
                ->get()
                ->map(fn (RoadmapItem $item) => $this->itemToArray($item))
                ->values()
                ->all(),
            'customProducts' => RoadmapCustomProduct::query()
                ->orderBy('product_created_at')
                ->get()
                ->map(fn (RoadmapCustomProduct $product) => $this->productToArray($product))
                ->values()
                ->all(),
            'deletedSeedIds' => RoadmapDeletedSeed::query()
                ->pluck('seed_id')
                ->values()
                ->all(),
        ];
    }

    public function importState(array $payload): array
    {
        if (RoadmapItem::query()->exists()
            || RoadmapCustomProduct::query()->exists()
            || RoadmapDeletedSeed::query()->exists()) {
            throw new InvalidArgumentException('O roadmap já possui dados. Importação bloqueada para evitar sobrescrita.');
        }

        DB::transaction(function () use ($payload) {
            foreach ($payload['items'] ?? [] as $item) {
                $this->persistItem($this->normalizeItemInput($item));
            }

            foreach ($payload['customProducts'] ?? [] as $product) {
                $this->persistProduct($this->normalizeProductInput($product));
            }

            foreach ($payload['deletedSeedIds'] ?? [] as $seedId) {
                $this->trackDeletedSeed((string) $seedId, false);
            }
        });

        return $this->getState();
    }

    /**
     * Mescla estado enviado pelo cliente (upsert por id). Não apaga itens ausentes no payload.
     */
    public function syncState(array $payload): array
    {
        DB::transaction(function () use ($payload) {
            foreach ($payload['items'] ?? [] as $item) {
                $this->upsertItem($item);
            }

            foreach ($payload['customProducts'] ?? [] as $product) {
                $this->upsertCustomProduct($product);
            }

            foreach ($payload['deletedSeedIds'] ?? [] as $seedId) {
                $seedId = (string) $seedId;
                if ($seedId === '') {
                    continue;
                }

                $this->trackDeletedSeed($seedId, true);
                RoadmapItem::query()->whereKey($seedId)->delete();
            }
        });

        return $this->getState();
    }

    public function createItem(array $input): array
    {
        $data = $this->normalizeItemInput($input);

        if ($data['title'] === '') {
            throw new InvalidArgumentException('O título do item é obrigatório.');
        }

        if (RoadmapItem::query()->whereKey($data['id'])->exists()) {
            throw new InvalidArgumentException('Já existe um item com este identificador.');
        }

        $item = $this->persistItem($data);

        return $this->itemToArray($item);
    }

    public function updateItem(string $id, array $input): array
    {
        $item = RoadmapItem::query()->find($id);

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

    public function deleteItem(string $id): void
    {
        $item = RoadmapItem::query()->find($id);

        if (!$item) {
            throw new NotFoundException('Item do roadmap', $id);
        }

        if (str_starts_with($id, 'rm-seed-')) {
            $this->trackDeletedSeed($id, true);
        }

        $item->delete();
    }

    public function deleteItemsByProduct(string $productId): int
    {
        $items = RoadmapItem::query()
            ->where('product_id', $productId)
            ->get();

        $count = $items->count();

        foreach ($items as $item) {
            $this->deleteItem($item->id);
        }

        return $count;
    }

    public function createCustomProduct(array $input): array
    {
        $data = $this->normalizeProductInput($input);

        if ($data['id'] === '') {
            throw new InvalidArgumentException('O identificador do módulo é obrigatório.');
        }

        if ($data['title'] === '') {
            throw new InvalidArgumentException('O título do módulo é obrigatório.');
        }

        if (RoadmapCustomProduct::query()->whereKey($data['id'])->exists()) {
            throw new InvalidArgumentException('Já existe um módulo com este identificador.');
        }

        $product = $this->persistProduct($data);

        return $this->productToArray($product);
    }

    public function updateCustomProduct(string $id, array $input): array
    {
        $product = RoadmapCustomProduct::query()->find($id);

        if (!$product) {
            throw new NotFoundException('Módulo do roadmap', $id);
        }

        $current = $this->productToArray($product);
        $merged = array_merge($current, array_intersect_key($input, array_flip([
            'title', 'description', 'icon', 'accent',
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
        ]);
        $product->save();

        return $this->productToArray($product->fresh());
    }

    public function deleteCustomProduct(string $id): void
    {
        $product = RoadmapCustomProduct::query()->find($id);

        if (!$product) {
            throw new NotFoundException('Módulo do roadmap', $id);
        }

        $this->deleteItemsByProduct($id);
        $product->delete();
    }

    private function upsertItem(array $input): void
    {
        $data = $this->normalizeItemInput($input);
        if ($data['title'] === '' || $data['product_id'] === '') {
            return;
        }

        $item = RoadmapItem::query()->find($data['id']);
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

        $this->persistItem($data);
    }

    private function upsertCustomProduct(array $input): void
    {
        $data = $this->normalizeProductInput($input);
        if ($data['id'] === '' || $data['title'] === '') {
            return;
        }

        $product = RoadmapCustomProduct::query()->find($data['id']);
        if ($product) {
            $product->fill([
                'title' => $data['title'],
                'description' => $data['description'],
                'icon' => $data['icon'],
                'accent' => $data['accent'],
            ]);
            $product->save();

            return;
        }

        $this->persistProduct($data);
    }

    private function persistItem(array $data): RoadmapItem
    {
        $item = new RoadmapItem([
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

    private function persistProduct(array $data): RoadmapCustomProduct
    {
        $product = new RoadmapCustomProduct([
            'id' => $data['id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'icon' => $data['icon'],
            'accent' => $data['accent'],
            'product_created_at' => $data['product_created_at'],
        ]);
        $product->save();

        return $product;
    }

    private function trackDeletedSeed(string $seedId, bool $ignoreDuplicates): void
    {
        if ($seedId === '') {
            return;
        }

        if ($ignoreDuplicates && RoadmapDeletedSeed::query()->whereKey($seedId)->exists()) {
            return;
        }

        RoadmapDeletedSeed::query()->updateOrCreate(
            ['seed_id' => $seedId],
            ['seed_id' => $seedId],
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
            'description' => trim((string) ($input['description'] ?? 'Módulo cadastrado no roadmap de produto.')),
            'icon' => (string) ($input['icon'] ?? 'pi pi-box'),
            'accent' => (string) ($input['accent'] ?? '#1e4fe0'),
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
            'custom' => true,
            'createdAt' => optional($product->product_created_at)->toIso8601String()
                ?? optional($product->created_at)->toIso8601String(),
        ];
    }
}
