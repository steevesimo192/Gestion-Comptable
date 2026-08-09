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
        // Ajout : référentiels réglementaires versionnés (SYSCOHADA, IFRS ou plans nationaux).
        Schema::create('referentiel_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('code', 30)->unique();
            $table->string('pays_code', 2)->nullable();
            $table->text('description')->nullable();
            $table->string('version');
            $table->date('date_mise_en_vigueur');
            $table->date('date_fin_validite')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression : retrait d'un référentiel uniquement après les dossiers et comptes associés.
        Schema::dropIfExists('referentiel_comptables');
    }
};
