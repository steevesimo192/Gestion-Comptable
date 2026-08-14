<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LigneDeclarationFiscale extends Model
{
    //

    use HasFactory, HasUuids;

    protected $table = 'ligne_declarations_fiscales';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'declaration_fiscale_id',
        'taxe_id',
        'base',
        'montant',
        'case_declaration',
    ];

    protected $casts = [
        'base' => 'decimal:4',
        'montant' => 'decimal:4',
    ];

    // -------------------- Relations --------------------

    public function declarationFiscale()
    {
        return $this->belongsTo(DeclarationFiscale::class, 'declaration_fiscale_id', 'id');
    }

    public function taxe()
    {
        return $this->belongsTo(Taxe::class, 'taxe_id', 'id');
    }

}
