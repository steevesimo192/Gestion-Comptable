<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferentielComptable extends Model
{
    //

     use HasFactory, HasUuids;

    protected $table = 'referentiel_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nom',
        'code',
        'pays_code',
        'description',
        'version',
        'date_mise_en_vigueur',
        'date_fin_validite',
        'actif',
    ];

    protected $casts = [
        'date_mise_en_vigueur' => 'date',
        'date_fin_validite' => 'date',
        'actif' => 'boolean',
    ];

    // -------------------- Relations --------------------

    public function dossiersComptables()
    {
        return $this->hasMany(DossierComptable::class, 'referentiel_comptable_id', 'id');
    }

    public function comptes()
    {
        return $this->hasMany(Compte::class, 'referentiel_comptable_id', 'id');
    }

    public function exerciceComptables()
    {
        return $this->hasMany(ExerciceComptable::class, 'referentiel_comptable_id', 'id');
    }

}
