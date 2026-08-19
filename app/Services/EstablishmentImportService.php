<?php

namespace App\Services;

use App\Models\Establishment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importa a base de credenciados (formato Sulamérica: UF, MUNICIPIO,
 * ESPECIALIDADE, FANTASIA, RAZAO_SOCIAL, CLASSIFICACAO, GRUPO_ECON, CNPJ_CPF,
 * BAIRRO, ENDERECO, NUM_ENDERECO, COMPLEMENTO, CEP, DDD, TELEFONE, EMAIL,
 * DIVULGACAO, NAT_ND, PF_PJ) filtrando pro Vale do Paraíba e agrupando por CNPJ
 * (uma linha por prestador × especialidade na planilha → um estabelecimento com
 * a lista de especialidades). Quem já existe (por cnpj_cpf) é pulado.
 */
class EstablishmentImportService
{
    private const VALE_DO_PARAIBA_CIDADES = [
        'SAO JOSE DOS CAMPOS', 'TAUBATE', 'JACAREI', 'CRUZEIRO', 'LORENA',
        'GUARATINGUETA', 'PINDAMONHANGABA', 'CACAPAVA', 'SAO SEBASTIAO',
        'CAMPOS DO JORDAO', 'CARAGUATATUBA', 'CACHOEIRA PAULISTA', 'ILHABELA',
        'UBATUBA', 'TREMEMBE', 'SAO BENTO DO SAPUCAI', 'SANTA BRANCA',
        'APARECIDA', 'CANAS', 'ROSEIRA',
    ];

    public function import(UploadedFile $file): array
    {
        // Planilhas de credenciados chegam a ~1M linhas — o reader padrão do
        // PhpSpreadsheet carrega estilo/formatação por célula e estoura o
        // memory_limit default do PHP. setReadDataOnly ignora isso (só valores).
        ini_set('memory_limit', '2048M');
        set_time_limit(900);

        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();

        $providers = [];

        foreach ($sheet->getRowIterator(2) as $row) {
            $cells = [];
            foreach ($row->getCellIterator('A', 'S') as $cell) {
                $cells[] = trim((string) $cell->getFormattedValue());
            }

            [$uf, $municipio, $especialidade, $fantasia, $razaoSocial, $classificacao,
                $grupoEcon, $cnpjCpf, $bairro, $endereco, $numEndereco, $complemento,
                $cep, $ddd, $telefone, $email, $divulgacao, $natNd, $pfPj] = array_pad($cells, 19, '');

            if (! $this->normalize($municipio) || ! in_array($this->normalize($municipio), self::VALE_DO_PARAIBA_CIDADES, true)) {
                continue;
            }

            $cnpjCpf = preg_replace('/\D/', '', $cnpjCpf);
            if ($cnpjCpf === '') {
                continue;
            }

            if (! isset($providers[$cnpjCpf])) {
                $providers[$cnpjCpf] = [
                    'cnpj_cpf' => $cnpjCpf,
                    'fantasia' => $fantasia,
                    'razao_social' => $razaoSocial,
                    'classificacao' => $classificacao,
                    'grupo_econ' => $grupoEcon,
                    'municipio' => $municipio,
                    'uf' => $uf,
                    'bairro' => $bairro,
                    'endereco' => $endereco,
                    'num_endereco' => $numEndereco,
                    'complemento' => $complemento,
                    'cep' => $cep,
                    'ddd' => $ddd,
                    'telefone' => $telefone,
                    'email' => $email,
                    'divulgacao' => $divulgacao,
                    'nat_nd' => $natNd,
                    'pf_pj' => $pfPj,
                    'especialidades' => [],
                ];
            }

            if ($especialidade && ! in_array($especialidade, $providers[$cnpjCpf]['especialidades'], true)) {
                $providers[$cnpjCpf]['especialidades'][] = $especialidade;
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $sheet);

        $existing = Establishment::whereIn('cnpj_cpf', array_keys($providers))
            ->pluck('cnpj_cpf')
            ->all();
        $existing = array_flip($existing);

        $now = now();
        $rows = [];
        $skipped = 0;

        foreach ($providers as $cnpj => $provider) {
            if (isset($existing[$cnpj])) {
                $skipped++;
                continue;
            }

            $rows[] = [
                'id' => $cnpj,
                'cnpj_cpf' => $cnpj,
                'fantasia' => $provider['fantasia'],
                'razao_social' => $provider['razao_social'],
                'kind' => str_contains($provider['classificacao'], 'HOSPITAL') ? 'hospital' : 'clinica',
                'projects' => json_encode([]),
                'especialidades' => json_encode($provider['especialidades'], JSON_UNESCAPED_UNICODE),
                'classificacao' => $provider['classificacao'],
                'grupo_econ' => $provider['grupo_econ'],
                'municipio' => $provider['municipio'],
                'uf' => $provider['uf'],
                'bairro' => $provider['bairro'],
                'endereco' => $provider['endereco'],
                'num_endereco' => $provider['num_endereco'],
                'complemento' => $provider['complemento'],
                'cep' => $provider['cep'],
                'ddd' => $provider['ddd'],
                'telefone' => $provider['telefone'],
                'email' => $provider['email'],
                'divulgacao' => $provider['divulgacao'],
                'nat_nd' => $provider['nat_nd'],
                'pf_pj' => $provider['pf_pj'],
                'stage_id' => 'inbox',
                'order_index' => 0,
                'onboarding_order_index' => 0,
                'observacao' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('establishments')->insert($chunk);
        }

        return [
            'imported' => count($rows),
            'skipped' => $skipped,
            'total' => count($providers),
        ];
    }

    private function normalize(string $value): string
    {
        $value = strtoupper($value);
        $value = strtr($value, [
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
            'É' => 'E', 'Ê' => 'E',
            'Í' => 'I',
            'Ó' => 'O', 'Õ' => 'O', 'Ô' => 'O',
            'Ú' => 'U',
            'Ç' => 'C',
        ]);

        return trim($value);
    }
}
