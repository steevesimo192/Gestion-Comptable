<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReleveBancaire extends Model
{
    //
      use HasFactory, HasUuids;

    protected $table = 'releves_bancaires';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'compte_tresorerie_id',
        'reference',
        'date_debut',
        'date_fin',
        'solde_ouverture',
        'solde_cloture',
        'statut',
        'rapproche_le',
        'rapproche_par_id',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'solde_ouverture' => 'decimal:4',
        'solde_cloture' => 'decimal:4',
        'rapproche_le' => 'datetime',
    ];

    // -------------------- Relations --------------------

    public function compteTresorerie()
    {
        return $this->belongsTo(CompteTresorerie::class, 'compte_tresorerie_id', 'id');
    }

    public function operationsBancaires()
    {
        return $this->hasMany(OperationBancaire::class, 'releve_bancaire_id', 'id');
    }

}
