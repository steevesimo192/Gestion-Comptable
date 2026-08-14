<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleIdempotence extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table='cles_idempotences';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable =[
        'dossier_comptable_id',
        'cle',
        'operation',
        'empreinte_requete',
        'code_reponse',
        'reponse',
        'expire_le'
    ];

    protected $casts = [
        'code_reponse' => 'integer',
        'reponse' => 'array',
        'expire_le' => 'datetime',
    ];

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class,'dossier_comptable_id');
    }
}
