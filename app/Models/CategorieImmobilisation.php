<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategorieImmobilisation extends Model
{
    //
   use HasFactory,HasUuids;

   protected $table='categories_immobilisations';
   protected $keyType = 'string';
   public $incrementing = false;

   protected $fillable =[
       'dossier_comptable_id',
       'code',
       'libelle',
       'compte_actif_id',
       'compte_amortissement_id',
        'compte_dotation_id',
        'compte_cession_id',
        'methode_amortissement',
        'duree_mois',
       'valeur_residuelle_pourcentage'
   ];

   protected $casts = [
       'duree_mois' => 'integer',
       'valeur_residuelle_pourcentage' => 'decimal:6',
   ];

 public function dossierComptable()
   {
       return $this->belongsTo(DossierComptable::class,'dossier_comptable_id');
   }

   public function compteActif()
   {
       return $this->belongsTo(Compte::class, 'compte_actif_id');
   }

   public function compteAmortissement()
   {
       return $this->belongsTo(Compte::class, 'compte_amortissement_id');
   }

   public function compteDotation()
   {
       return $this->belongsTo(Compte::class, 'compte_dotation_id');
   }

   public function compteCession()
   {
       return $this->belongsTo(Compte::class, 'compte_cession_id');
   }
   public function immobilisations()
   {
       return $this->hasMany(Immobilisation::class, 'categorie_immobilisation_id');
   }
}
