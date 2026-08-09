# Périmètre et décisions du schéma comptable

Ce microservice ne crée ni ne gère les entreprises. Il reçoit `entreprise_id` du service propriétaire et conserve uniquement un `dossier_comptable` associé. Les identifiants d'acteurs (`*_par_id`) sont également externes et ne portent pas de clé étrangère vers une gestion locale des utilisateurs.

## Suppressions et raisons

- `entreprises` : supprimée, car l'identité légale et le cycle de vie d'une entreprise appartiennent au service entreprise.
- `entreprise_user` : supprimée, car les membres, rôles et permissions appartiennent au service d'identité ou d'autorisation.
- `pays_applique` : remplacé par `pays_code`, format ISO plus stable et interopérable.
- `symbol` : remplacé par `symbole`, afin d'harmoniser le vocabulaire français du domaine.
- `est_cloturer` : remplacé par `statut`, qui distingue brouillon, ouvert, verrouillé et clôturé.
- `date_cloture` : remplacée par `cloture_le`, horodatage nécessaire à l'audit.
- `date` et `date_ecriture` de `ecritures` : remplacées par l'unique `date_comptable`; le doublon rendait les contrôles de période ambigus.
- `montant_total` de `ecritures` : remplacé par quatre totaux débit/crédit, en devise de transaction et fonctionnelle, pour contrôler l'équilibre.
- `cree_le` de `ecritures` : supprimé, car `created_at` horodaté remplit déjà ce rôle.
- suppressions en cascade des journaux, comptes, taxes et tiers utilisés : remplacées par des restrictions ou mises à null lorsqu'une suppression aurait détruit l'historique comptable.

## Ajouts et raisons

- `dossiers_comptables` : frontière d'agrégat du microservice, reliée à l'identifiant externe d'entreprise.
- `taux_changes` et montants fonctionnels : comptabilité multidevise et écarts de change.
- `periode_comptables` et `verrouillages_comptables` : clôtures mensuelles, annuelles et contrôles de saisie.
- enrichissement de `comptes`, `journals`, `taxes`, `ecritures` et `ligne_ecritures` : plan hiérarchique, séquences, comptes automatiques, cycle de validation et double entrée.
- `tiers` et `coordonnees_bancaires_tiers` : soldes auxiliaires et règlements, sans gérer la fiche métier complète.
- `axes_analytiques` et `comptes_analytiques` : centres de coûts, projets et dimensions de gestion.
- `pieces_comptables`, lignes et échéances : factures, avoirs, taxes, balance âgée et justification des écritures.
- `comptes_tresorerie`, `paiements` et affectations : encaissements, décaissements et règlements partiels.
- `releves_bancaires`, opérations et rapprochements : import bancaire et rapprochement plusieurs-à-plusieurs.
- `lettrages` et lignes : apurement partiel ou total des comptes de tiers.
- `budgets` et lignes : versions budgétaires et analyse réalisé/prévisionnel.
- catégories, `immobilisations` et `amortissements` : registre patrimonial et plans d'amortissement.
- modèles récurrents et déclarations fiscales : automatisation périodique et suivi déclaratif.
- documents, audit chaîné, idempotence et boîte de sortie : preuve, intégration fiable et traçabilité propres à un microservice financier.
- contraintes PostgreSQL et déclencheurs d'immutabilité : rejet des lignes débit/crédit invalides, des écritures postées déséquilibrées et des modifications d'une écriture comptabilisée ou du journal d'audit.

Les règles transactionnelles qui comparent plusieurs lignes—équilibre débit/crédit, interdiction de modifier une écriture comptabilisée, unicité séquentielle sous concurrence et cohérence dossier/compte—doivent aussi être imposées par la couche domaine dans une transaction. Le schéma fournit les clés, index et états nécessaires, mais ne remplace pas ces invariants applicatifs.
