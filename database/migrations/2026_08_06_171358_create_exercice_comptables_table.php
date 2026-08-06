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
        Schema::create('exercice_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('referentiel_comptable_id')->constrained('referentiel_comptables')
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            $table->foreignUuid('devise_id')->constrained('devises')
            ->nullable()
            ->cascadeOnUpdate()
            ->nullOnDelete();
            $table->uuid('entreprise_id')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('est_cloturer')->default(false);
            $table->string('titre');
            $table->integer('annee');
            $table->date('date_cloture')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercice_comptables');
    }
};
