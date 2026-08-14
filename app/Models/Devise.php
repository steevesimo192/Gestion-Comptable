<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devise extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table='devises';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'nom',
        'symbole',
        'precision',
        'position_symbole',
        'actif'
    ];

     protected $casts = [
        'precision' => 'integer',
        'actif' => 'boolean',
    ];


    public function tauxChangesDeviseSource()
    {
        return $this->hasMany(TauxChange::class,'devise_source_id');
    }

    public function tauxChangesDeviseCible()
    {
        return $this->hasMany(TauxChange::class,'devise_cible_id');
    }

    public function dossiersComptables()
    {
        return $this->hasMany(DossierComptable::class,'devise_id');
    }

    public function journals()
    {
        return $this->hasMany(Journal::class, 'devise_id', 'id');
    }

    public function comptes()
    {
        return $this->hasMany(Compte::class, 'devise_id', 'id');
    }

    public function exerciceComptables()
    {
        return $this->hasMany(ExerciceComptable::class, 'devise_id', 'id');
    }

    public function tiers()
    {
        return $this->hasMany(Tiers::class, 'devise_id', 'id');
    }

    public function ligneEcritures()
    {
        return $this->hasMany(LigneEcriture::class, 'devise_id', 'id');
    }

    public function piecesComptables()
    {
        return $this->hasMany(PieceComptable::class, 'devise_id', 'id');
    }

    public function comptesTresorerie()
    {
        return $this->hasMany(CompteTresorerie::class, 'devise_id', 'id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'devise_id', 'id');
    }

    public function immobilisations()
    {
        return $this->hasMany(Immobilisation::class, 'devise_id', 'id');
    }

}
