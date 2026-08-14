<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table= 'budgets';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable =[
       'dossier_comptable_id',
       'exercice_comptable_id',
       'code',
       'libelle',
       'version',
       'statut',
       'total_debit',
       'total_credit',
    ];

     protected $casts = [
        'total_debit' => 'decimal:4',
        'total_credit' => 'decimal:4',
    ];

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id');
    }
    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_comptable_id');
    }
    public function ligneBudgets()
    {
        return $this->hasMany(LigneBudget::class, 'budget_id');
    }
}
