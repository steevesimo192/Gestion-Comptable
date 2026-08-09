<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout PostgreSQL : contraintes locales empêchant montants négatifs, lignes mixtes et périodes inversées.
        // SQLite reste utilisable pour les tests unitaires ; les invariants multi-lignes restent aussi contrôlés par le domaine.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE ligne_ecritures ADD CONSTRAINT ligne_ecritures_debit_credit_exclusif CHECK ((debit > 0 AND credit = 0) OR (credit > 0 AND debit = 0))");
        DB::statement("ALTER TABLE ligne_ecritures ADD CONSTRAINT ligne_ecritures_fonctionnel_positif CHECK (debit_fonctionnel >= 0 AND credit_fonctionnel >= 0)");
        DB::statement("ALTER TABLE ecritures ADD CONSTRAINT ecritures_totaux_positifs CHECK (total_debit >= 0 AND total_credit >= 0 AND total_debit_fonctionnel >= 0 AND total_credit_fonctionnel >= 0)");
        DB::statement("ALTER TABLE ecritures ADD CONSTRAINT ecritures_equilibrees_si_postees CHECK (statut NOT IN ('validee', 'comptabilisee') OR (total_debit = total_credit AND total_debit_fonctionnel = total_credit_fonctionnel AND total_debit > 0))");
        DB::statement('ALTER TABLE exercice_comptables ADD CONSTRAINT exercice_dates_coherentes CHECK (date_debut <= date_fin)');
        DB::statement('ALTER TABLE periode_comptables ADD CONSTRAINT periode_dates_coherentes CHECK (date_debut <= date_fin)');
        DB::statement('ALTER TABLE taux_changes ADD CONSTRAINT taux_changes_taux_positif CHECK (taux > 0 AND devise_source_id <> devise_cible_id)');
        DB::statement('ALTER TABLE taxes ADD CONSTRAINT taxes_taux_coherent CHECK (taux >= 0 AND (montant_fixe IS NULL OR montant_fixe >= 0))');
        DB::statement('ALTER TABLE paiements ADD CONSTRAINT paiements_montant_positif CHECK (montant > 0 AND frais >= 0)');

        // Ajout PostgreSQL : une écriture comptabilisée devient immuable ; une correction passe par une extourne.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION empecher_mutation_ecriture_comptabilisee() RETURNS trigger AS $$
            BEGIN
                IF (TG_OP = 'DELETE' AND OLD.statut = 'comptabilisee')
                   OR (TG_OP = 'UPDATE' AND OLD.statut = 'comptabilisee') THEN
                    RAISE EXCEPTION 'Une écriture comptabilisée est immuable ; créez une extourne.';
                END IF;
                RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ecritures_immutables_apres_comptabilisation
            BEFORE UPDATE OR DELETE ON ecritures
            FOR EACH ROW EXECUTE FUNCTION empecher_mutation_ecriture_comptabilisee();
            SQL);

        // Ajout PostgreSQL : les lignes d'une écriture comptabilisée héritent de la même immutabilité.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION empecher_mutation_ligne_comptabilisee() RETURNS trigger AS $$
            DECLARE
                ecriture_cible uuid;
            BEGIN
                ecriture_cible := CASE WHEN TG_OP = 'INSERT' THEN NEW.ecriture_id ELSE OLD.ecriture_id END;
                IF EXISTS (SELECT 1 FROM ecritures WHERE id = ecriture_cible AND statut = 'comptabilisee') THEN
                    RAISE EXCEPTION 'Les lignes d une écriture comptabilisée sont immuables.';
                END IF;
                RETURN CASE WHEN TG_OP = 'DELETE' THEN OLD ELSE NEW END;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ligne_ecritures_immutables_apres_comptabilisation
            BEFORE INSERT OR UPDATE OR DELETE ON ligne_ecritures
            FOR EACH ROW EXECUTE FUNCTION empecher_mutation_ligne_comptabilisee();
            SQL);

        // Ajout PostgreSQL : le journal d'audit est append-only afin de conserver une preuve fiable.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION empecher_mutation_journal_audit() RETURNS trigger AS $$
            BEGIN
                RAISE EXCEPTION 'Le journal d audit est immuable.';
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER journaux_audit_immutables
            BEFORE UPDATE OR DELETE ON journaux_audit
            FOR EACH ROW EXECUTE FUNCTION empecher_mutation_journal_audit();
            SQL);
    }

    public function down(): void
    {
        // Suppression PostgreSQL : retrait explicite des déclencheurs avant leurs fonctions et contraintes.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS journaux_audit_immutables ON journaux_audit; DROP FUNCTION IF EXISTS empecher_mutation_journal_audit();');
        DB::unprepared('DROP TRIGGER IF EXISTS ligne_ecritures_immutables_apres_comptabilisation ON ligne_ecritures; DROP FUNCTION IF EXISTS empecher_mutation_ligne_comptabilisee();');
        DB::unprepared('DROP TRIGGER IF EXISTS ecritures_immutables_apres_comptabilisation ON ecritures; DROP FUNCTION IF EXISTS empecher_mutation_ecriture_comptabilisee();');
        DB::statement('ALTER TABLE paiements DROP CONSTRAINT IF EXISTS paiements_montant_positif');
        DB::statement('ALTER TABLE taxes DROP CONSTRAINT IF EXISTS taxes_taux_coherent');
        DB::statement('ALTER TABLE taux_changes DROP CONSTRAINT IF EXISTS taux_changes_taux_positif');
        DB::statement('ALTER TABLE periode_comptables DROP CONSTRAINT IF EXISTS periode_dates_coherentes');
        DB::statement('ALTER TABLE exercice_comptables DROP CONSTRAINT IF EXISTS exercice_dates_coherentes');
        DB::statement('ALTER TABLE ecritures DROP CONSTRAINT IF EXISTS ecritures_equilibrees_si_postees');
        DB::statement('ALTER TABLE ecritures DROP CONSTRAINT IF EXISTS ecritures_totaux_positifs');
        DB::statement('ALTER TABLE ligne_ecritures DROP CONSTRAINT IF EXISTS ligne_ecritures_fonctionnel_positif');
        DB::statement('ALTER TABLE ligne_ecritures DROP CONSTRAINT IF EXISTS ligne_ecritures_debit_credit_exclusif');
    }
};
