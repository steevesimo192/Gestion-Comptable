<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ecriture extends Model
{
    //
      use HasFactory, HasUuids;

    protected $table = 'ecritures';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'exercice_comptable_id',
        'periode_comptable_id',
        'journal_id',
        'ecriture_extournee_id',
        'date_comptable',
        'libelle',
        'statut',
        'numero',
        'date_piece',
        'numero_piece',
        'date_echeance',
        'source_type',
        'source_id',
        'reference_externe',
        'total_debit',
        'total_credit',
        'total_debit_fonctionnel',
        'total_credit_fonctionnel',
        'observation',
        'cree_par_id',
        'valide_par_id',
        'valide_le',
        'comptabilise_par_id',
        'comptabilise_le',
        'annule_par_id',
        'annule_le',
        'motif_annulation',
        'empreinte',
        'version',
    ];

    protected $casts = [
        'date_comptable' => 'date',
        'date_piece' => 'date',
        'date_echeance' => 'date',
        'total_debit' => 'decimal:4',
        'total_credit' => 'decimal:4',
        'total_debit_fonctionnel' => 'decimal:4',
        'total_credit_fonctionnel' => 'decimal:4',
        'valide_le' => 'datetime',
        'comptabilise_le' => 'datetime',
        'annule_le' => 'datetime',
        'version' => 'integer',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_comptable_id', 'id');
    }

    public function periodeComptable()
    {
        return $this->belongsTo(PeriodeComptable::class, 'periode_comptable_id', 'id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id', 'id');
    }

    public function ecritureExtournee()
    {
        return $this->belongsTo(Ecriture::class, 'ecriture_extournee_id', 'id');
    }

    public function extournees()
    {
        return $this->hasMany(Ecriture::class, 'ecriture_extournee_id', 'id');
    }

    public function ligneEcritures()
    {
        return $this->hasMany(LigneEcriture::class, 'ecriture_id', 'id');
    }

    public function piecesComptables()
    {
        return $this->hasMany(PieceComptable::class, 'ecriture_id', 'id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'ecriture_id', 'id');
    }

    public function amortissements()
    {
        return $this->hasMany(Amortissement::class, 'ecriture_id', 'id');
    }

}
