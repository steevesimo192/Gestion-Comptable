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
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('referentiel_comptable_id')->constrained('referentiel_comptables')
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            $table->string('numero');
            $table->string('intitule');
            $table->string('classe');
            $table->string('nature');  // NatureCompte (enum PHP a definir cote modele)
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
