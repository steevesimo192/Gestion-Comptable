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
        // Ajout : référentiel ISO des devises et règles d'affichage des montants.
        Schema::create('devises', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 3)->unique();
            $table->string('nom');
            $table->string('symbole', 10);
            $table->unsignedTinyInteger('precision')->default(2);
            $table->string('position_symbole', 10)->default('apres');
            $table->boolean('actif')->default(true);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression : retrait du référentiel des devises après ses tables dépendantes.
        Schema::dropIfExists('devises');
    }
};
