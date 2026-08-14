<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldeComptable extends Model
{
    //
     use HasFactory, HasUuids;

    protected $table = 'soldes_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    const CREATED_AT = null;

    protected $fillable = [
        'dossier_comptable_id',
        'exercice_comptable_id',
        'periode_comptable_id',
        'compte_id',
        'total_debit',
        'total_credit',
        'solde_debiteur',
        'solde_crediteur',
        'solde_normal',
        'solde_anormal',
    ];

    protected $casts = [
        'total_debit' => 'decimal:4',
        'total_credit' => 'decimal:4',
        'solde_debiteur' => 'decimal:4',
        'solde_crediteur' => 'decimal:4',
        'solde_normal' => 'decimal:4',
        'solde_anormal' => 'decimal:4',
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

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id', 'id');
    }
}
