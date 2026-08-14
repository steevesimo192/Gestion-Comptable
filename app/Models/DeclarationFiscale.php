<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeclarationFiscale extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table='declarations_fiscales';

    protected $keyType = 'string';
    public $incrementing = false;


    protected $fillable = [
       'dossier_comptable_id',
        'exercice_comptable_id',
        'type',
        'numero',
        'periode_debut',
        'periode_fin',
        'date_echeance',
        'base_imposable',
        'taxe_collectee',
        'taxe_deductible',
        'credit_anterieur',
        'montant_du',
        'statut',
        'deposee_le',
        'accuse_reception',
        'credit_reportable'
    ];


    protected $casts=[
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'date_echeance' => 'date',
        'base_imposable' => 'decimal:4',
        'taxe_collectee' => 'decimal:4',
        'taxe_deductible' => 'decimal:4',
        'credit_anterieur' => 'decimal:4',
        'montant_du' => 'decimal:4',
        'deposee_le' => 'datetime',
          'credit_reportable' => 'decimal:4',
    ];

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class,'dossier_comptable_id');
    }

    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class,'exercice_comptable_id');
    }

    public function lignesDeclarationFiscale()
    {
        return $this->hasMany(LigneDeclarationFiscale::class,'declaration_fiscale_id');
    }


}
