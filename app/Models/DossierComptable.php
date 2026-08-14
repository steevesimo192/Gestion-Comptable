<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierComptable extends Model
{
    //
    use HasFactory,HasUuids;

    protected $table = 'dossiers_comptables';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'entreprise_id',
        'referentiel_comptable_id',
        'devise_fonctionnelle_id',
        'code',
        'libelle',
        'mois_debut_exercice',
        'fuseau_horaire',
        'multi_devise',
        'comptabilite_analytique',
        'methode_arrondi',
        'statut',
        'parametres'
    ];


    protected $casts = [
        'mois_debut_exercice' => 'integer',
        'multi_devise' => 'boolean',
        'comptabilite_analytique' => 'boolean',
        'parametres' => 'array',
    ];

    public function referentielComptable()
    {
        return $this->belongsTo(ReferentielComptable::class, 'referentiel_comptable_id');
    }

    public function deviseFonctionnelle()
    {
        return $this->belongsTo(Devise::class, 'devise_fonctionnelle_id');
    }

    public function taxes()
    {
        return $this->hasMany(Taxe::class, 'dossier_comptable_id');
    }

     public function journals()
    {
        return $this->hasMany(Journal::class, 'dossier_comptable_id', 'id');
    }

    public function comptes()
    {
        return $this->hasMany(Compte::class, 'dossier_comptable_id', 'id');
    }

    public function exerciceComptables()
    {
        return $this->hasMany(ExerciceComptable::class, 'dossier_comptable_id', 'id');
    }

    public function tiers()
    {
        return $this->hasMany(Tiers::class, 'dossier_comptable_id', 'id');
    }

    public function axesAnalytiques()
    {
        return $this->hasMany(AxeAnalytique::class, 'dossier_comptable_id', 'id');
    }

    public function ecritures()
    {
        return $this->hasMany(Ecriture::class, 'dossier_comptable_id', 'id');
    }

    public function piecesComptables()
    {
        return $this->hasMany(PieceComptable::class, 'dossier_comptable_id', 'id');
    }

    public function comptesTresorerie()
    {
        return $this->hasMany(CompteTresorerie::class, 'dossier_comptable_id', 'id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'dossier_comptable_id', 'id');
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'dossier_comptable_id', 'id');
    }

    public function categoriesImmobilisations()
    {
        return $this->hasMany(CategorieImmobilisation::class, 'dossier_comptable_id', 'id');
    }

    public function modelesEcrituresRecurrentes()
    {
        return $this->hasMany(ModeleEcritureRecurrente::class, 'dossier_comptable_id', 'id');
    }

    public function declarationsFiscales()
    {
        return $this->hasMany(DeclarationFiscale::class, 'dossier_comptable_id', 'id');
    }

    public function lettrages()
    {
        return $this->hasMany(Lettrage::class, 'dossier_comptable_id', 'id');
    }

    public function verrouillagesComptables()
    {
        return $this->hasMany(VerrouillageComptable::class, 'dossier_comptable_id', 'id');
    }

    public function documentsComptables()
    {
        return $this->hasMany(DocumentComptable::class, 'dossier_comptable_id', 'id');
    }

    public function journauxAudit()
    {
        return $this->hasMany(JournalAudit::class, 'dossier_comptable_id', 'id');
    }

    public function clesIdempotence()
    {
        return $this->hasMany(CleIdempotence::class, 'dossier_comptable_id', 'id');
    }

    public function evenementsSortants()
    {
        return $this->hasMany(EvenementSortant::class, 'dossier_comptable_id', 'id');
    }

}
