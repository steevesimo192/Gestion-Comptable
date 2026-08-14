<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompteTresorerie extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table='comptes_tresorerie';

    protected $keyType='string';
    public $incrementing=false;

    protected $fillable=[
        'dossier_comptable_id',
        'compte_id',
        'journal_id',
        'devise_id',
        'type',
        'banque',
        'iban',
        'bic_swift',
        'numero_compte',
        'solde_initial',
        'date_solde_initial',
        'actif',
    ];


    protected $casts=[
        'solde_initial'=>'decimal:4',
        'date_solde_initial'=>'date',
        'actif'=>'boolean',
    ];

    public function dossierComptable(){
        return $this->belongsTo(DossierComptable::class,'dossier_comptable_id');
    }
    public function compte(){
        return $this->belongsTo(Compte::class,'compte_id');
    }
    public function journal(){
        return $this->belongsTo(Journal::class,'journal_id');
    }

    public function devise(){
        return $this->belongsTo(Devise::class,'devise_id');
    }

    public function paiements(){
        return $this->hasMany(Paiement::class,'compte_tresorerie_id');
    }

    public function releveBancaire(){
        return $this->hasMany(ReleveBancaire::class,'compte_tresorerie_id');
    }
}
