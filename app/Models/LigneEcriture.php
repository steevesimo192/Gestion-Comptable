<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneEcriture extends Model
{
    //

    use HasFactory, HasUuids;

    protected $table = 'ligne_ecritures';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ecriture_id',
        'compte_id',
        'tiers_id',
        'taxe_id',
        'axe_analytique_id',
        'compte_analytique_id',
        'devise_id',
        'debit',
        'credit',
        'montant_devise',
        'taux_change',
        'debit_fonctionnel',
        'credit_fonctionnel',
        'ordre',
        'reference',
        'libelle',
        'date_echeance',
        'lettrage_code',
        'lettre_le',
        'rapprochee',
        'dimensions',
        'montant_rapproche',
    ];

    protected $casts = [
        'debit' => 'decimal:4',
        'credit' => 'decimal:4',
        'montant_devise' => 'decimal:4',
        'taux_change' => 'decimal:10',
        'debit_fonctionnel' => 'decimal:4',
        'credit_fonctionnel' => 'decimal:4',
        'ordre' => 'integer',
        'date_echeance' => 'date',
        'lettre_le' => 'datetime',
        'rapprochee' => 'boolean',
        'dimensions' => 'array',
        'montant_rapproche' => 'decimal:4',
    ];

    // -------------------- Relations --------------------

    public function ecriture()
    {
        return $this->belongsTo(Ecriture::class, 'ecriture_id', 'id');
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id', 'id');
    }

    public function tiers()
    {
        return $this->belongsTo(Tiers::class, 'tiers_id', 'id');
    }

    public function taxe()
    {
        return $this->belongsTo(Taxe::class, 'taxe_id', 'id');
    }

    public function axeAnalytique()
    {
        return $this->belongsTo(AxeAnalytique::class, 'axe_analytique_id', 'id');
    }

    public function compteAnalytique()
    {
        return $this->belongsTo(CompteAnalytique::class, 'compte_analytique_id', 'id');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'devise_id', 'id');
    }

    public function ligneLettrages()
    {
        return $this->hasMany(LigneLettrage::class, 'ligne_ecriture_id', 'id');
    }

    public function rapprochementsBancaires()
    {
        return $this->hasMany(RapprochementBancaire::class, 'ligne_ecriture_id', 'id');
    }

}
