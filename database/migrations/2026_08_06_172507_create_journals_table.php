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
        // Ajout : journaux comptables, séquences et comptes de contrepartie automatique.
        Schema::create('journals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('devise_id')->nullable()->constrained('devises')->nullOnDelete();
            $table->string('code', 20);
            $table->string('libelle');
            $table->string('type', 30);
            $table->string('prefixe_sequence', 20)->nullable();
            $table->unsignedBigInteger('prochain_numero')->default(1);
            $table->unsignedTinyInteger('padding_numero')->default(6);
            $table->boolean('controle_piece_unique')->default(false);
            $table->boolean('est_actif')->default(true);
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression : retrait des journaux après toutes les écritures qui les utilisent.
        Schema::dropIfExists('journals');
    }
};
