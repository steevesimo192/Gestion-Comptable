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
        // Ajout : règles de taxes paramétrables par dossier et périodes de validité.
        Schema::create('taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->string('nom');
            $table->string('code', 30);
            $table->string('type', 20)->default('pourcentage');
            $table->string('usage', 20)->default('les_deux');
            $table->decimal('taux', 9, 6)->default(0);
            $table->decimal('montant_fixe', 19, 4)->nullable();
            $table->boolean('prix_taxe_incluse')->default(false);
            $table->boolean('incluse_dans_base')->default(false);
            $table->string('portee', 20)->default('ligne');
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->json('regles')->nullable();
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression : retrait des règles fiscales après leurs lignes dépendantes.
        Schema::dropIfExists('taxes');
    }
};
