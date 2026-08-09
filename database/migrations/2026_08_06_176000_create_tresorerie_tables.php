<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : comptes bancaires et caisses rattachés à un compte général et un journal.
        Schema::create('comptes_tresorerie', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('compte_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignUuid('journal_id')->constrained('journals')->restrictOnDelete();
            $table->foreignUuid('devise_id')->constrained('devises')->restrictOnDelete();
            $table->string('type', 20)->default('banque');
            $table->string('nom');
            $table->string('banque')->nullable();
            $table->string('iban', 50)->nullable();
            $table->string('bic_swift', 20)->nullable();
            $table->string('numero_compte', 80)->nullable();
            $table->decimal('solde_initial', 19, 4)->default(0);
            $table->date('date_solde_initial')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestampsTz();
        });

        // Ajout : règlements entrants, sortants, virements et remboursements.
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('tiers_id')->nullable()->constrained('tiers')->restrictOnDelete();
            $table->foreignUuid('compte_tresorerie_id')->constrained('comptes_tresorerie')->restrictOnDelete();
            $table->foreignUuid('devise_id')->constrained('devises')->restrictOnDelete();
            $table->foreignUuid('ecriture_id')->nullable()->constrained('ecritures')->nullOnDelete();
            $table->string('numero', 80);
            $table->string('sens', 20);
            $table->string('mode', 30);
            $table->date('date_paiement');
            $table->date('date_valeur')->nullable();
            $table->decimal('montant', 19, 4);
            $table->decimal('taux_change', 20, 10)->default(1);
            $table->decimal('frais', 19, 4)->default(0);
            $table->string('reference')->nullable();
            $table->string('statut', 20)->default('brouillon');
            $table->text('memo')->nullable();
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'numero']);
            $table->index(['tiers_id', 'date_paiement']);
        });

        // Ajout : ventilation d'un paiement sur plusieurs pièces ou échéances.
        Schema::create('affectations_paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('paiement_id')->constrained('paiements')->cascadeOnDelete();
            $table->foreignUuid('piece_comptable_id')->constrained('pieces_comptables')->restrictOnDelete();
            $table->foreignUuid('echeance_id')->nullable()->constrained('echeances')->nullOnDelete();
            $table->decimal('montant', 19, 4);
            $table->decimal('ecart_change', 19, 4)->default(0);
            $table->decimal('escompte', 19, 4)->default(0);
            $table->timestampTz('affecte_le');
            $table->timestampsTz();
            $table->unique(['paiement_id', 'piece_comptable_id', 'echeance_id'], 'affectations_paiements_unicite');
        });
    }

    public function down(): void
    {
        // Suppression : allocations, paiements puis comptes de trésorerie selon leurs dépendances.
        Schema::dropIfExists('affectations_paiements');
        Schema::dropIfExists('paiements');
        Schema::dropIfExists('comptes_tresorerie');
    }
};
