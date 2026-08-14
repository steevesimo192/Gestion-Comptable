<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TauxChange extends Model
{
    //
     use HasFactory, HasUuids;

    protected $table = 'taux_changes';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'devise_source_id',
        'devise_cible_id',
        'date_taux',
        'taux',
        'source',
    ];

    protected $casts = [
        'date_taux' => 'date',
        'taux' => 'decimal:10',
    ];

    // -------------------- Relations --------------------

    public function deviseSource()
    {
        return $this->belongsTo(Devise::class, 'devise_source_id', 'id');
    }

    public function deviseCible()
    {
        return $this->belongsTo(Devise::class, 'devise_cible_id', 'id');
    }

}
