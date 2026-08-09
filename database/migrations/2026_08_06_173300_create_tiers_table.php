<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : référentiel comptable minimal des clients, fournisseurs et autres tiers.
        // L'identité métier reste gérée par son microservice via reference_externe.
        Schema::create('tiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->uuid('reference_externe')->nullable();
            $table->string('code', 50);
            $table->string('type', 30);
            $table->string('nom_affichage');
            $table->string('numero_fiscal', 100)->nullable();
            $table->string('pays_code', 2)->nullable();
            $table->foreignUuid('devise_id')->nullable()->constrained('devises')->nullOnDelete();
            $table->foreignUuid('compte_collectif_id')->nullable()->constrained('comptes')->nullOnDelete();
            $table->unsignedInteger('delai_paiement_jours')->default(0);
            $table->decimal('limite_credit', 19, 4)->nullable();
            $table->boolean('actif')->default(true);
            $table->json('metadonnees')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['dossier_comptable_id', 'code']);
            $table->unique(['dossier_comptable_id', 'reference_externe']);
        });

        // Ajout : coordonnées bancaires utiles aux paiements sans dupliquer une fiche entreprise complète.
        Schema::create('coordonnees_bancaires_tiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tiers_id')->constrained('tiers')->cascadeOnDelete();
            $table->string('titulaire');
            $table->string('banque')->nullable();
            $table->string('iban', 50)->nullable();
            $table->string('bic_swift', 20)->nullable();
            $table->string('numero_compte', 80)->nullable();
            $table->string('pays_code', 2)->nullable();
            $table->boolean('principal')->default(false);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        // Suppression dans l'ordre inverse afin de respecter la clé étrangère vers le tiers.
        Schema::dropIfExists('coordonnees_bancaires_tiers');
        Schema::dropIfExists('tiers');
    }
};
