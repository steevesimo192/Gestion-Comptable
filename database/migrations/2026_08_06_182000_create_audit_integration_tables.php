<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : verrouillages transversaux au-delà d'une période, par date et par type d'opération.
        Schema::create('verrouillages_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->string('type', 30);
            $table->date('date_limite');
            $table->string('portee', 30)->default('toutes_ecritures');
            $table->text('motif');
            $table->uuid('verrouille_par_id')->nullable();
            $table->timestampTz('verrouille_le');
            $table->timestampsTz();
        });

        // Ajout : pièces justificatives polymorphes conservant hash et métadonnées probantes.
        Schema::create('documents_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->string('documentable_type', 80);
            $table->uuid('documentable_id');
            $table->string('nom_original');
            $table->string('chemin_stockage');
            $table->string('type_mime', 100)->nullable();
            $table->unsignedBigInteger('taille_octets')->nullable();
            $table->string('empreinte_sha256', 64);
            $table->uuid('depose_par_id')->nullable();
            $table->timestampsTz();
            $table->index(['documentable_type', 'documentable_id']);
        });

        // Ajout : journal d'audit immuable de toutes les mutations comptables sensibles.
        Schema::create('journaux_audit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->nullable()->constrained('dossiers_comptables')->nullOnDelete();
            $table->uuid('acteur_id')->nullable();
            $table->string('acteur_type', 30)->default('utilisateur');
            $table->string('action', 50);
            $table->string('entite_type', 80);
            $table->uuid('entite_id');
            $table->json('valeurs_avant')->nullable();
            $table->json('valeurs_apres')->nullable();
            $table->string('adresse_ip', 45)->nullable();
            $table->string('correlation_id', 100)->nullable();
            $table->string('empreinte_precedente', 64)->nullable();
            $table->string('empreinte', 64)->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['entite_type', 'entite_id']);
            $table->index(['dossier_comptable_id', 'created_at']);
        });

        // Ajout : déduplication des commandes reçues pour garantir l'idempotence du microservice.
        Schema::create('cles_idempotence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->nullable()->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->string('cle', 150);
            $table->string('operation', 80);
            $table->string('empreinte_requete', 64);
            $table->unsignedSmallInteger('code_reponse')->nullable();
            $table->json('reponse')->nullable();
            $table->timestampTz('expire_le');
            $table->timestampsTz();
            $table->unique(['dossier_comptable_id', 'cle', 'operation']);
        });

        // Ajout : boîte de sortie transactionnelle pour publier les événements comptables sans perte.
        Schema::create('evenements_sortants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->nullable()->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->string('type', 100);
            $table->string('agregat_type', 80);
            $table->uuid('agregat_id');
            $table->unsignedInteger('version_agregat')->default(1);
            $table->json('contenu');
            $table->string('correlation_id', 100)->nullable();
            $table->timestampTz('publie_le')->nullable();
            $table->unsignedSmallInteger('tentatives')->default(0);
            $table->text('derniere_erreur')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['publie_le', 'created_at']);
        });
    }

    public function down(): void
    {
        // Suppression : tables techniques retirées sans toucher aux écritures comptables elles-mêmes.
        Schema::dropIfExists('evenements_sortants');
        Schema::dropIfExists('cles_idempotence');
        Schema::dropIfExists('journaux_audit');
        Schema::dropIfExists('documents_comptables');
        Schema::dropIfExists('verrouillages_comptables');
    }
};
