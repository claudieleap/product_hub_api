<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Semeia os estabelecimentos reais do quadro de Onboarding (mesmos dados de
 * onboardingSeed.js no frontend, que até aqui só existiam em localStorage).
 * Idempotente por id — mesmo padrão de seed_auth_users.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $cards = [
            ['id' => 'onb-ortodoc', 'phase' => 'backlog', 'order' => 0, 'name' => 'Ortodoc', 'kind' => 'clinica', 'projects' => ['saas'], 'responsavel' => 'thiago'],
            ['id' => 'onb-cian', 'phase' => 'backlog', 'order' => 1, 'name' => 'Cian', 'kind' => 'clinica', 'projects' => ['bpo'], 'responsavel' => 'diego'],

            ['id' => 'onb-pronto-baby', 'phase' => 'onboarding', 'order' => 0, 'name' => 'Pronto Baby', 'kind' => 'clinica', 'projects' => ['bpo'], 'responsavel' => 'carlinhos'],
            ['id' => 'onb-dimeg', 'phase' => 'onboarding', 'order' => 1, 'name' => 'Dimeg', 'kind' => 'clinica', 'projects' => ['saas'], 'responsavel' => 'clau'],
            ['id' => 'onb-santa-casa-cruzeiro', 'phase' => 'onboarding', 'order' => 2, 'name' => 'Santa Casa Cruzeiro', 'kind' => 'hospital', 'projects' => ['bpo'], 'responsavel' => 'mike'],
            ['id' => 'onb-santa-casa-ourinhos', 'phase' => 'onboarding', 'order' => 3, 'name' => 'Santa Casa Ourinhos', 'kind' => 'hospital', 'projects' => ['bpo'], 'responsavel' => 'matheus'],
            ['id' => 'onb-inep', 'phase' => 'onboarding', 'order' => 4, 'name' => 'INEP', 'kind' => 'clinica', 'projects' => ['saas', 'bpo'], 'responsavel' => 'wendel'],
            ['id' => 'onb-hoc', 'phase' => 'onboarding', 'order' => 5, 'name' => 'HOC Oswaldo Cruz', 'kind' => 'hospital', 'projects' => ['bpo'], 'responsavel' => 'diego'],
            ['id' => 'onb-rm', 'phase' => 'onboarding', 'order' => 6, 'name' => 'RM', 'kind' => 'clinica', 'projects' => ['saas'], 'responsavel' => 'regina'],

            ['id' => 'onb-cotrel', 'phase' => 'golive', 'order' => 0, 'name' => 'Cotrel', 'kind' => 'clinica', 'projects' => ['saas', 'bpo'], 'responsavel' => 'pedro'],
        ];

        $existing = DB::table('establishments')->pluck('id')->all();
        $existing = array_flip($existing);

        foreach ($cards as $card) {
            if (isset($existing[$card['id']])) {
                continue;
            }

            DB::table('establishments')->insert([
                'id' => $card['id'],
                'fantasia' => $card['name'],
                'kind' => $card['kind'],
                'projects' => json_encode($card['projects']),
                'stage_id' => null,
                'order_index' => 0,
                'onboarding_phase_id' => $card['phase'],
                'onboarding_order_index' => $card['order'],
                'onboarding_responsavel_id' => $card['responsavel'],
                'observacao' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Matriz de exemplo da Cotrel (única com unidades/convênios/células preenchidos).
        if (! DB::table('establishment_units')->where('id', 'un-1')->exists()) {
            DB::table('establishment_units')->insert([
                ['id' => 'un-1', 'establishment_id' => 'onb-cotrel', 'name' => 'Unidade 1', 'order_index' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 'un-2', 'establishment_id' => 'onb-cotrel', 'name' => 'Unidade 2', 'order_index' => 1, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('establishment_convenios')->insert([
                ['id' => 'cv-unimed', 'establishment_id' => 'onb-cotrel', 'name' => 'Unimed', 'order_index' => 0, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 'cv-bradesco', 'establishment_id' => 'onb-cotrel', 'name' => 'Bradesco Saúde', 'order_index' => 1, 'created_at' => $now, 'updated_at' => $now],
                ['id' => 'cv-sulamerica', 'establishment_id' => 'onb-cotrel', 'name' => 'SulAmérica', 'order_index' => 2, 'created_at' => $now, 'updated_at' => $now],
            ]);

            $op = fn (string $status) => ['status' => $status, 'descricao' => ''];

            DB::table('establishment_cells')->insert([
                [
                    'establishment_id' => 'onb-cotrel', 'convenio_id' => 'cv-unimed', 'unit_id' => 'un-1',
                    'ativo' => true, 'modo' => 'portal',
                    'ops' => json_encode(['fat' => $op('feito'), 'dp' => $op('feito'), 'dc' => $op('parcial')]),
                    'created_at' => $now, 'updated_at' => $now,
                ],
                [
                    'establishment_id' => 'onb-cotrel', 'convenio_id' => 'cv-unimed', 'unit_id' => 'un-2',
                    'ativo' => true, 'modo' => 'portal',
                    'ops' => json_encode(['fat' => $op('feito'), 'dp' => $op('parcial'), 'dc' => $op('pendente')]),
                    'created_at' => $now, 'updated_at' => $now,
                ],
                [
                    'establishment_id' => 'onb-cotrel', 'convenio_id' => 'cv-bradesco', 'unit_id' => 'un-1',
                    'ativo' => true, 'modo' => 'fora',
                    'ops' => json_encode(['fat' => $op('feito'), 'dp' => $op('feito'), 'dc' => $op('feito')]),
                    'created_at' => $now, 'updated_at' => $now,
                ],
                [
                    'establishment_id' => 'onb-cotrel', 'convenio_id' => 'cv-sulamerica', 'unit_id' => 'un-1',
                    'ativo' => true, 'modo' => 'portal',
                    'ops' => json_encode(['fat' => $op('parcial'), 'dp' => $op('pendente'), 'dc' => $op('pendente')]),
                    'created_at' => $now, 'updated_at' => $now,
                ],
            ]);
        }
    }

    public function down(): void
    {
        $ids = [
            'onb-ortodoc', 'onb-cian', 'onb-pronto-baby', 'onb-dimeg', 'onb-santa-casa-cruzeiro',
            'onb-santa-casa-ourinhos', 'onb-inep', 'onb-hoc', 'onb-rm', 'onb-cotrel',
        ];

        DB::table('establishments')->whereIn('id', $ids)->delete();
    }
};
