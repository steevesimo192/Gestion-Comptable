<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : factures, avoirs, notes de débit et pièces diverses générant des écritures.
        Schema::create('pieces_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('tiers_id')->nullable()->constrained('tiers')->restrictOnDelete();
            $table->foreignUuid('journal_id')->constrained('journals')->restrictOnDelete();
            $table->foreignUuid('devise_id')->constrained('devises')->restrictOnDelete();
            $table->foreignUuid('ecriture_id')->nullable()->constrained('ecritures')->nullOnDelete();
            $table->string('type', 30);
            $table->string('numero', 80);
            $table->string('reference_fournisseur')->nullable();
            $table->date('date_piece');
            $table->date('date_comptable');
            $table->date('date_echeance')->nullable();
            $table->string('statut', 30)->default('brouillon');
            $table->decimal('taux_change', 20, 10)->default(1);
            $table->decimal('sous_total_ht', 19, 4)->default(0);
            $table->decimal('total_remise', 19, 4)->default(0);
            $table->decimal('total_taxes', 19, 4)->default(0);
            $table->decimal('total_ttc', 19, 4)->default(0);
            $table->decimal('total_regle', 19, 4)->default(0);
            $table->decimal('solde_du', 19, 4)->default(0);
            $table->string('conditions_paiement')->nullable();
            $table->text('notes')->nullable();
            $table->uuid('cree_par_id')->nullable();
            $table->uuid('valide_par_id')->nullable();
            $table->timestampTz('valide_le')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['dossier_comptable_id', 'type', 'numero']);
            $table->index(['tiers_id', 'date_echeance', 'statut']);
        });

        // Ajout : lignes détaillées permettant les bases fiscales et imputations comptables distinctes.
        Schema::create('ligne_pieces_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('piece_comptable_id')->constrained('pieces_comptables')->cascadeOnDelete();
            $table->foreignUuid('compte_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignUuid('taxe_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->foreignUuid('compte_analytique_id')->nullable()->constrained('comptes_analytiques')->nullOnDelete();
            $table->uuid('article_id')->nullable()->comment('Référence externe facultative du catalogue');
            $table->unsignedInteger('ordre');
            $table->string('description');
            $table->decimal('quantite', 19, 6)->default(1);
            $table->string('unite', 30)->nullable();
            $table->decimal('prix_unitaire', 19, 4);
            $table->decimal('taux_remise', 9, 6)->default(0);
            $table->decimal('montant_remise', 19, 4)->default(0);
            $table->decimal('base_hors_taxe', 19, 4)->default(0);
            $table->decimal('montant_taxe', 19, 4)->default(0);
            $table->decimal('montant_ttc', 19, 4)->default(0);
            $table->json('dimensions')->nullable();
            $table->timestampsTz();
            $table->unique(['piece_comptable_id', 'ordre']);
        });

        // Ajout : échéancier fractionnable pour relances et balance âgée fiables.
        Schema::create('echeances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('piece_comptable_id')->constrained('pieces_comptables')->cascadeOnDelete();
            $table->date('date_echeance');
            $table->decimal('montant', 19, 4);
            $table->decimal('montant_regle', 19, 4)->default(0);
            $table->string('statut', 20)->default('ouverte');
            $table->timestampsTz();
            $table->index(['date_echeance', 'statut']);
        });
    }

    public function down(): void
    {
        // Suppression dans l'ordre enfant-parent afin de préserver l'intégrité des pièces.
        Schema::dropIfExists('echeances');
        Schema::dropIfExists('ligne_pieces_comptables');
        Schema::dropIfExists('pieces_comptables');
    }
};
