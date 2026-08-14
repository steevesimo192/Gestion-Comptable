<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeleEcritureRecurrente extends Model
{
    //

     use HasFactory, HasUuids;

    protected $table = 'modeles_ecritures_recurrentes';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'journal_id',
        'code',
        'libelle',
        'frequence',
        'intervalle',
        'prochaine_execution',
        'date_fin',
        'comptabilisation_automatique',
        'actif',
        'modele_lignes',
    ];

    protected $casts = [
        'intervalle' => 'integer',
        'prochaine_execution' => 'date',
        'date_fin' => 'date',
        'comptabilisation_automatique' => 'boolean',
        'actif' => 'boolean',
        'modele_lignes' => 'array',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id', 'id');
    }

}
