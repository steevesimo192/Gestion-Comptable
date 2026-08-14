<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffectationPaiement extends Model
{
    //
    use HasFactory,HasUuids;
    protected $table= 'affectations_paiements';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable =[
        'paiement_id',
        'piece_comptable_id',
        'echeance_id',
        'montant',
        'ecart_change',
        'escompte',
        'affecte_le',
    ];

    protected $casts = [
        'montant' => 'decimal:4',
        'ecart_change' => 'decimal:4',
        'escompte' => 'decimal:4',
        'affecte_le' => 'datetime',
    ];

    public function paiement(){
        return $this->belongsTo(Paiement::class,'paiement_id');
    }
    public function pieceComptable(){
        return $this->belongsTo(PieceComptable::class,'piece_comptable_id');
    }
    public function echeance(){
        return $this->belongsTo(Echeance::class,'echeance_id');
    }
}
