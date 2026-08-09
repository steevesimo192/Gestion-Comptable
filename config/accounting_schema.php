<?php

return [
    'categories' => [
        'fondations' => ['label' => 'Fondations', 'color' => '#1d4ed8'],
        'grand_livre' => ['label' => 'Grand livre', 'color' => '#7c3aed'],
        'tiers_ventes' => ['label' => 'Tiers & pièces', 'color' => '#c2410c'],
        'tresorerie' => ['label' => 'Trésorerie', 'color' => '#047857'],
        'pilotage' => ['label' => 'Pilotage', 'color' => '#b45309'],
        'fiscalite_actifs' => ['label' => 'Fiscalité & actifs', 'color' => '#be123c'],
        'gouvernance' => ['label' => 'Gouvernance', 'color' => '#475569'],
    ],

    'tables' => [
        'devises' => ['category' => 'fondations', 'label' => 'Devises', 'description' => 'Référentiel ISO des monnaies utilisées par les dossiers, pièces, paiements et écritures multidevises.'],
        'taux_changes' => ['category' => 'fondations', 'label' => 'Taux de change', 'description' => 'Historique daté des taux entre deux devises, avec la source ayant fourni chaque valeur.'],
        'referentiel_comptables' => ['category' => 'fondations', 'label' => 'Référentiels comptables', 'description' => 'Normes et plans réglementaires versionnés, par exemple SYSCOHADA ou IFRS.'],
        'dossiers_comptables' => ['category' => 'fondations', 'label' => 'Dossiers comptables', 'description' => 'Racine comptable du microservice. Chaque dossier correspond à une entreprise gérée par un service externe.'],
        'exercice_comptables' => ['category' => 'fondations', 'label' => 'Exercices comptables', 'description' => 'Cadres annuels de saisie, validation et clôture des opérations comptables.'],
        'periode_comptables' => ['category' => 'fondations', 'label' => 'Périodes comptables', 'description' => 'Découpage mensuel ou d’ajustement d’un exercice, avec état d’ouverture ou de verrouillage.'],

        'comptes' => ['category' => 'grand_livre', 'label' => 'Plan comptable', 'description' => 'Comptes généraux hiérarchiques qui portent toutes les imputations du grand livre.'],
        'journals' => ['category' => 'grand_livre', 'label' => 'Journaux', 'description' => 'Regroupent les écritures par nature et pilotent leurs séquences et comptes automatiques.'],
        'ecritures' => ['category' => 'grand_livre', 'label' => 'Écritures', 'description' => 'En-têtes des opérations en partie double, de leur brouillon jusqu’à la comptabilisation ou l’extourne.'],
        'ligne_ecritures' => ['category' => 'grand_livre', 'label' => 'Lignes d’écriture', 'description' => 'Mouvements débit ou crédit du grand livre, enrichis des tiers, taxes, devises et dimensions analytiques.'],
        'lettrages' => ['category' => 'grand_livre', 'label' => 'Lettrages', 'description' => 'Groupes d’apurement total ou partiel des mouvements d’un compte auxiliaire.'],
        'ligne_lettrages' => ['category' => 'grand_livre', 'label' => 'Lignes de lettrage', 'description' => 'Associent un montant précis de ligne d’écriture à un groupe de lettrage.'],

        'tiers' => ['category' => 'tiers_ventes', 'label' => 'Tiers', 'description' => 'Projection comptable minimale des clients, fournisseurs et autres contreparties externes.'],
        'coordonnees_bancaires_tiers' => ['category' => 'tiers_ventes', 'label' => 'Banques des tiers', 'description' => 'Coordonnées bancaires nécessaires au règlement des tiers sans dupliquer leur fiche métier.'],
        'pieces_comptables' => ['category' => 'tiers_ventes', 'label' => 'Pièces comptables', 'description' => 'Factures, avoirs, notes de débit et autres documents générateurs d’écritures.'],
        'ligne_pieces_comptables' => ['category' => 'tiers_ventes', 'label' => 'Lignes de pièce', 'description' => 'Détail quantitatif, fiscal, analytique et comptable de chaque pièce.'],
        'echeances' => ['category' => 'tiers_ventes', 'label' => 'Échéances', 'description' => 'Calendrier des montants dus permettant les règlements partiels et la balance âgée.'],

        'comptes_tresorerie' => ['category' => 'tresorerie', 'label' => 'Comptes de trésorerie', 'description' => 'Comptes bancaires et caisses reliés au plan comptable, à un journal et à une devise.'],
        'paiements' => ['category' => 'tresorerie', 'label' => 'Paiements', 'description' => 'Encaissements, décaissements, virements et remboursements comptabilisés.'],
        'affectations_paiements' => ['category' => 'tresorerie', 'label' => 'Affectations de paiement', 'description' => 'Ventilent un paiement sur une ou plusieurs pièces et échéances.'],
        'releves_bancaires' => ['category' => 'tresorerie', 'label' => 'Relevés bancaires', 'description' => 'Périmètres importés d’une banque avec soldes d’ouverture et de clôture.'],
        'operations_bancaires' => ['category' => 'tresorerie', 'label' => 'Opérations bancaires', 'description' => 'Lignes brutes issues des relevés avant leur rapprochement au grand livre.'],
        'rapprochements_bancaires' => ['category' => 'tresorerie', 'label' => 'Rapprochements', 'description' => 'Liens plusieurs-à-plusieurs entre opérations bancaires et mouvements comptables.'],

        'axes_analytiques' => ['category' => 'pilotage', 'label' => 'Axes analytiques', 'description' => 'Dimensions de pilotage telles que centre de coût, projet ou département.'],
        'comptes_analytiques' => ['category' => 'pilotage', 'label' => 'Valeurs analytiques', 'description' => 'Valeurs hiérarchiques utilisables dans chaque axe analytique.'],
        'budgets' => ['category' => 'pilotage', 'label' => 'Budgets', 'description' => 'Versions budgétaires d’un exercice servant à comparer prévisionnel et réalisé.'],
        'ligne_budgets' => ['category' => 'pilotage', 'label' => 'Lignes budgétaires', 'description' => 'Montants prévus par période, compte général et valeur analytique.'],

        'taxes' => ['category' => 'fiscalite_actifs', 'label' => 'Taxes', 'description' => 'Règles fiscales paramétrables, leurs taux, périodes de validité et comptes de liquidation.'],
        'declarations_fiscales' => ['category' => 'fiscalite_actifs', 'label' => 'Déclarations fiscales', 'description' => 'Synthèses périodiques de TVA, retenues et autres obligations déclaratives.'],
        'ligne_declarations_fiscales' => ['category' => 'fiscalite_actifs', 'label' => 'Lignes de déclaration', 'description' => 'Ventilation auditable d’une déclaration par taxe et case réglementaire.'],
        'categories_immobilisations' => ['category' => 'fiscalite_actifs', 'label' => 'Catégories d’actifs', 'description' => 'Règles, durées et comptes automatiques appliqués aux immobilisations.'],
        'immobilisations' => ['category' => 'fiscalite_actifs', 'label' => 'Immobilisations', 'description' => 'Registre patrimonial couvrant acquisition, mise en service, valeur nette et sortie.'],
        'amortissements' => ['category' => 'fiscalite_actifs', 'label' => 'Amortissements', 'description' => 'Échéances du plan d’amortissement et écritures de dotation correspondantes.'],
        'modeles_ecritures_recurrentes' => ['category' => 'fiscalite_actifs', 'label' => 'Écritures récurrentes', 'description' => 'Modèles planifiés pour loyers, abonnements, provisions et autres opérations périodiques.'],

        'verrouillages_comptables' => ['category' => 'gouvernance', 'label' => 'Verrouillages', 'description' => 'Interdictions de saisie par date et portée, indépendantes des clôtures de période.'],
        'documents_comptables' => ['category' => 'gouvernance', 'label' => 'Justificatifs', 'description' => 'Métadonnées et empreintes des pièces jointes probantes liées aux objets comptables.'],
        'journaux_audit' => ['category' => 'gouvernance', 'label' => 'Journal d’audit', 'description' => 'Trace append-only et chaînable des modifications comptables sensibles.'],
        'cles_idempotence' => ['category' => 'gouvernance', 'label' => 'Clés d’idempotence', 'description' => 'Dédupliquent les commandes API afin qu’une même demande ne soit traitée qu’une fois.'],
        'evenements_sortants' => ['category' => 'gouvernance', 'label' => 'Événements sortants', 'description' => 'Boîte de sortie transactionnelle pour publier les événements du microservice sans perte.'],
    ],
];
