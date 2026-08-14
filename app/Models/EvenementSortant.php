<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvenementSortant extends Model
{
    //
     use HasFactory, HasUuids;

    protected $table = 'evenements_sortants';

    protected $keyType = 'string';
    public $incrementing = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'dossier_comptable_id',
        'type',
        'agregat_type',
        'agregat_id',
        'version_agregat',
        'contenu',
        'correlation_id',
        'publie_le',
        'tentatives',
        'derniere_erreur',
    ];

    protected $casts = [
        'version_agregat' => 'integer',
        'contenu' => 'array',
        'publie_le' => 'datetime',
        'tentatives' => 'integer',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

}
