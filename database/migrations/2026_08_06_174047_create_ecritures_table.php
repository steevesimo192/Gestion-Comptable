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
        Schema::create('ecritures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_id')->constrained('journals')
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            $table->date('date');
            $table->string('libelle');
            $table->string('statut'); // StatutEnum (enum PHP a definir cote modele)
            $table->string('numero');
            $table->date('date_ecriture');
            $table->date('date_piece')->nullable();
            $table->string('numero_piece')->nullable();
            $table->decimal('montant_total', 15, 2);
            $table->dateTime('cree_le');
            $table->string('observation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecritures');
    }
};
