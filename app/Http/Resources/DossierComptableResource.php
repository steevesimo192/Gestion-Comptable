<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DossierComptableResource extends JsonResource
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
            'entreprise_id' => $this->entreprise_id,
            'referentiel_comptable_id' => $this->referentiel_comptable_id,
            'devise_fonctionnelle_id' => $this->devise_fonctionnelle_id,
            'code' => $this->code,
            'libelle' => $this->libelle,
            'mois_debut_exercice' => $this->mois_debut_exercice,
            'fuseau_horaire' => $this->fuseau_horaire,
            'multi_devise' => (bool) $this->multi_devise,
            'comptabilite_analytique' => (bool) $this->comptabilite_analytique,
            'methode_arrondi' => $this->methode_arrondi,
            'statut' => $this->statut,
            'parametres' => $this->parametres,

            // ✅ Relations chargées de façon conditionnelle (whenLoaded) pour éviter le N+1
            'referentiel_comptable' => new ReferentielComptableResource(
                $this->whenLoaded('referentielComptable')
            ),
            'devise_fonctionnelle' => new DeviseResource(
                $this->whenLoaded('deviseFonctionnelle')
            ),

            // c'7 le Formatage propre des dates pour les API (Format ISO 8601)
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
