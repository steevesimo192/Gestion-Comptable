<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    //
    use HasFactory,HasUuids;
    protected $table='comptes';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable=[
        'referentiel_comptable_id',
        'dossier_comptable_id',
        'parent_id',
        'numero',
        'intitule',
        'classe',
        'type',
        'nature',
        'categorie',
        'lettrable',
        'rapprochable',
        'accepte_ecritures',
        'auxiliaire',
        'devise_id',
        'code_reporting',
        'description',
        'actif',
        'autorise_solde_inverse',
    ];

    protected $casts = [
        'lettrable' => 'boolean',
        'rapprochable' => 'boolean',
        'accepte_ecritures' => 'boolean',
        'auxiliaire' => 'boolean',
        'actif' => 'boolean',
        'autorise_solde_inverse' => 'boolean',
    ];


    public function referentielComptable()
    {
        return $this->belongsTo(ReferentielComptable::class);
    }
    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class);
    }
    public function parent()
    {
        return $this->belongsTo(Compte::class, 'parent_id');
    }
    public function devise()
    {
        return $this->belongsTo(Devise::class);
    }

    public function taxeCompteTaxeCollectee(){
        return $this->hasMany(Taxe::class,'compte_id');
    }

    public function taxeCompteTaxeDeductible(){
        return $this->hasMany(Taxe::class,'compte_id');
    }
    public function taxeCompteTaxeContrepartie(){
        return $this->hasMany(Taxe::class,'compte_id');
    }
    public function journalsCompteDefautDebit(){
        return $this->hasMany(Journal::class,'compte_defaut_debit_id');
    }
    public function journalsCompteDefautCredit(){
        return $this->hasMany(Journal::class,'compte_defaut_credit_id');
    }

    public function enfants(){
         return $this->hasMany(Compte::class,'parent_id');
    }

    public function tiers(){
        return $this->hasMany(Tiers::class,'compte_id');
    }

    public function ligneEcritures(){
        return $this->hasMany(LigneEcriture::class,'compte_id');
    }

    public function lignePieceComptable(){
        return $this->hasMany(LignePieceComptable::class,'compte_id');
    }

    public function compteTresorerie(){
        return $this->hasMany(Compte::class,'parent_id');
    }

    public function ligneBudgets(){
        return $this->hasMany(LigneBudget::class,'compte_id');
    }

    public function categoriesImmobilisationsCompteActif(){
        return $this->hasMany(CategorieImmobilisation::class,'compte_actif_id');
    }

    public function categoriesImmobilisationsCompteAmortissement(){
        return $this->hasMany(CategorieImmobilisation::class,'compte_amortissement_id');
    }

    public function categoriesImmobilisationsCompteDotation(){
        return $this->hasMany(CategorieImmobilisation::class,'compte_dotation_id');
    }

    public function categoriesImmobilisationsCompteCession(){
        return $this->hasMany(CategorieImmobilisation::class,'compte_cession_id');
    }

    public function lettrages(){
        return $this->hasMany(Lettrage::class,'compte_id');
    }

     public function soldesComptables()
    {
        return $this->hasMany(SoldeComptable::class, 'compte_id', 'id');
    }
}
