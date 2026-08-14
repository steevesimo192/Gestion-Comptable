<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    //
      use HasFactory, HasUuids;

    protected $table = 'paiements';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'tiers_id',
        'compte_tresorerie_id',
        'devise_id',
        'ecriture_id',
        'numero',
        'sens',
        'mode',
        'date_paiement',
        'date_valeur',
        'montant',
        'taux_change',
        'frais',
        'reference',
        'statut',
        'memo',
        'montant_affecte',
        'solde_non_affecte',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'date_valeur' => 'date',
        'montant' => 'decimal:4',
        'taux_change' => 'decimal:10',
        'frais' => 'decimal:4',
        'montant_affecte' => 'decimal:4',
        'solde_non_affecte' => 'decimal:4',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

    public function tiers()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id', 'id');
    }

    public function compteTresorerie()
    {
        return $this->belongsTo(CompteTresorerie::class, 'compte_tresorerie_id', 'id');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'devise_id', 'id');
    }

    public function ecriture()
    {
        return $this->belongsTo(Ecriture::class, 'ecriture_id', 'id');
    }

    public function affectationsPaiements()
    {
        return $this->hasMany(AffectationPaiement::class, 'paiement_id', 'id');
    }

}
