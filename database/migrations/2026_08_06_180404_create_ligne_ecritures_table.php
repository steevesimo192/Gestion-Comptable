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
        Schema::create('ligne_ecritures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ecriture_id')->constrained('ecritures')
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            $table->foreignUuid('compte_id')->constrained('comptes')
            ->cascadeOnUpdate()
            ->cascadeOnDelete();
            $table->foreignUuid('taxe_id')->constrained('taxes')
            ->cascadeOnUpdate()
            ->cascadeOnDelete()
            ->nullOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->integer('ordre');
            $table->string('reference')->nullable();
            $table->string('libelle');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_ecritures');
    }
};
