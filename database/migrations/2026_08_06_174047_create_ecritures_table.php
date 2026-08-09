<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : en-tête d'écriture avec cycle brouillon-validation-comptabilisation-extourne.
        Schema::create('ecritures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('exercice_comptable_id')->constrained('exercice_comptables')->restrictOnDelete();
            $table->foreignUuid('periode_comptable_id')->nullable()->constrained('periode_comptables')->restrictOnDelete();
            $table->foreignUuid('journal_id')->constrained('journals')->restrictOnDelete();
            // Ajout différé : UUID de l'écriture extournée, contraint après création de la clé primaire.
            $table->uuid('ecriture_extournee_id')->nullable();
            $table->date('date_comptable');
            $table->string('libelle');
            $table->string('statut', 20)->default('brouillon');
            $table->string('numero', 80);
            $table->date('date_piece')->nullable();
            $table->string('numero_piece')->nullable();
            $table->date('date_echeance')->nullable();
            $table->string('source_type', 50)->nullable();
            $table->uuid('source_id')->nullable();
            $table->string('reference_externe')->nullable();
            $table->decimal('total_debit', 19, 4)->default(0);
            $table->decimal('total_credit', 19, 4)->default(0);
            $table->decimal('total_debit_fonctionnel', 19, 4)->default(0);
            $table->decimal('total_credit_fonctionnel', 19, 4)->default(0);
            $table->text('observation')->nullable();
            $table->uuid('cree_par_id')->nullable();
            $table->uuid('valide_par_id')->nullable();
            $table->timestampTz('valide_le')->nullable();
            $table->uuid('comptabilise_par_id')->nullable();
            $table->timestampTz('comptabilise_le')->nullable();
            $table->uuid('annule_par_id')->nullable();
            $table->timestampTz('annule_le')->nullable();
            $table->string('motif_annulation')->nullable();
            $table->string('empreinte', 64)->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestampsTz();
            $table->unique(['journal_id', 'numero']);
            $table->index(['dossier_comptable_id', 'date_comptable', 'statut']);
            $table->index(['source_type', 'source_id']);
        });

        // Ajout différé : lien d'extourne auto-référencé appliqué après création complète de la table.
        Schema::table('ecritures', function (Blueprint $table) {
            $table->foreign('ecriture_extournee_id')->references('id')->on('ecritures')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Suppression : retrait des écritures après leurs lignes et objets de rapprochement.
        Schema::dropIfExists('ecritures');
    }
};
