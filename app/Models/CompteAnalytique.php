<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompteAnalytique extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table='comptes_analytique';

    protected $keyType='string';
    public $incrementing=false;

    protected $fillable=[
        'axe_analytique_id',
        'parent_id',
        'code',
        'libelle',
        'date_debut',
        'date_fin',
        'actif',
        'metadonnees',
    ];


    protected $cats=[
        'date_debut'=>'date',
        'date_fin'=>'date',
        'actif'=>'boolean',
        'metadonnees'=>'array',
    ];

    public function axeAnalytique()
    {
        return $this->belongsTo(AxeAnalytique::class,'axe_analytique_id');
    }


    public function parent()
    {
        return $this->belongsTo(CompteAnalytique::class,'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(CompteAnalytique::class,'parent_id');
    }

    public function ligneEcritures()
    {
        return $this->hasMany(LigneEcriture::class,'compte_analytique_id');
    }

    public function lingePieceComptables()
    {
        return $this->hasMany(LignePieceComptable::class,'compte_analytique_id');
    }

    public function ligneBudgets()
    {
        return $this->hasMany(LigneBudget::class,'compte_analytique_id');
    }

}
