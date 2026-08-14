<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Immobilisation extends Model
{
    //
    use HasFactory, HasUuids;

    protected $table = 'immobilisations';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'categorie_immobilisation_id',
        'tiers_id',
        'piece_comptable_id',
        'devise_id',
        'code',
        'libelle',
        'date_acquisition',
        'date_mise_en_service',
        'cout_acquisition',
        'valeur_residuelle',
        'valeur_nette_comptable',
        'duree_mois',
        'methode_amortissement',
        'statut',
        'date_sortie',
        'prix_cession',
        'metadonnees',
        'amortissement_cumule',
    ];

    protected $casts = [
        'date_acquisition' => 'date',
        'date_mise_en_service' => 'date',
        'cout_acquisition' => 'decimal:4',
        'valeur_residuelle' => 'decimal:4',
        'valeur_nette_comptable' => 'decimal:4',
        'duree_mois' => 'integer',
        'date_sortie' => 'date',
        'prix_cession' => 'decimal:4',
        'metadonnees' => 'array',
        'amortissement_cumule' => 'decimal:4',
    ];

    // -------------------- Relations --------------------

    public function categorieImmobilisation()
    {
        return $this->belongsTo(CategorieImmobilisation::class, 'categorie_immobilisation_id', 'id');
    }

    public function tiers()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id', 'id');
    }

    public function pieceComptable()
    {
        return $this->belongsTo(PieceComptable::class, 'piece_comptable_id', 'id');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'devise_id', 'id');
    }

    public function amortissements()
    {
        return $this->hasMany(Amortissement::class, 'immobilisation_id', 'id');
    }
}
