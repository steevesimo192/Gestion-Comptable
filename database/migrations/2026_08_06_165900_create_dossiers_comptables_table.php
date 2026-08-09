<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : configuration comptable locale reliée à l'entreprise gérée par un autre microservice.
        Schema::create('dossiers_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id')->unique()->comment('Identifiant géré par le service entreprise');
            $table->foreignUuid('referentiel_comptable_id')->constrained('referentiel_comptables')->restrictOnDelete();
            $table->foreignUuid('devise_fonctionnelle_id')->constrained('devises')->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('libelle');
            $table->unsignedTinyInteger('mois_debut_exercice')->default(1);
            $table->string('fuseau_horaire', 64)->default('Africa/Douala');
            $table->boolean('multi_devise')->default(false);
            $table->boolean('comptabilite_analytique')->default(false);
            $table->string('methode_arrondi', 20)->default('demi_superieur');
            $table->string('statut', 20)->default('actif');
            $table->json('parametres')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        // Suppression : retrait de la frontière comptable, sans supprimer l'entreprise externe.
        Schema::dropIfExists('dossiers_comptables');
    }
};
