<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout différé : les comptes n'existent qu'après la création des taxes et journaux historiques.
        Schema::table('taxes', function (Blueprint $table) {
            $table->foreignUuid('compte_taxe_collectee_id')->nullable()->constrained('comptes')->nullOnDelete();
            $table->foreignUuid('compte_taxe_deductible_id')->nullable()->constrained('comptes')->nullOnDelete();
            $table->foreignUuid('compte_contrepartie_id')->nullable()->constrained('comptes')->nullOnDelete();
        });

        // Ajout : comptes automatiques utilisés lors de la génération des écritures d'un journal.
        Schema::table('journals', function (Blueprint $table) {
            $table->foreignUuid('compte_defaut_debit_id')->nullable()->constrained('comptes')->nullOnDelete();
            $table->foreignUuid('compte_defaut_credit_id')->nullable()->constrained('comptes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Suppression : retrait préalable des références pour permettre la suppression du plan comptable.
        Schema::table('journals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('compte_defaut_credit_id');
            $table->dropConstrainedForeignId('compte_defaut_debit_id');
        });
        Schema::table('taxes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('compte_contrepartie_id');
            $table->dropConstrainedForeignId('compte_taxe_deductible_id');
            $table->dropConstrainedForeignId('compte_taxe_collectee_id');
        });
    }
};
