<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoordonneesBancaireTiers extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table='coordonnees_bancaires_tiers';

    protected $keyType='string';
    public $incrementing=false;

    protected $fillable=[
        'tiers_id',
        'titulaire',
        'banque',
        'iban',
        'bic_swift',
        'numero_compte',
        'pays_code',
        'principal',
    ];

    protected $casts=[
        'principal'=>'boolean',
    ];


    public function tiers()
    {
        return $this->belongsTo(Tiers::class,'tiers_id');
    }

}
