<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentComptable extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table='documents_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'documentable_type',
        'documentable_id',
        'nom_original',
        'chemin_stockage',
        'type_mime',
        'taille_octets',
        'empreinte_sha256',
        'depose_par_id'
    ];

    protected $casts=[
        'taille_octets'=>'integer',
    ];


    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class,'dossier_comptable_id');
    }
}
