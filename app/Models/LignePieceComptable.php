<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LignePieceComptable extends Model
{
    //

     use HasFactory, HasUuids;

    protected $table = 'ligne_pieces_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'piece_comptable_id',
        'compte_id',
        'taxe_id',
        'compte_analytique_id',
        'article_id',
        'ordre',
        'description',
        'quantite',
        'unite',
        'prix_unitaire',
        'taux_remise',
        'montant_remise',
        'base_hors_taxe',
        'montant_taxe',
        'montant_ttc',
        'dimensions',
    ];

    protected $casts = [
        'ordre' => 'integer',
        'quantite' => 'decimal:6',
        'prix_unitaire' => 'decimal:4',
        'taux_remise' => 'decimal:6',
        'montant_remise' => 'decimal:4',
        'base_hors_taxe' => 'decimal:4',
        'montant_taxe' => 'decimal:4',
        'montant_ttc' => 'decimal:4',
        'dimensions' => 'array',
    ];

    // -------------------- Relations --------------------

    public function pieceComptable()
    {
        return $this->belongsTo(PieceComptable::class, 'piece_comptable_id', 'id');
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id', 'id');
    }

    public function taxe()
    {
        return $this->belongsTo(Taxe::class, 'taxe_id', 'id');
    }

    public function compteAnalytique()
    {
        return $this->belongsTo(CompteAnalytique::class, 'compte_analytique_id', 'id');
    }

}
