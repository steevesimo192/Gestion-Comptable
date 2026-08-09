<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : mouvements de grand livre en double entrée, multidevises et analytiques.
        Schema::create('ligne_ecritures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ecriture_id')->constrained('ecritures')->cascadeOnDelete();
            $table->foreignUuid('compte_id')->constrained('comptes')->restrictOnDelete();
            $table->foreignUuid('tiers_id')->nullable()->constrained('tiers')->nullOnDelete();
            $table->foreignUuid('taxe_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->foreignUuid('axe_analytique_id')->nullable()->constrained('axes_analytiques')->nullOnDelete();
            $table->foreignUuid('compte_analytique_id')->nullable()->constrained('comptes_analytiques')->nullOnDelete();
            $table->foreignUuid('devise_id')->nullable()->constrained('devises')->nullOnDelete();
            $table->decimal('debit', 19, 4)->default(0);
            $table->decimal('credit', 19, 4)->default(0);
            $table->decimal('montant_devise', 19, 4)->nullable();
            $table->decimal('taux_change', 20, 10)->nullable();
            $table->decimal('debit_fonctionnel', 19, 4)->default(0);
            $table->decimal('credit_fonctionnel', 19, 4)->default(0);
            $table->unsignedInteger('ordre');
            $table->string('reference')->nullable();
            $table->string('libelle');
            $table->date('date_echeance')->nullable();
            $table->string('lettrage_code', 50)->nullable();
            $table->timestampTz('lettre_le')->nullable();
            $table->boolean('rapprochee')->default(false);
            $table->json('dimensions')->nullable();
            $table->timestampsTz();
            $table->unique(['ecriture_id', 'ordre']);
            $table->index(['compte_id', 'date_echeance']);
            $table->index(['tiers_id', 'lettrage_code']);
        });
    }

    public function down(): void
    {
        // Suppression : retrait des mouvements avant leurs écritures parentes.
        Schema::dropIfExists('ligne_ecritures');
    }
};
