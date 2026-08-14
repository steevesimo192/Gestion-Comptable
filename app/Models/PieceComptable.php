<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PieceComptable extends Model
{
    //

     use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'pieces_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'tiers_id',
        'journal_id',
        'devise_id',
        'ecriture_id',
        'type',
        'numero',
        'reference_fournisseur',
        'date_piece',
        'date_comptable',
        'date_echeance',
        'statut',
        'taux_change',
        'sous_total_ht',
        'total_remise',
        'total_taxes',
        'total_ttc',
        'total_regle',
        'solde_du',
        'conditions_paiement',
        'notes',
        'cree_par_id',
        'valide_par_id',
        'valide_le',
    ];

    protected $casts = [
        'date_piece' => 'date',
        'date_comptable' => 'date',
        'date_echeance' => 'date',
        'taux_change' => 'decimal:10',
        'sous_total_ht' => 'decimal:4',
        'total_remise' => 'decimal:4',
        'total_taxes' => 'decimal:4',
        'total_ttc' => 'decimal:4',
        'total_regle' => 'decimal:4',
        'solde_du' => 'decimal:4',
        'valide_le' => 'datetime',
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

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id', 'id');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'devise_id', 'id');
    }

    public function ecriture()
    {
        return $this->belongsTo(Ecriture::class, 'ecriture_id', 'id');
    }

    public function lignePiecesComptables()
    {
        return $this->hasMany(LignePieceComptable::class, 'piece_comptable_id', 'id');
    }

    public function echeances()
    {
        return $this->hasMany(Echeance::class, 'piece_comptable_id', 'id');
    }

    public function affectationsPaiements()
    {
        return $this->hasMany(AffectationPaiement::class, 'piece_comptable_id', 'id');
    }

    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'piece_comptable_id', 'id');
    }

}
