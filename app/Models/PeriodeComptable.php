<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodeComptable extends Model
{
    //

     use HasFactory, HasUuids;

    protected $table = 'periode_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'exercice_comptable_id',
        'code',
        'libelle',
        'date_debut',
        'date_fin',
        'type',
        'statut',
        'verrouille_le',
        'verrouille_par_id',
        'motif_verrouillage',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'verrouille_le' => 'datetime',
    ];

    // -------------------- Relations --------------------

    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_comptable_id', 'id');
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class, 'periode_comptable_id', 'id');
    }

    public function ligneBudgets()
    {
        return $this->hasMany(LigneBudget::class, 'periode_comptable_id', 'id');
    }

    public function amortissements()
    {
        return $this->hasMany(Amortissement::class, 'periode_comptable_id', 'id');
    }

}
