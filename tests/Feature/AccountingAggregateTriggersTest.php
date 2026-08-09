<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountingAggregateTriggersTest extends TestCase
{
    public function test_postgresql_recalculates_entry_and_projects_nature_aware_balances(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Les déclencheurs comptables sont spécifiques à PostgreSQL.');
        }

        DB::beginTransaction();

        try {
            $ids = collect([
                'devise', 'referentiel', 'dossier', 'exercice', 'periode', 'journal',
                'compte_debit', 'compte_credit', 'ecriture', 'ligne_debit', 'ligne_credit',
                'piece', 'ligne_piece', 'compte_tresorerie', 'paiement', 'affectation',
            ])->mapWithKeys(fn (string $key) => [$key => (string) Str::uuid()]);

            DB::table('devises')->insert([
                'id' => $ids['devise'], 'code' => 'TST', 'nom' => 'Devise de test', 'symbole' => 'T',
            ]);
            DB::table('referentiel_comptables')->insert([
                'id' => $ids['referentiel'], 'nom' => 'Référentiel de test', 'code' => 'TEST-'.Str::lower(Str::random(8)),
                'version' => '1', 'date_mise_en_vigueur' => '2026-01-01',
            ]);
            DB::table('dossiers_comptables')->insert([
                'id' => $ids['dossier'], 'entreprise_id' => (string) Str::uuid(),
                'referentiel_comptable_id' => $ids['referentiel'], 'devise_fonctionnelle_id' => $ids['devise'],
                'code' => 'DOS-'.Str::lower(Str::random(8)), 'libelle' => 'Dossier de test',
            ]);
            DB::table('exercice_comptables')->insert([
                'id' => $ids['exercice'], 'referentiel_comptable_id' => $ids['referentiel'],
                'devise_id' => $ids['devise'], 'dossier_comptable_id' => $ids['dossier'],
                'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31', 'titre' => 'Exercice 2026', 'annee' => 2026,
            ]);
            DB::table('periode_comptables')->insert([
                'id' => $ids['periode'], 'exercice_comptable_id' => $ids['exercice'], 'code' => '01',
                'libelle' => 'Janvier', 'date_debut' => '2026-01-01', 'date_fin' => '2026-01-31', 'statut' => 'ouverte',
            ]);
            DB::table('journals')->insert([
                'id' => $ids['journal'], 'dossier_comptable_id' => $ids['dossier'], 'devise_id' => $ids['devise'],
                'code' => 'OD', 'libelle' => 'Opérations diverses', 'type' => 'operations_diverses',
            ]);

            foreach ([['compte_debit', '512', 'debit'], ['compte_credit', '701', 'credit']] as [$key, $numero, $nature]) {
                DB::table('comptes')->insert([
                    'id' => $ids[$key], 'referentiel_comptable_id' => $ids['referentiel'],
                    'dossier_comptable_id' => $ids['dossier'], 'numero' => $numero,
                    'intitule' => 'Compte '.$numero, 'classe' => $numero[0], 'type' => 'general', 'nature' => $nature,
                ]);
            }

            DB::table('ecritures')->insert([
                'id' => $ids['ecriture'], 'dossier_comptable_id' => $ids['dossier'],
                'exercice_comptable_id' => $ids['exercice'], 'periode_comptable_id' => $ids['periode'],
                'journal_id' => $ids['journal'], 'date_comptable' => '2026-01-15',
                'libelle' => 'Écriture contrôlée', 'numero' => 'OD-TEST-'.Str::lower(Str::random(6)),
            ]);
            DB::table('ligne_ecritures')->insert([
                [
                    'id' => $ids['ligne_debit'], 'ecriture_id' => $ids['ecriture'], 'compte_id' => $ids['compte_debit'],
                    'debit' => 125, 'credit' => 0, 'debit_fonctionnel' => 125, 'credit_fonctionnel' => 0,
                    'ordre' => 1, 'libelle' => 'Débit contrôlé',
                ],
                [
                    'id' => $ids['ligne_credit'], 'ecriture_id' => $ids['ecriture'], 'compte_id' => $ids['compte_credit'],
                    'debit' => 0, 'credit' => 125, 'debit_fonctionnel' => 0, 'credit_fonctionnel' => 125,
                    'ordre' => 2, 'libelle' => 'Crédit contrôlé',
                ],
            ]);

            $ecriture = DB::table('ecritures')->find($ids['ecriture']);
            $this->assertSame('125.0000', $ecriture->total_debit);
            $this->assertSame('125.0000', $ecriture->total_credit);

            DB::table('ecritures')->where('id', $ids['ecriture'])->update(['statut' => 'comptabilisee']);

            $soldes = DB::table('soldes_comptables')->where('exercice_comptable_id', $ids['exercice']);
            $this->assertSame(2, $soldes->count());
            $this->assertSame('125.0000', DB::table('soldes_comptables')->where('compte_id', $ids['compte_debit'])->value('solde_normal'));
            $this->assertSame('0.0000', DB::table('soldes_comptables')->where('compte_id', $ids['compte_debit'])->value('solde_anormal'));
            $this->assertSame('125.0000', DB::table('soldes_comptables')->where('compte_id', $ids['compte_credit'])->value('solde_normal'));

            DB::table('pieces_comptables')->insert([
                'id' => $ids['piece'], 'dossier_comptable_id' => $ids['dossier'], 'journal_id' => $ids['journal'],
                'devise_id' => $ids['devise'], 'type' => 'facture_client', 'numero' => 'FAC-'.Str::lower(Str::random(6)),
                'date_piece' => '2026-01-15', 'date_comptable' => '2026-01-15',
            ]);
            DB::table('ligne_pieces_comptables')->insert([
                'id' => $ids['ligne_piece'], 'piece_comptable_id' => $ids['piece'], 'compte_id' => $ids['compte_credit'],
                'ordre' => 1, 'description' => 'Service contrôlé', 'prix_unitaire' => 100,
                'base_hors_taxe' => 100, 'montant_taxe' => 25, 'montant_ttc' => 125,
            ]);

            $piece = DB::table('pieces_comptables')->find($ids['piece']);
            $this->assertSame('125.0000', $piece->total_ttc);
            $this->assertSame('125.0000', $piece->solde_du);

            DB::table('comptes_tresorerie')->insert([
                'id' => $ids['compte_tresorerie'], 'dossier_comptable_id' => $ids['dossier'],
                'compte_id' => $ids['compte_debit'], 'journal_id' => $ids['journal'], 'devise_id' => $ids['devise'],
                'nom' => 'Banque de test',
            ]);
            DB::table('paiements')->insert([
                'id' => $ids['paiement'], 'dossier_comptable_id' => $ids['dossier'],
                'compte_tresorerie_id' => $ids['compte_tresorerie'], 'devise_id' => $ids['devise'],
                'numero' => 'PAY-'.Str::lower(Str::random(6)), 'sens' => 'entrant', 'mode' => 'virement',
                'date_paiement' => '2026-01-16', 'montant' => 100,
            ]);
            DB::table('affectations_paiements')->insert([
                'id' => $ids['affectation'], 'paiement_id' => $ids['paiement'], 'piece_comptable_id' => $ids['piece'],
                'montant' => 80, 'affecte_le' => now(),
            ]);

            $paiement = DB::table('paiements')->find($ids['paiement']);
            $piece = DB::table('pieces_comptables')->find($ids['piece']);
            $this->assertSame('80.0000', $paiement->montant_affecte);
            $this->assertSame('20.0000', $paiement->solde_non_affecte);
            $this->assertSame('80.0000', $piece->total_regle);
            $this->assertSame('45.0000', $piece->solde_du);

            DB::beginTransaction();
            try {
                DB::table('affectations_paiements')->insert([
                    'id' => (string) Str::uuid(), 'paiement_id' => $ids['paiement'], 'piece_comptable_id' => $ids['piece'],
                    'montant' => 21, 'affecte_le' => now(),
                ]);
                $this->fail('Une sur-affectation aurait dû être refusée.');
            } catch (\Illuminate\Database\QueryException $exception) {
                DB::rollBack();
                $this->assertStringContainsString('dépasse le solde disponible', $exception->getMessage());
            }
        } finally {
            DB::rollBack();
        }
    }
}
