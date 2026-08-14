<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneLettrage extends Model
{
    //

    use HasFactory, HasUuids;

    protected $table = 'ligne_lettrages';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'lettrage_id',
        'ligne_ecriture_id',
        'montant',
    ];

    protected $casts = [
        'montant' => 'decimal:4',
    ];

    // -------------------- Relations --------------------

    public function lettrage()
    {
        return $this->belongsTo(Lettrage::class, 'lettrage_id', 'id');
    }

    public function ligneEcriture()
    {
        return $this->belongsTo(LigneEcriture::class, 'ligne_ecriture_id', 'id');
    }
}
