<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AxeAnalytique extends Model
{
    //
    use HasFactory, HasUuids;

    protected $table= 'axes_analytiques';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable=[
        'dossier_comptable_id',
        'code',
        'libelle',
        'obligatoire',
        'actif',
    ];

    protected $casts=[
        'obligatoire'=>'boolean',
        'actif'=>'boolean',
    ];

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id');
    }

    public function comptesAnalytiques()
    {
        return $this->hasMany(CompteAnalytique::class, 'axe_analytique_id');
    }

    public function ligneEcritures(){
        return $this->hasMany(LigneEcriture::class, 'axe_analytique_id');
    }
}
