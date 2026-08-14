<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneBudget extends Model
{
    //
      use HasFactory, HasUuids;

    protected $table = 'ligne_budgets';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'budget_id',
        'periode_comptable_id',
        'compte_id',
        'compte_analytique_id',
        'montant_debit',
        'montant_credit',
        'commentaire',
    ];

    protected $casts = [
        'montant_debit' => 'decimal:4',
        'montant_credit' => 'decimal:4',
    ];

    // -------------------- Relations --------------------

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function periodeComptable()
    {
        return $this->belongsTo(PeriodeComptable::class, 'periode_comptable_id', 'id');
    }

    public function compte()
    {
        return $this->belongsTo(Compte::class, 'compte_id', 'id');
    }

    public function compteAnalytique()
    {
        return $this->belongsTo(CompteAnalytique::class, 'compte_analytique_id', 'id');
    }

}
