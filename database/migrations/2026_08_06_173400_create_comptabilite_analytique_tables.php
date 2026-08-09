<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : axes libres (centre de coût, projet, département) pour l'analyse multidimensionnelle.
        Schema::create('axes_analytiques', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('libelle');
            $table->boolean('obligatoire')->default(false);
            $table->boolean('actif')->default(true);
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'code']);
        });

        // Ajout : valeurs hiérarchiques de chaque axe analytique.
        Schema::create('comptes_analytiques', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('axe_analytique_id')->constrained('axes_analytiques')->cascadeOnDelete();
            // Ajout différé : UUID parent déclaré ici, puis contraint après création de la table auto-référencée.
            $table->uuid('parent_id')->nullable();
            $table->string('code', 50);
            $table->string('libelle');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->boolean('actif')->default(true);
            $table->json('metadonnees')->nullable();
            $table->timestampsTz();
            $table->unique(['axe_analytique_id', 'code']);
        });

        // Ajout différé : relation hiérarchique appliquée une fois la clé primaire analytique disponible.
        Schema::table('comptes_analytiques', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('comptes_analytiques')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Suppression : les valeurs analytiques précèdent leur axe parent.
        Schema::dropIfExists('comptes_analytiques');
        Schema::dropIfExists('axes_analytiques');
    }
};
