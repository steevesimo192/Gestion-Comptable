<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Echeance extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table = 'echeances';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'piece_comptable_id',
        'date_echeance',
        'montant',
        'montant_regle',
        'statut',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'montant' => 'decimal:4',
        'montant_regle' => 'decimal:4',
    ];


    public function pieceComptable()
    {
        return $this->belongsTo(PieceComptable::class, 'piece_comptable_id');
    }

    public function affectationsPaiements()
    {
        return $this->hasMany(AffectationPaiement::class, 'echeance_id', 'id');
    }
}
