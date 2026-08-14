<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciceComptable extends Model
{
    //
      use HasFactory, HasUuids;

    protected $table = 'exercice_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'referentiel_comptable_id',
        'devise_id',
        'dossier_comptable_id',
        'date_debut',
        'date_fin',
        'statut',
        'titre',
        'annee',
        'cloture_le',
        'cloture_par_id',
        'ajustements_autorises',
        'notes',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'annee' => 'integer',
        'cloture_le' => 'datetime',
        'ajustements_autorises' => 'boolean',
    ];

    // -------------------- Relations --------------------

    public function referentielComptable()
    {
        return $this->belongsTo(ReferentielComptable::class, 'referentiel_comptable_id', 'id');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'devise_id', 'id');
    }

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

    public function periodeComptables()
    {
        return $this->hasMany(PeriodeComptable::class, 'exercice_comptable_id', 'id');
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class, 'exercice_comptable_id', 'id');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'exercice_comptable_id', 'id');
    }

    public function declarationsFiscales()
    {
        return $this->hasMany(DeclarationFiscale::class, 'exercice_comptable_id', 'id');
    }

}
