<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajout : exercices comptables avec états de validation, verrouillage et clôture auditable.
        Schema::create('exercice_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('referentiel_comptable_id')->constrained('referentiel_comptables')
            ->cascadeOnUpdate()
            ->restrictOnDelete();
            $table->foreignUuid('devise_id')->nullable()->constrained('devises')->nullOnDelete();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('statut', 20)->default('ouvert');
            $table->string('titre');
            $table->integer('annee');
            $table->timestampTz('cloture_le')->nullable();
            $table->uuid('cloture_par_id')->nullable()->comment('Identifiant externe de l acteur ayant clôturé');
            $table->boolean('ajustements_autorises')->default(true);
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'annee']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression : retrait d'un exercice après ses périodes, écritures et états liés.
        Schema::dropIfExists('exercice_comptables');
    }
};
