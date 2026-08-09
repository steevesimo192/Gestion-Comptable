<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : périodes mensuelles ou d'ajustement pour contrôler les dates et clôtures.
        Schema::create('periode_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exercice_comptable_id')->constrained('exercice_comptables')->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('libelle');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('type', 20)->default('normale');
            $table->string('statut', 20)->default('ouverte');
            $table->timestampTz('verrouille_le')->nullable();
            $table->uuid('verrouille_par_id')->nullable();
            $table->text('motif_verrouillage')->nullable();
            $table->timestampsTz();
            $table->unique(['exercice_comptable_id', 'code']);
        });
    }

    public function down(): void
    {
        // Suppression : les périodes sont des subdivisions de l'exercice et disparaissent avec lui.
        Schema::dropIfExists('periode_comptables');
    }
};
