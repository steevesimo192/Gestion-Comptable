<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    //
     use HasFactory, HasUuids;

    protected $table = 'journals';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'dossier_comptable_id',
        'devise_id',
        'code',
        'libelle',
        'type',
        'prefixe_sequence',
        'prochain_numero',
        'padding_numero',
        'controle_piece_unique',
        'est_actif',
        'compte_defaut_debit_id',
        'compte_defaut_credit_id',
    ];

    protected $casts = [
        'prochain_numero' => 'integer',
        'padding_numero' => 'integer',
        'controle_piece_unique' => 'boolean',
        'est_actif' => 'boolean',
    ];

    // -------------------- Relations --------------------

    public function dossierComptable()
    {
        return $this->belongsTo(DossierComptable::class, 'dossier_comptable_id', 'id');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'devise_id', 'id');
    }

    public function compteDefautDebit()
    {
        return $this->belongsTo(Compte::class, 'compte_defaut_debit_id', 'id');
    }

    public function compteDefautCredit()
    {
        return $this->belongsTo(Compte::class, 'compte_defaut_credit_id', 'id');
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class, 'journal_id', 'id');
    }

    public function piecesComptables()
    {
        return $this->hasMany(PieceComptable::class, 'journal_id', 'id');
    }

    public function comptesTresorerie()
    {
        return $this->hasMany(CompteTresorerie::class, 'journal_id', 'id');
    }

    public function modelesEcrituresRecurrentes()
    {
        return $this->hasMany(ModeleEcritureRecurrente::class, 'journal_id', 'id');
    }

}
