<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajout : politique explicite des soldes inverses pour distinguer une anomalie d'un cas autorisé.
        Schema::table('comptes', function (Blueprint $table) {
            $table->boolean('autorise_solde_inverse')->default(false);
        });

        // Ajout : projection périodique des mouvements comptabilisés, sans montant signé négatif ambigu.
        Schema::create('soldes_comptables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dossier_comptable_id')->constrained('dossiers_comptables')->cascadeOnDelete();
            $table->foreignUuid('exercice_comptable_id')->constrained('exercice_comptables')->cascadeOnDelete();
            $table->foreignUuid('periode_comptable_id')->constrained('periode_comptables')->cascadeOnDelete();
            $table->foreignUuid('compte_id')->constrained('comptes')->cascadeOnDelete();
            $table->decimal('total_debit', 19, 4)->default(0);
            $table->decimal('total_credit', 19, 4)->default(0);
            $table->decimal('solde_debiteur', 19, 4)->default(0);
            $table->decimal('solde_crediteur', 19, 4)->default(0);
            $table->decimal('solde_normal', 19, 4)->default(0);
            $table->decimal('solde_anormal', 19, 4)->default(0);
            $table->timestampTz('updated_at')->useCurrent();
            $table->unique(['exercice_comptable_id', 'periode_comptable_id', 'compte_id'], 'soldes_comptables_unicite');
            $table->index(['dossier_comptable_id', 'compte_id']);
        });

        // Ajout : agrégats de consommation et restes disponibles pilotés exclusivement par les déclencheurs.
        Schema::table('paiements', function (Blueprint $table) {
            $table->decimal('montant_affecte', 19, 4)->default(0);
            $table->decimal('solde_non_affecte', 19, 4)->default(0);
        });
        Schema::table('operations_bancaires', function (Blueprint $table) {
            $table->decimal('montant_rapproche', 19, 4)->default(0);
            $table->decimal('solde_a_rapprocher', 19, 4)->default(0);
        });
        Schema::table('ligne_ecritures', function (Blueprint $table) {
            $table->decimal('montant_rapproche', 19, 4)->default(0);
        });
        Schema::table('immobilisations', function (Blueprint $table) {
            $table->decimal('amortissement_cumule', 19, 4)->default(0);
        });
        Schema::table('declarations_fiscales', function (Blueprint $table) {
            $table->decimal('credit_reportable', 19, 4)->default(0);
        });
        Schema::table('ligne_declarations_fiscales', function (Blueprint $table) {
            $table->string('nature', 20)->default('collectee');
        });
        Schema::table('budgets', function (Blueprint $table) {
            $table->decimal('total_debit', 19, 4)->default(0);
            $table->decimal('total_credit', 19, 4)->default(0);
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // Ajout PostgreSQL : contraintes simples, immédiates et indépendantes des chemins applicatifs.
        DB::statement("ALTER TABLE comptes ADD CONSTRAINT comptes_nature_valide CHECK (nature IN ('debit', 'credit'))");
        DB::statement('ALTER TABLE soldes_comptables ADD CONSTRAINT soldes_comptables_non_negatifs CHECK (total_debit >= 0 AND total_credit >= 0 AND solde_debiteur >= 0 AND solde_crediteur >= 0 AND solde_normal >= 0 AND solde_anormal >= 0)');
        DB::statement('ALTER TABLE pieces_comptables ADD CONSTRAINT pieces_comptables_soldes_non_negatifs CHECK (sous_total_ht >= 0 AND total_remise >= 0 AND total_taxes >= 0 AND total_ttc >= 0 AND total_regle >= 0 AND total_regle <= total_ttc AND solde_du >= 0)');
        DB::statement('ALTER TABLE ligne_pieces_comptables ADD CONSTRAINT ligne_pieces_montants_non_negatifs CHECK (quantite > 0 AND prix_unitaire >= 0 AND taux_remise >= 0 AND montant_remise >= 0 AND base_hors_taxe >= 0 AND montant_taxe >= 0 AND montant_ttc >= 0)');
        DB::statement('ALTER TABLE echeances ADD CONSTRAINT echeances_montants_coherents CHECK (montant > 0 AND montant_regle >= 0 AND montant_regle <= montant)');
        DB::statement('ALTER TABLE paiements ADD CONSTRAINT paiements_affectations_coherentes CHECK (montant_affecte >= 0 AND solde_non_affecte >= 0 AND montant_affecte <= montant)');
        DB::statement('ALTER TABLE affectations_paiements ADD CONSTRAINT affectations_paiements_montant_positif CHECK (montant > 0 AND escompte >= 0)');
        DB::statement('ALTER TABLE ligne_lettrages ADD CONSTRAINT ligne_lettrages_montant_positif CHECK (montant > 0)');
        DB::statement('ALTER TABLE rapprochements_bancaires ADD CONSTRAINT rapprochements_montant_positif CHECK (montant_rapproche > 0)');
        DB::statement('ALTER TABLE lettrages ADD CONSTRAINT lettrages_soldes_non_negatifs CHECK (total_debit >= 0 AND total_credit >= 0 AND solde >= 0)');
        DB::statement('ALTER TABLE operations_bancaires ADD CONSTRAINT operations_rapprochement_non_negatif CHECK (montant_rapproche >= 0 AND montant_rapproche <= ABS(montant) AND solde_a_rapprocher >= 0)');
        DB::statement('ALTER TABLE ligne_ecritures ADD CONSTRAINT ligne_ecritures_rapprochement_non_negatif CHECK (montant_rapproche >= 0 AND montant_rapproche <= GREATEST(debit_fonctionnel, credit_fonctionnel))');
        DB::statement('ALTER TABLE immobilisations ADD CONSTRAINT immobilisations_valeurs_coherentes CHECK (cout_acquisition >= 0 AND valeur_residuelle >= 0 AND valeur_residuelle <= cout_acquisition AND amortissement_cumule >= 0 AND valeur_nette_comptable >= valeur_residuelle)');
        DB::statement('ALTER TABLE amortissements ADD CONSTRAINT amortissements_montants_non_negatifs CHECK (base_amortissable >= 0 AND dotation >= 0 AND amortissement_cumule >= 0 AND valeur_nette >= 0)');
        DB::statement("ALTER TABLE ligne_declarations_fiscales ADD CONSTRAINT ligne_declarations_nature_valide CHECK (nature IN ('collectee', 'deductible', 'credit_anterieur'))");
        DB::statement('ALTER TABLE ligne_declarations_fiscales ADD CONSTRAINT ligne_declarations_montants_non_negatifs CHECK (base >= 0 AND montant >= 0)');
        DB::statement('ALTER TABLE declarations_fiscales ADD CONSTRAINT declarations_fiscales_montants_non_negatifs CHECK (base_imposable >= 0 AND taxe_collectee >= 0 AND taxe_deductible >= 0 AND credit_anterieur >= 0 AND montant_du >= 0 AND credit_reportable >= 0)');
        DB::statement('ALTER TABLE ligne_budgets ADD CONSTRAINT ligne_budgets_montants_non_negatifs CHECK (montant_debit >= 0 AND montant_credit >= 0)');

        $this->createJournalTriggers();
        $this->createDocumentAndPaymentTriggers();
        $this->createMatchingTriggers();
        $this->createAssetTaxAndBudgetTriggers();

        // Ajout PostgreSQL : reprise des écritures déjà comptabilisées pour initialiser la projection lors d'une mise à niveau.
        DB::unprepared(<<<'SQL'
            DO $$ DECLARE v_ecriture record;
            BEGIN
                FOR v_ecriture IN SELECT id FROM ecritures WHERE statut = 'comptabilisee' ORDER BY date_comptable, id LOOP
                    PERFORM projeter_solde_compte(v_ecriture.id);
                END LOOP;
            END $$;
            SQL);
    }

    private function createJournalTriggers(): void
    {
        // Ajout PostgreSQL : toute mutation de ligne recalcule les quatre totaux de son écriture par agrégation.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION actualiser_totaux_ecriture(p_ecriture_id uuid) RETURNS void AS $$
            BEGIN
                IF p_ecriture_id IS NULL THEN RETURN; END IF;
                PERFORM 1 FROM ecritures WHERE id = p_ecriture_id FOR UPDATE;
                UPDATE ecritures
                SET total_debit = COALESCE(x.total_debit, 0),
                    total_credit = COALESCE(x.total_credit, 0),
                    total_debit_fonctionnel = COALESCE(x.total_debit_fonctionnel, 0),
                    total_credit_fonctionnel = COALESCE(x.total_credit_fonctionnel, 0)
                FROM (
                    SELECT COALESCE(SUM(debit), 0) total_debit,
                           COALESCE(SUM(credit), 0) total_credit,
                           COALESCE(SUM(debit_fonctionnel), 0) total_debit_fonctionnel,
                           COALESCE(SUM(credit_fonctionnel), 0) total_credit_fonctionnel
                    FROM ligne_ecritures WHERE ecriture_id = p_ecriture_id
                ) x
                WHERE ecritures.id = p_ecriture_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION propager_totaux_ligne_ecriture() RETURNS trigger AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN PERFORM actualiser_totaux_ecriture(OLD.ecriture_id); END IF;
                IF TG_OP IN ('INSERT', 'UPDATE') AND (TG_OP <> 'UPDATE' OR NEW.ecriture_id IS DISTINCT FROM OLD.ecriture_id) THEN
                    PERFORM actualiser_totaux_ecriture(NEW.ecriture_id);
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ligne_ecritures_actualiser_parent
            AFTER INSERT OR UPDATE OR DELETE ON ligne_ecritures
            FOR EACH ROW EXECUTE FUNCTION propager_totaux_ligne_ecriture();
            SQL);

        // Ajout PostgreSQL : contrôle de cohérence du dossier et interdiction de poster une écriture incomplète.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION valider_ligne_ecriture() RETURNS trigger AS $$
            DECLARE v_dossier uuid; v_accepte boolean;
            BEGIN
                SELECT dossier_comptable_id INTO v_dossier FROM ecritures WHERE id = NEW.ecriture_id FOR UPDATE;
                SELECT accepte_ecritures INTO v_accepte FROM comptes WHERE id = NEW.compte_id AND dossier_comptable_id = v_dossier;
                IF v_dossier IS NULL OR v_accepte IS NULL THEN RAISE EXCEPTION 'Le compte et l écriture doivent appartenir au même dossier.'; END IF;
                IF NOT v_accepte THEN RAISE EXCEPTION 'Ce compte récapitulatif n accepte pas les écritures.'; END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ligne_ecritures_valider_coherence
            BEFORE INSERT OR UPDATE OF ecriture_id, compte_id ON ligne_ecritures
            FOR EACH ROW EXECUTE FUNCTION valider_ligne_ecriture();

            CREATE FUNCTION valider_comptabilisation_ecriture() RETURNS trigger AS $$
            DECLARE v_lignes integer; v_dossier_journal uuid; v_exercice uuid; v_periode uuid;
            BEGIN
                IF NEW.statut = 'comptabilisee' AND OLD.statut IS DISTINCT FROM 'comptabilisee' THEN
                    SELECT COUNT(*) INTO v_lignes FROM ligne_ecritures WHERE ecriture_id = NEW.id;
                    SELECT dossier_comptable_id INTO v_dossier_journal FROM journals WHERE id = NEW.journal_id;
                    SELECT exercice_comptable_id INTO v_exercice FROM periode_comptables WHERE id = NEW.periode_comptable_id;
                    SELECT id INTO v_periode FROM periode_comptables WHERE id = NEW.periode_comptable_id AND statut = 'ouverte';
                    IF NEW.periode_comptable_id IS NULL OR v_lignes < 2 THEN RAISE EXCEPTION 'Une écriture comptabilisée exige une période et au moins deux lignes.'; END IF;
                    IF NEW.total_debit <> NEW.total_credit OR NEW.total_debit_fonctionnel <> NEW.total_credit_fonctionnel OR NEW.total_debit <= 0 THEN
                        RAISE EXCEPTION 'Une écriture comptabilisée doit être équilibrée et non vide.';
                    END IF;
                    IF v_dossier_journal IS DISTINCT FROM NEW.dossier_comptable_id THEN RAISE EXCEPTION 'Le journal appartient à un autre dossier.'; END IF;
                    IF v_exercice IS DISTINCT FROM NEW.exercice_comptable_id OR v_periode IS NULL THEN RAISE EXCEPTION 'La période doit être ouverte et appartenir à l exercice.'; END IF;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ecritures_valider_avant_comptabilisation
            BEFORE UPDATE OF statut ON ecritures
            FOR EACH ROW EXECUTE FUNCTION valider_comptabilisation_ecriture();
            SQL);

        // Ajout PostgreSQL : la comptabilisation alimente atomiquement la projection de soldes par compte et période.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION projeter_solde_compte(p_ecriture_id uuid) RETURNS void AS $$
            DECLARE mouvement record; v_nature text; v_inverse boolean; v_anormal numeric;
            BEGIN
                FOR mouvement IN
                    SELECT e.dossier_comptable_id, e.exercice_comptable_id, e.periode_comptable_id, l.compte_id,
                           SUM(l.debit_fonctionnel) total_debit, SUM(l.credit_fonctionnel) total_credit
                    FROM ecritures e JOIN ligne_ecritures l ON l.ecriture_id = e.id
                    WHERE e.id = p_ecriture_id
                    GROUP BY e.dossier_comptable_id, e.exercice_comptable_id, e.periode_comptable_id, l.compte_id
                LOOP
                    INSERT INTO soldes_comptables (
                        id, dossier_comptable_id, exercice_comptable_id, periode_comptable_id, compte_id,
                        total_debit, total_credit, solde_debiteur, solde_crediteur, solde_normal, solde_anormal, updated_at
                    ) VALUES (
                        gen_random_uuid(), mouvement.dossier_comptable_id, mouvement.exercice_comptable_id, mouvement.periode_comptable_id, mouvement.compte_id,
                        mouvement.total_debit, mouvement.total_credit,
                        GREATEST(mouvement.total_debit - mouvement.total_credit, 0),
                        GREATEST(mouvement.total_credit - mouvement.total_debit, 0), 0, 0, now()
                    )
                    ON CONFLICT (exercice_comptable_id, periode_comptable_id, compte_id) DO UPDATE
                    SET total_debit = soldes_comptables.total_debit + EXCLUDED.total_debit,
                        total_credit = soldes_comptables.total_credit + EXCLUDED.total_credit,
                        solde_debiteur = GREATEST((soldes_comptables.total_debit + EXCLUDED.total_debit) - (soldes_comptables.total_credit + EXCLUDED.total_credit), 0),
                        solde_crediteur = GREATEST((soldes_comptables.total_credit + EXCLUDED.total_credit) - (soldes_comptables.total_debit + EXCLUDED.total_debit), 0),
                        updated_at = now();

                    SELECT nature, autorise_solde_inverse INTO v_nature, v_inverse FROM comptes WHERE id = mouvement.compte_id;
                    UPDATE soldes_comptables SET
                        solde_normal = CASE WHEN v_nature = 'debit' THEN solde_debiteur ELSE solde_crediteur END,
                        solde_anormal = CASE WHEN v_nature = 'debit' THEN solde_crediteur ELSE solde_debiteur END
                    WHERE exercice_comptable_id = mouvement.exercice_comptable_id
                      AND periode_comptable_id = mouvement.periode_comptable_id AND compte_id = mouvement.compte_id
                    RETURNING solde_anormal INTO v_anormal;
                    IF v_anormal > 0 AND NOT v_inverse THEN
                        RAISE EXCEPTION 'Le compte % présente un solde inverse non autorisé.', mouvement.compte_id;
                    END IF;
                END LOOP;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION comptabiliser_dans_projection() RETURNS trigger AS $$
            BEGIN
                IF NEW.statut = 'comptabilisee' AND OLD.statut IS DISTINCT FROM 'comptabilisee' THEN
                    PERFORM projeter_solde_compte(NEW.id);
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ecritures_projeter_soldes
            AFTER UPDATE OF statut ON ecritures
            FOR EACH ROW EXECUTE FUNCTION comptabiliser_dans_projection();
            SQL);

        // Ajout PostgreSQL : vue hiérarchique calculant les soldes des comptes parents sans double comptage stocké.
        DB::unprepared(<<<'SQL'
            CREATE VIEW vue_soldes_comptables_agreges AS
            WITH RECURSIVE hierarchie AS (
                SELECT id compte_feuille_id, id compte_agrege_id FROM comptes
                UNION ALL
                SELECT h.compte_feuille_id, c.parent_id
                FROM hierarchie h JOIN comptes c ON c.id = h.compte_agrege_id
                WHERE c.parent_id IS NOT NULL
            )
            SELECT s.dossier_comptable_id, s.exercice_comptable_id, s.periode_comptable_id,
                   h.compte_agrege_id compte_id,
                   SUM(s.total_debit) total_debit, SUM(s.total_credit) total_credit,
                   GREATEST(SUM(s.total_debit) - SUM(s.total_credit), 0) solde_debiteur,
                   GREATEST(SUM(s.total_credit) - SUM(s.total_debit), 0) solde_crediteur
            FROM soldes_comptables s JOIN hierarchie h ON h.compte_feuille_id = s.compte_id
            GROUP BY s.dossier_comptable_id, s.exercice_comptable_id, s.periode_comptable_id, h.compte_agrege_id;
            SQL);
    }

    private function createDocumentAndPaymentTriggers(): void
    {
        // Ajout PostgreSQL : les totaux d'une pièce sont la somme de ses lignes, avec refus d'un total inférieur aux règlements.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION actualiser_totaux_piece(p_piece_id uuid) RETURNS void AS $$
            DECLARE v_total_regle numeric; v_ht numeric; v_remise numeric; v_taxes numeric; v_ttc numeric;
            BEGIN
                IF p_piece_id IS NULL THEN RETURN; END IF;
                SELECT total_regle INTO v_total_regle FROM pieces_comptables WHERE id = p_piece_id FOR UPDATE;
                IF NOT FOUND THEN RETURN; END IF;
                SELECT COALESCE(SUM(base_hors_taxe), 0), COALESCE(SUM(montant_remise), 0),
                       COALESCE(SUM(montant_taxe), 0), COALESCE(SUM(montant_ttc), 0)
                INTO v_ht, v_remise, v_taxes, v_ttc
                FROM ligne_pieces_comptables WHERE piece_comptable_id = p_piece_id;
                IF v_total_regle > v_ttc THEN
                    RAISE EXCEPTION 'Le nouveau total TTC (%) ne peut pas être inférieur au montant déjà réglé (%).', v_ttc, v_total_regle;
                END IF;
                UPDATE pieces_comptables SET sous_total_ht = v_ht, total_remise = v_remise,
                    total_taxes = v_taxes, total_ttc = v_ttc, solde_du = v_ttc - v_total_regle
                WHERE id = p_piece_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION propager_totaux_ligne_piece() RETURNS trigger AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN PERFORM actualiser_totaux_piece(OLD.piece_comptable_id); END IF;
                IF TG_OP IN ('INSERT', 'UPDATE') AND (TG_OP <> 'UPDATE' OR NEW.piece_comptable_id IS DISTINCT FROM OLD.piece_comptable_id) THEN
                    PERFORM actualiser_totaux_piece(NEW.piece_comptable_id);
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ligne_pieces_actualiser_parent
            AFTER INSERT OR UPDATE OR DELETE ON ligne_pieces_comptables
            FOR EACH ROW EXECUTE FUNCTION propager_totaux_ligne_piece();
            SQL);

        // Ajout PostgreSQL : un paiement initialise son disponible et refuse toute réduction sous le montant déjà affecté.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION initialiser_solde_paiement() RETURNS trigger AS $$
            DECLARE v_affecte numeric;
            BEGIN
                IF NEW.montant <= 0 THEN RAISE EXCEPTION 'Le montant du paiement doit être strictement positif.'; END IF;
                IF TG_OP = 'INSERT' THEN
                    NEW.montant_affecte := 0;
                    NEW.solde_non_affecte := NEW.montant;
                ELSE
                    SELECT COALESCE(SUM(montant), 0) INTO v_affecte FROM affectations_paiements WHERE paiement_id = NEW.id;
                    IF v_affecte > NEW.montant THEN RAISE EXCEPTION 'Le paiement ne peut pas devenir inférieur au montant déjà affecté.'; END IF;
                    NEW.montant_affecte := v_affecte;
                    NEW.solde_non_affecte := NEW.montant - v_affecte;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER paiements_initialiser_solde
            BEFORE INSERT OR UPDATE OF montant ON paiements
            FOR EACH ROW EXECUTE FUNCTION initialiser_solde_paiement();
            SQL);

        // Ajout PostgreSQL : verrouillage et plafonnement atomiques des affectations par paiement, pièce et échéance.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION valider_affectation_paiement() RETURNS trigger AS $$
            DECLARE v_paiement numeric; v_piece numeric; v_echeance numeric; v_piece_echeance uuid;
                    v_deja_paiement numeric; v_deja_piece numeric; v_deja_echeance numeric;
            BEGIN
                SELECT montant INTO v_paiement FROM paiements WHERE id = NEW.paiement_id FOR UPDATE;
                SELECT total_ttc INTO v_piece FROM pieces_comptables WHERE id = NEW.piece_comptable_id FOR UPDATE;
                SELECT COALESCE(SUM(montant), 0) INTO v_deja_paiement FROM affectations_paiements
                    WHERE paiement_id = NEW.paiement_id AND id IS DISTINCT FROM NEW.id;
                SELECT COALESCE(SUM(montant), 0) INTO v_deja_piece FROM affectations_paiements
                    WHERE piece_comptable_id = NEW.piece_comptable_id AND id IS DISTINCT FROM NEW.id;
                IF v_deja_paiement + NEW.montant > v_paiement THEN RAISE EXCEPTION 'L affectation dépasse le solde disponible du paiement.'; END IF;
                IF v_deja_piece + NEW.montant > v_piece THEN RAISE EXCEPTION 'L affectation dépasse le solde dû de la pièce.'; END IF;
                IF NEW.echeance_id IS NOT NULL THEN
                    SELECT montant, piece_comptable_id INTO v_echeance, v_piece_echeance FROM echeances WHERE id = NEW.echeance_id FOR UPDATE;
                    IF v_piece_echeance IS DISTINCT FROM NEW.piece_comptable_id THEN RAISE EXCEPTION 'L échéance n appartient pas à la pièce affectée.'; END IF;
                    SELECT COALESCE(SUM(montant), 0) INTO v_deja_echeance FROM affectations_paiements
                        WHERE echeance_id = NEW.echeance_id AND id IS DISTINCT FROM NEW.id;
                    IF v_deja_echeance + NEW.montant > v_echeance THEN RAISE EXCEPTION 'L affectation dépasse le solde de l échéance.'; END IF;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER affectations_paiements_valider
            BEFORE INSERT OR UPDATE ON affectations_paiements
            FOR EACH ROW EXECUTE FUNCTION valider_affectation_paiement();
            SQL);

        // Ajout PostgreSQL : chaque affectation recalcule les restes du paiement, de la pièce et de l'échéance concernée.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION actualiser_affectations_paiement(p_paiement_id uuid) RETURNS void AS $$
            DECLARE v_total numeric;
            BEGIN
                IF p_paiement_id IS NULL THEN RETURN; END IF;
                PERFORM 1 FROM paiements WHERE id = p_paiement_id FOR UPDATE;
                SELECT COALESCE(SUM(montant), 0) INTO v_total FROM affectations_paiements WHERE paiement_id = p_paiement_id;
                UPDATE paiements SET montant_affecte = v_total, solde_non_affecte = montant - v_total WHERE id = p_paiement_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION actualiser_reglements_piece(p_piece_id uuid) RETURNS void AS $$
            DECLARE v_total numeric;
            BEGIN
                IF p_piece_id IS NULL THEN RETURN; END IF;
                PERFORM 1 FROM pieces_comptables WHERE id = p_piece_id FOR UPDATE;
                SELECT COALESCE(SUM(montant), 0) INTO v_total FROM affectations_paiements WHERE piece_comptable_id = p_piece_id;
                UPDATE pieces_comptables SET total_regle = v_total, solde_du = total_ttc - v_total WHERE id = p_piece_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION actualiser_reglements_echeance(p_echeance_id uuid) RETURNS void AS $$
            DECLARE v_total numeric;
            BEGIN
                IF p_echeance_id IS NULL THEN RETURN; END IF;
                PERFORM 1 FROM echeances WHERE id = p_echeance_id FOR UPDATE;
                SELECT COALESCE(SUM(montant), 0) INTO v_total FROM affectations_paiements WHERE echeance_id = p_echeance_id;
                UPDATE echeances SET montant_regle = v_total,
                    statut = CASE WHEN v_total = 0 THEN 'ouverte' WHEN v_total = montant THEN 'reglee' ELSE 'partielle' END
                WHERE id = p_echeance_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION propager_affectation_paiement() RETURNS trigger AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN
                    PERFORM actualiser_affectations_paiement(OLD.paiement_id);
                    PERFORM actualiser_reglements_piece(OLD.piece_comptable_id);
                    PERFORM actualiser_reglements_echeance(OLD.echeance_id);
                END IF;
                IF TG_OP IN ('INSERT', 'UPDATE') THEN
                    PERFORM actualiser_affectations_paiement(NEW.paiement_id);
                    PERFORM actualiser_reglements_piece(NEW.piece_comptable_id);
                    PERFORM actualiser_reglements_echeance(NEW.echeance_id);
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER affectations_paiements_actualiser_parents
            AFTER INSERT OR UPDATE OR DELETE ON affectations_paiements
            FOR EACH ROW EXECUTE FUNCTION propager_affectation_paiement();
            SQL);
    }

    private function createMatchingTriggers(): void
    {
        // Ajout PostgreSQL : les totaux d'un nouveau lettrage démarrent à zéro et ne peuvent pas être injectés par le client.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION initialiser_lettrage() RETURNS trigger AS $$
            BEGIN
                NEW.total_debit := 0; NEW.total_credit := 0; NEW.solde := 0; NEW.statut := 'partiel';
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER lettrages_initialiser_totaux
            BEFORE INSERT ON lettrages
            FOR EACH ROW EXECUTE FUNCTION initialiser_lettrage();
            SQL);

        // Ajout PostgreSQL : une ligne d'écriture ne peut être lettrée au-delà de son débit ou de son crédit.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION valider_ligne_lettrage() RETURNS trigger AS $$
            DECLARE v_capacite numeric; v_consomme numeric; v_compte uuid; v_compte_lettrage uuid;
            BEGIN
                SELECT GREATEST(debit_fonctionnel, credit_fonctionnel), compte_id INTO v_capacite, v_compte
                FROM ligne_ecritures WHERE id = NEW.ligne_ecriture_id FOR UPDATE;
                SELECT compte_id INTO v_compte_lettrage FROM lettrages WHERE id = NEW.lettrage_id FOR UPDATE;
                IF v_compte IS DISTINCT FROM v_compte_lettrage THEN RAISE EXCEPTION 'La ligne et le lettrage doivent utiliser le même compte.'; END IF;
                SELECT COALESCE(SUM(montant), 0) INTO v_consomme FROM ligne_lettrages
                    WHERE ligne_ecriture_id = NEW.ligne_ecriture_id AND id IS DISTINCT FROM NEW.id;
                IF v_consomme + NEW.montant > v_capacite THEN RAISE EXCEPTION 'Le lettrage dépasse le montant disponible de la ligne.'; END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ligne_lettrages_valider
            BEFORE INSERT OR UPDATE ON ligne_lettrages
            FOR EACH ROW EXECUTE FUNCTION valider_ligne_lettrage();

            CREATE FUNCTION actualiser_lettrage(p_lettrage_id uuid) RETURNS void AS $$
            DECLARE v_debit numeric; v_credit numeric;
            BEGIN
                IF p_lettrage_id IS NULL THEN RETURN; END IF;
                PERFORM 1 FROM lettrages WHERE id = p_lettrage_id FOR UPDATE;
                SELECT COALESCE(SUM(CASE WHEN le.debit_fonctionnel > 0 THEN ll.montant ELSE 0 END), 0),
                       COALESCE(SUM(CASE WHEN le.credit_fonctionnel > 0 THEN ll.montant ELSE 0 END), 0)
                INTO v_debit, v_credit FROM ligne_lettrages ll
                JOIN ligne_ecritures le ON le.id = ll.ligne_ecriture_id WHERE ll.lettrage_id = p_lettrage_id;
                UPDATE lettrages SET total_debit = v_debit, total_credit = v_credit, solde = ABS(v_debit - v_credit),
                    statut = CASE WHEN v_debit > 0 AND v_debit = v_credit THEN 'complet' ELSE 'partiel' END
                WHERE id = p_lettrage_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION propager_ligne_lettrage() RETURNS trigger AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN PERFORM actualiser_lettrage(OLD.lettrage_id); END IF;
                IF TG_OP IN ('INSERT', 'UPDATE') THEN PERFORM actualiser_lettrage(NEW.lettrage_id); END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ligne_lettrages_actualiser_parent
            AFTER INSERT OR UPDATE OR DELETE ON ligne_lettrages
            FOR EACH ROW EXECUTE FUNCTION propager_ligne_lettrage();
            SQL);

        // Ajout PostgreSQL : le rapprochement bancaire est plafonné des deux côtés et met à jour leurs soldes non négatifs.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION initialiser_solde_operation_bancaire() RETURNS trigger AS $$
            DECLARE v_rapproche numeric;
            BEGIN
                IF TG_OP = 'INSERT' THEN v_rapproche := 0; ELSE
                    SELECT COALESCE(SUM(montant_rapproche), 0) INTO v_rapproche FROM rapprochements_bancaires WHERE operation_bancaire_id = NEW.id;
                END IF;
                IF v_rapproche > ABS(NEW.montant) THEN RAISE EXCEPTION 'Le nouveau montant bancaire est inférieur au montant déjà rapproché.'; END IF;
                NEW.montant_rapproche := v_rapproche;
                NEW.solde_a_rapprocher := ABS(NEW.montant) - v_rapproche;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER operations_bancaires_initialiser_solde
            BEFORE INSERT OR UPDATE OF montant ON operations_bancaires
            FOR EACH ROW EXECUTE FUNCTION initialiser_solde_operation_bancaire();

            CREATE FUNCTION valider_rapprochement_bancaire() RETURNS trigger AS $$
            DECLARE v_operation numeric; v_ligne numeric; v_operation_consommee numeric; v_ligne_consommee numeric;
            BEGIN
                SELECT ABS(montant) INTO v_operation FROM operations_bancaires WHERE id = NEW.operation_bancaire_id FOR UPDATE;
                SELECT GREATEST(debit_fonctionnel, credit_fonctionnel) INTO v_ligne FROM ligne_ecritures WHERE id = NEW.ligne_ecriture_id FOR UPDATE;
                SELECT COALESCE(SUM(montant_rapproche), 0) INTO v_operation_consommee FROM rapprochements_bancaires
                    WHERE operation_bancaire_id = NEW.operation_bancaire_id AND id IS DISTINCT FROM NEW.id;
                SELECT COALESCE(SUM(montant_rapproche), 0) INTO v_ligne_consommee FROM rapprochements_bancaires
                    WHERE ligne_ecriture_id = NEW.ligne_ecriture_id AND id IS DISTINCT FROM NEW.id;
                IF v_operation_consommee + NEW.montant_rapproche > v_operation THEN RAISE EXCEPTION 'Le rapprochement dépasse l opération bancaire.'; END IF;
                IF v_ligne_consommee + NEW.montant_rapproche > v_ligne THEN RAISE EXCEPTION 'Le rapprochement dépasse la ligne comptable.'; END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER rapprochements_bancaires_valider
            BEFORE INSERT OR UPDATE ON rapprochements_bancaires
            FOR EACH ROW EXECUTE FUNCTION valider_rapprochement_bancaire();

            CREATE FUNCTION actualiser_operation_rapprochee(p_operation_id uuid) RETURNS void AS $$
            DECLARE v_total numeric;
            BEGIN
                IF p_operation_id IS NULL THEN RETURN; END IF;
                PERFORM 1 FROM operations_bancaires WHERE id = p_operation_id FOR UPDATE;
                SELECT COALESCE(SUM(montant_rapproche), 0) INTO v_total FROM rapprochements_bancaires WHERE operation_bancaire_id = p_operation_id;
                UPDATE operations_bancaires SET montant_rapproche = v_total, solde_a_rapprocher = ABS(montant) - v_total,
                    statut = CASE WHEN v_total = 0 THEN 'non_rapprochee' WHEN v_total = ABS(montant) THEN 'rapprochee' ELSE 'partiellement_rapprochee' END
                WHERE id = p_operation_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION actualiser_ligne_rapprochee(p_ligne_id uuid) RETURNS void AS $$
            DECLARE v_total numeric; v_capacite numeric;
            BEGIN
                IF p_ligne_id IS NULL THEN RETURN; END IF;
                SELECT GREATEST(debit_fonctionnel, credit_fonctionnel) INTO v_capacite FROM ligne_ecritures WHERE id = p_ligne_id FOR UPDATE;
                SELECT COALESCE(SUM(montant_rapproche), 0) INTO v_total FROM rapprochements_bancaires WHERE ligne_ecriture_id = p_ligne_id;
                UPDATE ligne_ecritures SET montant_rapproche = v_total, rapprochee = (v_total > 0 AND v_total = v_capacite) WHERE id = p_ligne_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION propager_rapprochement_bancaire() RETURNS trigger AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN
                    PERFORM actualiser_operation_rapprochee(OLD.operation_bancaire_id);
                    PERFORM actualiser_ligne_rapprochee(OLD.ligne_ecriture_id);
                END IF;
                IF TG_OP IN ('INSERT', 'UPDATE') THEN
                    PERFORM actualiser_operation_rapprochee(NEW.operation_bancaire_id);
                    PERFORM actualiser_ligne_rapprochee(NEW.ligne_ecriture_id);
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER rapprochements_bancaires_actualiser_parents
            AFTER INSERT OR UPDATE OR DELETE ON rapprochements_bancaires
            FOR EACH ROW EXECUTE FUNCTION propager_rapprochement_bancaire();
            SQL);
    }

    private function createAssetTaxAndBudgetTriggers(): void
    {
        // Ajout PostgreSQL : la valeur nette d'une immobilisation découle uniquement des amortissements comptabilisés.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION initialiser_valeur_immobilisation() RETURNS trigger AS $$
            DECLARE v_total numeric := 0;
            BEGIN
                IF NEW.valeur_residuelle > NEW.cout_acquisition THEN RAISE EXCEPTION 'La valeur résiduelle ne peut pas dépasser le coût d acquisition.'; END IF;
                IF TG_OP = 'UPDATE' THEN
                    SELECT COALESCE(SUM(dotation), 0) INTO v_total FROM amortissements
                    WHERE immobilisation_id = NEW.id AND statut IN ('comptabilise', 'comptabilisee');
                END IF;
                IF v_total > NEW.cout_acquisition - NEW.valeur_residuelle THEN RAISE EXCEPTION 'Le nouveau coût est inférieur à la base déjà amortie.'; END IF;
                NEW.amortissement_cumule := v_total;
                NEW.valeur_nette_comptable := NEW.cout_acquisition - v_total;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER immobilisations_initialiser_valeur
            BEFORE INSERT OR UPDATE OF cout_acquisition, valeur_residuelle ON immobilisations
            FOR EACH ROW EXECUTE FUNCTION initialiser_valeur_immobilisation();

            CREATE FUNCTION actualiser_valeur_immobilisation(p_immobilisation_id uuid) RETURNS void AS $$
            DECLARE v_total numeric; v_cout numeric; v_residuelle numeric;
            BEGIN
                IF p_immobilisation_id IS NULL THEN RETURN; END IF;
                SELECT cout_acquisition, valeur_residuelle INTO v_cout, v_residuelle FROM immobilisations WHERE id = p_immobilisation_id FOR UPDATE;
                IF NOT FOUND THEN RETURN; END IF;
                SELECT COALESCE(SUM(dotation), 0) INTO v_total FROM amortissements
                    WHERE immobilisation_id = p_immobilisation_id AND statut IN ('comptabilise', 'comptabilisee');
                IF v_total > v_cout - v_residuelle THEN RAISE EXCEPTION 'Les amortissements dépassent la base amortissable.'; END IF;
                UPDATE immobilisations SET amortissement_cumule = v_total, valeur_nette_comptable = v_cout - v_total
                WHERE id = p_immobilisation_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION propager_amortissement() RETURNS trigger AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN PERFORM actualiser_valeur_immobilisation(OLD.immobilisation_id); END IF;
                IF TG_OP IN ('INSERT', 'UPDATE') THEN PERFORM actualiser_valeur_immobilisation(NEW.immobilisation_id); END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER amortissements_actualiser_immobilisation
            AFTER INSERT OR UPDATE OR DELETE ON amortissements
            FOR EACH ROW EXECUTE FUNCTION propager_amortissement();
            SQL);

        // Ajout PostgreSQL : les lignes fiscales calculent la taxe nette et isolent le crédit reportable sans valeur négative.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION actualiser_declaration_fiscale(p_declaration_id uuid) RETURNS void AS $$
            DECLARE v_base numeric; v_collectee numeric; v_deductible numeric; v_credit numeric; v_net numeric;
            BEGIN
                IF p_declaration_id IS NULL THEN RETURN; END IF;
                PERFORM 1 FROM declarations_fiscales WHERE id = p_declaration_id FOR UPDATE;
                SELECT COALESCE(SUM(base), 0),
                       COALESCE(SUM(CASE WHEN nature = 'collectee' THEN montant ELSE 0 END), 0),
                       COALESCE(SUM(CASE WHEN nature = 'deductible' THEN montant ELSE 0 END), 0),
                       COALESCE(SUM(CASE WHEN nature = 'credit_anterieur' THEN montant ELSE 0 END), 0)
                INTO v_base, v_collectee, v_deductible, v_credit
                FROM ligne_declarations_fiscales WHERE declaration_fiscale_id = p_declaration_id;
                v_net := v_collectee - v_deductible - v_credit;
                UPDATE declarations_fiscales SET base_imposable = v_base, taxe_collectee = v_collectee,
                    taxe_deductible = v_deductible, credit_anterieur = v_credit,
                    montant_du = GREATEST(v_net, 0), credit_reportable = GREATEST(-v_net, 0)
                WHERE id = p_declaration_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION propager_ligne_declaration_fiscale() RETURNS trigger AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN PERFORM actualiser_declaration_fiscale(OLD.declaration_fiscale_id); END IF;
                IF TG_OP IN ('INSERT', 'UPDATE') THEN PERFORM actualiser_declaration_fiscale(NEW.declaration_fiscale_id); END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ligne_declarations_actualiser_parent
            AFTER INSERT OR UPDATE OR DELETE ON ligne_declarations_fiscales
            FOR EACH ROW EXECUTE FUNCTION propager_ligne_declaration_fiscale();
            SQL);

        // Ajout PostgreSQL : le budget parent expose en permanence la somme de ses prévisions débit et crédit.
        DB::unprepared(<<<'SQL'
            CREATE FUNCTION actualiser_budget(p_budget_id uuid) RETURNS void AS $$
            BEGIN
                IF p_budget_id IS NULL THEN RETURN; END IF;
                PERFORM 1 FROM budgets WHERE id = p_budget_id FOR UPDATE;
                UPDATE budgets SET total_debit = x.debit, total_credit = x.credit
                FROM (SELECT COALESCE(SUM(montant_debit), 0) debit, COALESCE(SUM(montant_credit), 0) credit
                      FROM ligne_budgets WHERE budget_id = p_budget_id) x
                WHERE budgets.id = p_budget_id;
            END;
            $$ LANGUAGE plpgsql;

            CREATE FUNCTION propager_ligne_budget() RETURNS trigger AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') THEN PERFORM actualiser_budget(OLD.budget_id); END IF;
                IF TG_OP IN ('INSERT', 'UPDATE') THEN PERFORM actualiser_budget(NEW.budget_id); END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER ligne_budgets_actualiser_parent
            AFTER INSERT OR UPDATE OR DELETE ON ligne_budgets
            FOR EACH ROW EXECUTE FUNCTION propager_ligne_budget();
            SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            // Suppression PostgreSQL : retrait de la vue avant sa table source pour respecter les dépendances.
            DB::statement('DROP VIEW IF EXISTS vue_soldes_comptables_agreges');

            // Suppression PostgreSQL : retrait des déclencheurs puis de leurs fonctions, dans l'ordre inverse des ajouts.
            DB::unprepared(<<<'SQL'
                DROP TRIGGER IF EXISTS ligne_budgets_actualiser_parent ON ligne_budgets;
                DROP FUNCTION IF EXISTS propager_ligne_budget(); DROP FUNCTION IF EXISTS actualiser_budget(uuid);
                DROP TRIGGER IF EXISTS ligne_declarations_actualiser_parent ON ligne_declarations_fiscales;
                DROP FUNCTION IF EXISTS propager_ligne_declaration_fiscale(); DROP FUNCTION IF EXISTS actualiser_declaration_fiscale(uuid);
                DROP TRIGGER IF EXISTS amortissements_actualiser_immobilisation ON amortissements;
                DROP FUNCTION IF EXISTS propager_amortissement(); DROP FUNCTION IF EXISTS actualiser_valeur_immobilisation(uuid);
                DROP TRIGGER IF EXISTS immobilisations_initialiser_valeur ON immobilisations;
                DROP FUNCTION IF EXISTS initialiser_valeur_immobilisation();
                DROP TRIGGER IF EXISTS rapprochements_bancaires_actualiser_parents ON rapprochements_bancaires;
                DROP FUNCTION IF EXISTS propager_rapprochement_bancaire(); DROP FUNCTION IF EXISTS actualiser_ligne_rapprochee(uuid); DROP FUNCTION IF EXISTS actualiser_operation_rapprochee(uuid);
                DROP TRIGGER IF EXISTS rapprochements_bancaires_valider ON rapprochements_bancaires;
                DROP FUNCTION IF EXISTS valider_rapprochement_bancaire();
                DROP TRIGGER IF EXISTS operations_bancaires_initialiser_solde ON operations_bancaires;
                DROP FUNCTION IF EXISTS initialiser_solde_operation_bancaire();
                DROP TRIGGER IF EXISTS ligne_lettrages_actualiser_parent ON ligne_lettrages;
                DROP FUNCTION IF EXISTS propager_ligne_lettrage(); DROP FUNCTION IF EXISTS actualiser_lettrage(uuid);
                DROP TRIGGER IF EXISTS ligne_lettrages_valider ON ligne_lettrages;
                DROP FUNCTION IF EXISTS valider_ligne_lettrage();
                DROP TRIGGER IF EXISTS lettrages_initialiser_totaux ON lettrages;
                DROP FUNCTION IF EXISTS initialiser_lettrage();
                DROP TRIGGER IF EXISTS affectations_paiements_actualiser_parents ON affectations_paiements;
                DROP FUNCTION IF EXISTS propager_affectation_paiement(); DROP FUNCTION IF EXISTS actualiser_reglements_echeance(uuid); DROP FUNCTION IF EXISTS actualiser_reglements_piece(uuid); DROP FUNCTION IF EXISTS actualiser_affectations_paiement(uuid);
                DROP TRIGGER IF EXISTS affectations_paiements_valider ON affectations_paiements;
                DROP FUNCTION IF EXISTS valider_affectation_paiement();
                DROP TRIGGER IF EXISTS paiements_initialiser_solde ON paiements;
                DROP FUNCTION IF EXISTS initialiser_solde_paiement();
                DROP TRIGGER IF EXISTS ligne_pieces_actualiser_parent ON ligne_pieces_comptables;
                DROP FUNCTION IF EXISTS propager_totaux_ligne_piece(); DROP FUNCTION IF EXISTS actualiser_totaux_piece(uuid);
                DROP TRIGGER IF EXISTS ecritures_projeter_soldes ON ecritures;
                DROP FUNCTION IF EXISTS comptabiliser_dans_projection(); DROP FUNCTION IF EXISTS projeter_solde_compte(uuid);
                DROP TRIGGER IF EXISTS ecritures_valider_avant_comptabilisation ON ecritures;
                DROP FUNCTION IF EXISTS valider_comptabilisation_ecriture();
                DROP TRIGGER IF EXISTS ligne_ecritures_valider_coherence ON ligne_ecritures;
                DROP FUNCTION IF EXISTS valider_ligne_ecriture();
                DROP TRIGGER IF EXISTS ligne_ecritures_actualiser_parent ON ligne_ecritures;
                DROP FUNCTION IF EXISTS propager_totaux_ligne_ecriture(); DROP FUNCTION IF EXISTS actualiser_totaux_ecriture(uuid);
                SQL);

            // Suppression PostgreSQL : retrait des contraintes ajoutées avant la suppression de leurs colonnes.
            DB::unprepared(<<<'SQL'
                ALTER TABLE ligne_budgets DROP CONSTRAINT IF EXISTS ligne_budgets_montants_non_negatifs;
                ALTER TABLE declarations_fiscales DROP CONSTRAINT IF EXISTS declarations_fiscales_montants_non_negatifs;
                ALTER TABLE ligne_declarations_fiscales DROP CONSTRAINT IF EXISTS ligne_declarations_montants_non_negatifs;
                ALTER TABLE ligne_declarations_fiscales DROP CONSTRAINT IF EXISTS ligne_declarations_nature_valide;
                ALTER TABLE amortissements DROP CONSTRAINT IF EXISTS amortissements_montants_non_negatifs;
                ALTER TABLE immobilisations DROP CONSTRAINT IF EXISTS immobilisations_valeurs_coherentes;
                ALTER TABLE ligne_ecritures DROP CONSTRAINT IF EXISTS ligne_ecritures_rapprochement_non_negatif;
                ALTER TABLE operations_bancaires DROP CONSTRAINT IF EXISTS operations_rapprochement_non_negatif;
                ALTER TABLE rapprochements_bancaires DROP CONSTRAINT IF EXISTS rapprochements_montant_positif;
                ALTER TABLE lettrages DROP CONSTRAINT IF EXISTS lettrages_soldes_non_negatifs;
                ALTER TABLE ligne_lettrages DROP CONSTRAINT IF EXISTS ligne_lettrages_montant_positif;
                ALTER TABLE affectations_paiements DROP CONSTRAINT IF EXISTS affectations_paiements_montant_positif;
                ALTER TABLE paiements DROP CONSTRAINT IF EXISTS paiements_affectations_coherentes;
                ALTER TABLE echeances DROP CONSTRAINT IF EXISTS echeances_montants_coherents;
                ALTER TABLE ligne_pieces_comptables DROP CONSTRAINT IF EXISTS ligne_pieces_montants_non_negatifs;
                ALTER TABLE pieces_comptables DROP CONSTRAINT IF EXISTS pieces_comptables_soldes_non_negatifs;
                ALTER TABLE soldes_comptables DROP CONSTRAINT IF EXISTS soldes_comptables_non_negatifs;
                ALTER TABLE comptes DROP CONSTRAINT IF EXISTS comptes_nature_valide;
                SQL);
        }

        // Suppression : retrait des agrégats dérivés, car ils sont reconstruits par la migration lors d'une réinstallation.
        Schema::table('budgets', fn (Blueprint $table) => $table->dropColumn(['total_debit', 'total_credit']));
        Schema::table('ligne_declarations_fiscales', fn (Blueprint $table) => $table->dropColumn('nature'));
        Schema::table('declarations_fiscales', fn (Blueprint $table) => $table->dropColumn('credit_reportable'));
        Schema::table('immobilisations', fn (Blueprint $table) => $table->dropColumn('amortissement_cumule'));
        Schema::table('ligne_ecritures', fn (Blueprint $table) => $table->dropColumn('montant_rapproche'));
        Schema::table('operations_bancaires', fn (Blueprint $table) => $table->dropColumn(['montant_rapproche', 'solde_a_rapprocher']));
        Schema::table('paiements', fn (Blueprint $table) => $table->dropColumn(['montant_affecte', 'solde_non_affecte']));

        // Suppression : retrait de la projection avant l'indicateur de politique de compte dont elle dépendait.
        Schema::dropIfExists('soldes_comptables');
        Schema::table('comptes', fn (Blueprint $table) => $table->dropColumn('autorise_solde_inverse'));
    }
};
