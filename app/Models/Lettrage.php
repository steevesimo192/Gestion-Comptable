<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lettrage extends Model
{
    //
      use HasFactory, HasUuids;

    protected $table = 'lettrages';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'compte_id',
        'tiers_id',
        'code',
        'date_lettrage',
        'total_debit',
        'total_credit',
        'solde',
        'statut',
        'effectue_par_id',
    ];

    protected $casts = [
        'date_lettrage' => 'date',
        'total_debit' => 'decimal:4',
        'total_credit' => 'decimal:4',
        'solde' => 'decimal:4',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id', 'id');
    }

    public function tiers()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id', 'id');
    }

    public function ligneLettrages()
    {
        return $this->hasMany(LigneLettrage::class, 'lettrage_id', 'id');
    }

}
