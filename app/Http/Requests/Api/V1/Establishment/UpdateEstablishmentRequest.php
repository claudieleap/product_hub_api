<?php

namespace App\Http\Requests\Api\V1\Establishment;

class UpdateEstablishmentRequest extends EstablishmentRequest
{
    public function rules(): array
    {
        return [
            'cnpjCpf' => 'sometimes|nullable|string|max:20',
            'fantasia' => 'sometimes|nullable|string|max:255',
            'razaoSocial' => 'sometimes|nullable|string|max:255',
            'kind' => 'sometimes|in:clinica,hospital',
            'projects' => 'sometimes|array',
            'projects.*' => 'in:saas,bpo',
            'especialidades' => 'sometimes|array',
            'especialidades.*' => 'string|max:255',
            'classificacao' => 'sometimes|nullable|in:DIAGNOSTICA,TERAPEUTICA,CLINICA,PRONTO SOCORRO,HOSPITAL,HOSPITAL DIA,ANESTESIA',
            'grupoEcon' => 'sometimes|nullable|string|max:255',
            'municipio' => 'sometimes|nullable|string|max:120',
            'uf' => 'sometimes|nullable|string|max:2',
            'bairro' => 'sometimes|nullable|string|max:120',
            'endereco' => 'sometimes|nullable|string|max:255',
            'numEndereco' => 'sometimes|nullable|string|max:20',
            'complemento' => 'sometimes|nullable|string|max:120',
            'cep' => 'sometimes|nullable|string|max:15',
            'ddd' => 'sometimes|nullable|string|max:5',
            'telefone' => 'sometimes|nullable|string|max:30',
            'email' => 'sometimes|nullable|string|max:255',
            'divulgacao' => 'sometimes|nullable|in:DIVULGADO,NAO DIVULGADO',
            'natNd' => 'sometimes|nullable|in:-,NAT ND',
            'pfPj' => 'sometimes|nullable|in:F,J',
            'stageId' => 'sometimes|nullable|in:inbox,qualificado,fremium_aceito,proposta_apresentada,onboardado_fremium,levantada_mao',
            'orderIndex' => 'sometimes|integer|min:0',
            'onboardingPhaseId' => 'sometimes|nullable|string|max:40',
            'onboardingOrderIndex' => 'sometimes|integer|min:0',
            'onboardingResponsavelId' => 'sometimes|nullable|in:mike,diego,carlinhos,matheus,pedro,clau,wendel,thiago,regina',
            'observacao' => 'sometimes|nullable|string',
        ];
    }
}
