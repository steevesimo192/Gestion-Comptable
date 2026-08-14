<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerrouillageComptable extends Model
{
    //
    use HasFactory, HasUuids;

    protected $table = 'verrouillages_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'type',
        'date_limite',
        'portee',
        'motif',
        'verrouille_par_id',
        'verrouille_le',
    ];

    protected $casts = [
        'date_limite' => 'date',
        'verrouille_le' => 'datetime',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

}
