<?php

namespace App\Http\Requests\Api\V1\Establishment;

class StoreEstablishmentRequest extends EstablishmentRequest
{
    public function rules(): array
    {
        return [
            'id' => 'sometimes|string|max:40',
            'cnpj' => 'sometimes|nullable|string|max:20',
            'cpf' => 'sometimes|nullable|string|max:20',
            'fantasia' => 'sometimes|nullable|string|max:255',
            'razaoSocial' => 'required|string|max:255',
            'contactName' => 'sometimes|nullable|string|max:255',
            'kind' => 'sometimes|in:clinica,hospital',
            'projects' => 'sometimes|array',
            'projects.*' => 'in:saas,bpo',
            'stageId' => 'sometimes|nullable|in:inbox,qualificado,fremium_aceito,proposta_apresentada,onboardado_fremium,concluido,levantada_mao',
            'orderIndex' => 'sometimes|integer|min:0',
            'onboardingPhaseId' => 'sometimes|nullable|string|max:40',
            'onboardingOrderIndex' => 'sometimes|integer|min:0',
            'onboardingResponsavelId' => 'sometimes|nullable|in:mike,diego,carlinhos,matheus,pedro,clau,wendel,thiago,regina',
        ];
    }
}
