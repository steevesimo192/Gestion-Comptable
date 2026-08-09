<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : versions budgétaires par exercice pour le réalisé contre prévisionnel.
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('exercice_comptable_id')->constrained('exercice_comptables')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('libelle');
            $table->string('version', 30)->default('initial');
            $table->string('statut', 20)->default('brouillon');
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'code', 'version']);
        });

        // Ajout : montants budgétés par période, compte général et dimension analytique.
        Schema::create('ligne_budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('budget_id')->constrained('budgets')->cascadeOnDelete();
            $table->foreignUuid('periode_comptable_id')->constrained('periode_comptables')->cascadeOnDelete();
            $table->foreignUuid('compte_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignUuid('compte_analytique_id')->nullable()->constrained('comptes_analytiques')->nullOnDelete();
            $table->decimal('montant_debit', 19, 4)->default(0);
            $table->decimal('montant_credit', 19, 4)->default(0);
            $table->text('commentaire')->nullable();
            $table->timestampsTz();
            $table->unique(['budget_id', 'periode_comptable_id', 'compte_id', 'compte_analytique_id'], 'ligne_budgets_unicite');
        });

        // Ajout : catégories d'immobilisations portant les règles et comptes d'amortissement.
        Schema::create('categories_immobilisations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('libelle');
            $table->foreignUuid('compte_actif_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignUuid('compte_amortissement_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignUuid('compte_dotation_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignUuid('compte_cession_id')->nullable()->constrained('comptes')->nullOnDelete();
            $table->string('methode_amortissement', 30)->default('lineaire');
            $table->unsignedInteger('duree_mois');
            $table->decimal('valeur_residuelle_pourcentage', 9, 6)->default(0);
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'code']);
        });

        // Ajout : registre des immobilisations et cycle complet acquisition-mise en service-sortie.
        Schema::create('immobilisations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('categorie_immobilisation_id')->constrained('categories_immobilisations')->restrictOnDelete();
            $table->foreignUuid('tiers_id')->nullable()->constrained('tiers')->nullOnDelete();
            $table->foreignUuid('piece_comptable_id')->nullable()->constrained('pieces_comptables')->nullOnDelete();
            $table->foreignUuid('devise_id')->constrained('devises')->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('libelle');
            $table->date('date_acquisition');
            $table->date('date_mise_en_service');
            $table->decimal('cout_acquisition', 19, 4);
            $table->decimal('valeur_residuelle', 19, 4)->default(0);
            $table->decimal('valeur_nette_comptable', 19, 4);
            $table->unsignedInteger('duree_mois');
            $table->string('methode_amortissement', 30);
            $table->string('statut', 20)->default('actif');
            $table->date('date_sortie')->nullable();
            $table->decimal('prix_cession', 19, 4)->nullable();
            $table->json('metadonnees')->nullable();
            $table->timestampsTz();
        });

        // Ajout : plan d'amortissement avec traçabilité vers l'écriture comptable générée.
        Schema::create('amortissements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('immobilisation_id')->constrained('immobilisations')->cascadeOnDelete();
            $table->foreignUuid('periode_comptable_id')->constrained('periode_comptables')->restrictOnDelete();
            $table->foreignUuid('ecriture_id')->nullable()->constrained('ecritures')->nullOnDelete();
            $table->date('date_amortissement');
            $table->decimal('base_amortissable', 19, 4);
            $table->decimal('dotation', 19, 4);
            $table->decimal('amortissement_cumule', 19, 4);
            $table->decimal('valeur_nette', 19, 4);
            $table->string('statut', 20)->default('planifie');
            $table->timestampsTz();
            $table->unique(['immobilisation_id', 'periode_comptable_id']);
        });
    }

    public function down(): void
    {
        // Suppression en ordre inverse pour protéger les références budgétaires et patrimoniales.
        Schema::dropIfExists('amortissements');
        Schema::dropIfExists('immobilisations');
        Schema::dropIfExists('categories_immobilisations');
        Schema::dropIfExists('ligne_budgets');
        Schema::dropIfExists('budgets');
    }
};
