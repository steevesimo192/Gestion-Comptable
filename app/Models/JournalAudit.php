<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalAudit extends Model
{
    //
    use HasFactory, HasUuids;

    protected $table = 'journaux_audit';

    protected $keyType = 'string';
    public $incrementing = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'dossier_comptable_id',
        'acteur_id',
        'acteur_type',
        'action',
        'entite_type',
        'entite_id',
        'valeurs_avant',
        'valeurs_apres',
        'adresse_ip',
        'correlation_id',
        'empreinte_precedente',
        'empreinte',
    ];

    protected $casts = [
        'valeurs_avant' => 'array',
        'valeurs_apres' => 'array',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }
}
