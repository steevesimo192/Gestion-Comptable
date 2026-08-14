<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Amortissement extends Model
{
    //
    use HasFactory, HasUuids;

    protected $table= 'amortissements';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable =[
        'immobilisation_id',
        'periode_comptable_id',
        'ecriture_id',
        'date_amortissement',
        'base_amortissable',
        'dotation',
        'amortissement_cumule',
        'valeur_nette',
        'statut',
    ];

    protected $cats=[
        'date_amortissement'=>'date',
        'base_amortissable'=>'decimal:4',
        'dotation'=>'decimal:4',
        'amortissement_cumule'=>'decimal:4',
        'valeur_nette'=>'decimal:4',
    ];


    public function immobilisation(){
        return $this->belongsTo(Immobilisation::class, 'immobilisation_id');
    }

    public function periodeComptable(){
        return $this->belongsTo(PeriodeComptable::class, 'periode_comptable_id');
    }

    public function ecriture(){
        return $this->belongsTo(Ecriture::class, 'ecriture_id');
    }

}
