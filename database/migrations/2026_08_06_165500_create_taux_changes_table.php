<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : historique des taux nécessaire aux écritures et règlements multidevises.
        Schema::create('taux_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('devise_source_id')->constrained('devises')->restrictOnDelete();
            $table->foreignUuid('devise_cible_id')->constrained('devises')->restrictOnDelete();
            $table->date('date_taux');
            $table->decimal('taux', 20, 10);
            $table->string('source', 50)->default('manuel');
            $table->timestampsTz();
            $table->unique(['devise_source_id', 'devise_cible_id', 'date_taux', 'source'], 'taux_changes_unicite');
        });
    }

    public function down(): void
    {
        // Suppression : la table dépend uniquement des devises et peut être retirée sans supprimer celles-ci.
        Schema::dropIfExists('taux_changes');
    }
};
