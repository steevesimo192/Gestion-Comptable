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
        Schema::create('referentiel_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nom');
            $table->string('pays_applique');
            $table->text('description')->nullable();
            $table->string('version');
            $table->date('date_mise_en_vigueur');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referentiel_comptables');
    }
};
