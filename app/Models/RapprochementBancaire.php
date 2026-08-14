<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RapprochementBancaire extends Model
{
    //
      use HasFactory, HasUuids;

    protected $table = 'rapprochements_bancaires';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'operation_bancaire_id',
        'ligne_ecriture_id',
        'montant_rapproche',
        'ecart',
        'rapproche_le',
        'rapproche_par_id',
    ];

    protected $casts = [
        'montant_rapproche' => 'decimal:4',
        'ecart' => 'decimal:4',
        'rapproche_le' => 'datetime',
    ];

    // -------------------- Relations --------------------

    public function operationBancaire()
    {
        return $this->belongsTo(OperationBancaire::class, 'operation_bancaire_id', 'id');
    }

    public function ligneEcriture()
    {
        return $this->belongsTo(LigneEcriture::class, 'ligne_ecriture_id', 'id');
    }

}
