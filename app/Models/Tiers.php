<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tiers extends Model
{
    //

    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'tiers';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'reference_externe',
        'code',
        'type',
        'nom_affichage',
        'numero_fiscal',
        'pays_code',
        'devise_id',
        'compte_collectif_id',
        'delai_paiement_jours',
        'limite_credit',
        'actif',
        'metadonnees',
    ];

    protected $casts = [
        'delai_paiement_jours' => 'integer',
        'limite_credit' => 'decimal:4',
        'actif' => 'boolean',
        'metadonnees' => 'array',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'devise_id', 'id');
    }

    public function compteCollectif()
    {
        return $this->belongsTo(Compte::class, 'compte_collectif_id', 'id');
    }

    public function coordonneesBancairesTiers()
    {
        return $this->hasMany(CoordonneesBancaireTiers::class, 'tiers_id', 'id');
    }

    public function ligneEcritures()
    {
        return $this->hasMany(LigneEcriture::class, 'tiers_id', 'id');
    }

    public function piecesComptables()
    {
        return $this->hasMany(PieceComptable::class, 'tiers_id', 'id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'tiers_id', 'id');
    }

    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'tiers_id', 'id');
    }

    public function lettrages()
    {
        return $this->hasMany(Lettrage::class, 'tiers_id', 'id');
    }

}
