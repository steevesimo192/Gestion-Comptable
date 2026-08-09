<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : groupes de lettrage pour solder totalement ou partiellement clients et fournisseurs.
        Schema::create('lettrages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('compte_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignUuid('tiers_id')->nullable()->constrained('tiers')->nullOnDelete();
            $table->string('code', 50);
            $table->date('date_lettrage');
            $table->decimal('total_debit', 19, 4);
            $table->decimal('total_credit', 19, 4);
            $table->decimal('solde', 19, 4)->default(0);
            $table->string('statut', 20)->default('partiel');
            $table->uuid('effectue_par_id')->nullable();
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'code']);
        });

        // Ajout : montants consommés par ligne dans un lettrage, autorisant les règlements partiels.
        Schema::create('ligne_lettrages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lettrage_id')->constrained('lettrages')->cascadeOnDelete();
            $table->foreignUuid('ligne_ecriture_id')->constrained('ligne_ecritures')->restrictOnDelete();
            $table->decimal('montant', 19, 4);
            $table->timestampsTz();
            $table->unique(['lettrage_id', 'ligne_ecriture_id']);
        });

        // Ajout : relevés importés pour figer le périmètre et le solde d'un rapprochement bancaire.
        Schema::create('releves_bancaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('compte_tresorerie_id')->constrained('comptes_tresorerie')->cascadeOnDelete();
            $table->string('reference', 100);
            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('solde_ouverture', 19, 4);
            $table->decimal('solde_cloture', 19, 4);
            $table->string('statut', 20)->default('importe');
            $table->timestampTz('rapproche_le')->nullable();
            $table->uuid('rapproche_par_id')->nullable();
            $table->timestampsTz();
            $table->unique(['compte_tresorerie_id', 'reference']);
        });

        // Ajout : opérations bancaires brutes conservées séparément des écritures générées.
        Schema::create('operations_bancaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('releve_bancaire_id')->constrained('releves_bancaires')->cascadeOnDelete();
            $table->date('date_operation');
            $table->date('date_valeur')->nullable();
            $table->string('libelle');
            $table->string('reference')->nullable();
            $table->decimal('montant', 19, 4);
            $table->string('empreinte_import', 64)->nullable();
            $table->string('statut', 20)->default('non_rapprochee');
            $table->json('donnees_importees')->nullable();
            $table->timestampsTz();
            $table->unique(['releve_bancaire_id', 'empreinte_import']);
        });

        // Ajout : rapprochement plusieurs-à-plusieurs entre banque et grand livre avec écarts explicites.
        Schema::create('rapprochements_bancaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('operation_bancaire_id')->constrained('operations_bancaires')->cascadeOnDelete();
            $table->foreignUuid('ligne_ecriture_id')->constrained('ligne_ecritures')->restrictOnDelete();
            $table->decimal('montant_rapproche', 19, 4);
            $table->decimal('ecart', 19, 4)->default(0);
            $table->timestampTz('rapproche_le');
            $table->uuid('rapproche_par_id')->nullable();
            $table->timestampsTz();
            $table->unique(['operation_bancaire_id', 'ligne_ecriture_id'], 'rapprochements_bancaires_unicite');
        });
    }

    public function down(): void
    {
        // Suppression dans l'ordre inverse des dépendances de rapprochement et de lettrage.
        Schema::dropIfExists('rapprochements_bancaires');
        Schema::dropIfExists('operations_bancaires');
        Schema::dropIfExists('releves_bancaires');
        Schema::dropIfExists('ligne_lettrages');
        Schema::dropIfExists('lettrages');
    }
};
