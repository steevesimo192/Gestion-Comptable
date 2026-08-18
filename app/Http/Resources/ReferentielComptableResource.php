<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferentielComptableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'code' => $this->code,
            'pays_code' => $this->pays_code,
            'description' => $this->description,
            'version' => $this->version,
            'date_mise_en_vigueur' => $this->date_mise_en_vigueur?->toDateString(),
            'date_fin_validite' => $this->date_fin_validite?->toDateString(),
            'actif' => (bool) $this->actif,

            // c'7 le  Formatage propre des dates pour les API (Format ISO 8601)
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
