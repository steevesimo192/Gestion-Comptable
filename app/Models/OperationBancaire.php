<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationBancaire extends Model
{
    //

     use HasFactory, HasUuids;

    protected $table = 'operations_bancaires';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'releve_bancaire_id',
        'date_operation',
        'date_valeur',
        'libelle',
        'reference',
        'montant',
        'empreinte_import',
        'statut',
        'donnees_importees',
        'montant_rapproche',
        'solde_a_rapprocher',
    ];

    protected $casts = [
        'date_operation' => 'date',
        'date_valeur' => 'date',
        'montant' => 'decimal:4',
        'donnees_importees' => 'array',
         'montant_rapproche' => 'decimal:4',
        'solde_a_rapprocher' => 'decimal:4',
    ];

    // -------------------- Relations --------------------

    public function releveBancaire()
    {
        return $this->belongsTo(ReleveBancaire::class, 'releve_bancaire_id', 'id');
    }

    public function rapprochementsBancaires()
    {
        return $this->hasMany(RapprochementBancaire::class, 'operation_bancaire_id', 'id');
    }

}
