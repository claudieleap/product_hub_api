<?php

namespace Database\Seeders;

use App\Services\RoadmapService;
use App\Support\RoadmapType;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class RoadmapSeeder extends Seeder
{
    /**
     * Popula o roadmap a partir de database/roadmap-state.json.
     *
     * O snapshot corresponde ao roadmap "saas" (default) — os tipos interno/bpo
     * começam vazios e são populados pela aplicação.
     *
     * Idempotente: usa RoadmapService::importState, que bloqueia a importação
     * quando o roadmap já possui dados — assim edições feitas pela aplicação
     * (via /roadmap/sync) nunca são sobrescritas em redeploys.
     */
    public function run(): void
    {
        $path = database_path('roadmap-state.json');

        if (! is_file($path)) {
            $this->command?->warn('roadmap-state.json não encontrado — seed do roadmap ignorado.');

            return;
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload)) {
            $this->command?->warn('roadmap-state.json inválido — seed do roadmap ignorado.');

            return;
        }

        try {
            $state = app(RoadmapService::class)->importState(RoadmapType::SAAS, $payload);

            $this->command?->info(sprintf(
                'Roadmap (saas) importado: %d itens, %d módulos custom, %d seeds removidos.',
                count($state['items'] ?? []),
                count($state['customProducts'] ?? []),
                count($state['deletedSeedIds'] ?? []),
            ));
        } catch (InvalidArgumentException $e) {
            $this->command?->info('Roadmap já possui dados — seed ignorado.');
        }
    }
}
