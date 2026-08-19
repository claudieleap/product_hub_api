<?php

namespace App\Support;

class RoadmapMetricsDefaults
{
    /**
     * @return array<int, array{id: string, label: string, metrics: array<int, array{id: string, label: string}>}>
     */
    public static function groups(): array
    {
        return [
            [
                'id' => 'adocao',
                'label' => 'Adoção & uso',
                'metrics' => [
                    ['id' => 'rm-m-adocao-onboarding', 'label' => 'Onboarding concluído'],
                    ['id' => 'rm-m-adocao-1a-conciliacao', 'label' => 'Tempo até primeira conciliação'],
                    ['id' => 'rm-m-adocao-mau', 'label' => 'Usuários ativos semanais (WAU — clínica)'],
                    ['id' => 'rm-m-adocao-portal', 'label' => 'Profissionais no portal'],
                ],
            ],
            [
                'id' => 'conciliacao',
                'label' => 'Conciliação',
                'metrics' => [
                    ['id' => 'rm-m-conc-tempo', 'label' => 'Tempo de conciliação'],
                    ['id' => 'rm-m-conc-match', 'label' => 'Match automático faturado × recebido'],
                    ['id' => 'rm-m-conc-gap', 'label' => 'Gap faturado × pago (diferença não conciliada)'],
                    ['id' => 'rm-m-conc-dashboard', 'label' => 'Uso do dashboard financeiro'],
                ],
            ],
            [
                'id' => 'recebimento',
                'label' => 'Recebimento & faturamento',
                'metrics' => [
                    ['id' => 'rm-m-rec-imports', 'label' => 'Importações processadas por mês'],
                    ['id' => 'rm-m-rec-sla', 'label' => 'Tempo upload → conciliação'],
                    ['id' => 'rm-m-rec-operadoras', 'label' => 'Operadoras com retorno ativo'],
                ],
            ],
            [
                'id' => 'glosas',
                'label' => 'Glosas',
                'metrics' => [
                    ['id' => 'rm-m-glo-taxa', 'label' => 'Taxa de glosa'],
                    ['id' => 'rm-m-glo-recuperado', 'label' => 'Valor recuperado por mês'],
                    ['id' => 'rm-m-glo-pendentes', 'label' => 'Glosas pendentes de recurso'],
                ],
            ],
            [
                'id' => 'antecipacao',
                'label' => 'Antecipação',
                'metrics' => [
                    ['id' => 'rm-m-ant-clinicas', 'label' => 'Clínicas que anteciparam'],
                    ['id' => 'rm-m-ant-profissionais', 'label' => 'Profissionais que anteciparam'],
                    ['id' => 'rm-m-ant-volume', 'label' => 'Volume antecipado'],
                    ['id' => 'rm-m-ant-recorrencia', 'label' => 'Recorrência de antecipação'],
                    ['id' => 'rm-m-ant-elegivel', 'label' => '% do elegível utilizado (recebíveis disponíveis)'],
                    ['id' => 'rm-m-ant-retencao', 'label' => 'Retenção: quem antecipa × quem não'],
                ],
            ],
            [
                'id' => 'portal',
                'label' => 'Portal do Médico',
                'metrics' => [
                    ['id' => 'rm-m-portal-ativos', 'label' => 'Profissionais ativos no portal'],
                    ['id' => 'rm-m-portal-extrato', 'label' => 'Acessos semanais ao extrato'],
                ],
            ],
            [
                'id' => 'negocio',
                'label' => 'Negócio & retenção',
                'metrics' => [
                    ['id' => 'rm-m-neg-grr', 'label' => 'GRR (retenção de receita)'],
                    ['id' => 'rm-m-neg-nrr', 'label' => 'NRR (retenção líquida de receita)'],
                    ['id' => 'rm-m-neg-dso', 'label' => 'DSO (dias de recebimento)'],
                    ['id' => 'rm-m-neg-expand', 'label' => 'SaaS → BPO (migração para back-office gerenciado)'],
                    ['id' => 'rm-m-neg-pmf', 'label' => 'Muito decepcionado sem a Aleevia (teste Sean Ellis)'],
                    ['id' => 'rm-m-neg-margem-bpo', 'label' => 'Margem do BPO (back-office gerenciado)'],
                ],
            ],
        ];
    }
}
