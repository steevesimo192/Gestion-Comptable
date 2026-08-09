<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : modèles d'écritures périodiques pour abonnements, loyers et provisions.
        Schema::create('modeles_ecritures_recurrentes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('journal_id')->constrained('journals')->restrictOnDelete();
            $table->string('code', 30);
            $table->string('libelle');
            $table->string('frequence', 20);
            $table->unsignedTinyInteger('intervalle')->default(1);
            $table->date('prochaine_execution');
            $table->date('date_fin')->nullable();
            $table->boolean('comptabilisation_automatique')->default(false);
            $table->boolean('actif')->default(true);
            $table->json('modele_lignes');
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'code']);
        });

        // Ajout : déclarations fiscales et périodes déclarées, indispensables au suivi TVA et retenues.
        Schema::create('declarations_fiscales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('exercice_comptable_id')->constrained('exercice_comptables')->restrictOnDelete();
            $table->string('type', 30);
            $table->string('numero')->nullable();
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->date('date_echeance');
            $table->decimal('base_imposable', 19, 4)->default(0);
            $table->decimal('taxe_collectee', 19, 4)->default(0);
            $table->decimal('taxe_deductible', 19, 4)->default(0);
            $table->decimal('credit_anterieur', 19, 4)->default(0);
            $table->decimal('montant_du', 19, 4)->default(0);
            $table->string('statut', 20)->default('brouillon');
            $table->timestampTz('deposee_le')->nullable();
            $table->string('accuse_reception')->nullable();
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'type', 'periode_debut', 'periode_fin'], 'declarations_fiscales_unicite');
        });

        // Ajout : ventilation d'une déclaration par taxe pour rendre le calcul auditable.
        Schema::create('ligne_declarations_fiscales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('declaration_fiscale_id')->constrained('declarations_fiscales')->cascadeOnDelete();
            $table->foreignUuid('taxe_id')->constrained('taxes')->restrictOnDelete();
            $table->decimal('base', 19, 4)->default(0);
            $table->decimal('montant', 19, 4)->default(0);
            $table->string('case_declaration', 30)->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        // Suppression : lignes fiscales avant déclarations, puis modèles récurrents indépendants.
        Schema::dropIfExists('ligne_declarations_fiscales');
        Schema::dropIfExists('declarations_fiscales');
        Schema::dropIfExists('modeles_ecritures_recurrentes');
    }
};
