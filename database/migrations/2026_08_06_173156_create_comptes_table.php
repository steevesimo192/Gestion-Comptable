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
        // Ajout : plan comptable hiérarchique et propriétés de saisie, lettrage et rapprochement.
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('referentiel_comptable_id')->constrained('referentiel_comptables')
            ->cascadeOnUpdate()
            ->restrictOnDelete();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            // Ajout différé : UUID du compte parent ; la contrainte vient après la création de la clé primaire PostgreSQL.
            $table->uuid('parent_id')->nullable();
            $table->string('numero', 40);
            $table->string('intitule');
            $table->string('classe', 10);
            $table->string('type', 30);
            $table->string('nature', 20);
            $table->string('categorie', 50)->nullable();
            $table->boolean('lettrable')->default(false);
            $table->boolean('rapprochable')->default(false);
            $table->boolean('accepte_ecritures')->default(true);
            $table->boolean('auxiliaire')->default(false);
            $table->foreignUuid('devise_id')->nullable()->constrained('devises')->nullOnDelete();
            $table->string('code_reporting', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['dossier_comptable_id', 'numero']);
            $table->index(['dossier_comptable_id', 'classe', 'actif']);
        });

        // Ajout différé : PostgreSQL doit connaître la clé primaire de comptes avant l'auto-référence hiérarchique.
        Schema::table('comptes', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('comptes')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression : retrait du plan seulement après ses journaux, taxes et mouvements dépendants.
        Schema::dropIfExists('comptes');
    }
};
