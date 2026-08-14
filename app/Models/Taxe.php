<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxe extends Model
{
    //
    use HasFactory, HasUuids;

    protected $table = 'taxes';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'nom',
        'code',
        'type',
        'usage',
        'taux',
        'montant_fixe',
        'prix_taxe_incluse',
        'incluse_dans_base',
        'portee',
        'description',
        'actif',
        'date_debut',
        'date_fin',
        'regles',
        'compte_taxe_collectee_id',
        'compte_taxe_deductible_id',
        'compte_contrepartie_id',
    ];

    protected $casts = [
        'taux' => 'decimal:6',
        'montant_fixe' => 'decimal:4',
        'prix_taxe_incluse' => 'boolean',
        'incluse_dans_base' => 'boolean',
        'actif' => 'boolean',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'regles' => 'array',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

    public function compteTaxeCollectee()
    {
        return $this->belongsTo(Compte::class, 'compte_taxe_collectee_id', 'id');
    }

    public function compteTaxeDeductible()
    {
        return $this->belongsTo(Compte::class, 'compte_taxe_deductible_id', 'id');
    }

    public function compteContrepartie()
    {
        return $this->belongsTo(Compte::class, 'compte_contrepartie_id', 'id');
    }

    public function ligneEcritures()
    {
        return $this->hasMany(LigneEcriture::class, 'taxe_id', 'id');
    }

    public function lignePiecesComptables()
    {
        return $this->hasMany(LignePieceComptable::class, 'taxe_id', 'id');
    }

    public function ligneDeclarationsFiscales()
    {
        return $this->hasMany(LigneDeclarationFiscale::class, 'taxe_id', 'id');
    }

}
