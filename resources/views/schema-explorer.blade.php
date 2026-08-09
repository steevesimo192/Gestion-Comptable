<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Explorateur interactif du schéma relationnel du microservice de gestion comptable.">
    <title>Cartographie comptable · {{ config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell" x-data="schemaExplorer(@js($schema))" x-cloak>
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">GC</div>
            <p class="eyebrow">Architecture de données</p>
            <h1>Gestion comptable</h1>
            <p>Comprendre les tables, leurs attributs et chaque lien du modèle relationnel.</p>
        </div>

        <div class="sidebar-controls">
            <label class="search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input class="search-input" type="search" x-model="search" placeholder="Rechercher une table…" aria-label="Rechercher une table">
            </label>
            <select class="category-select" x-model="category" aria-label="Filtrer par domaine">
                <option value="all">Tous les domaines</option>
                <template x-for="item in schema.categories" :key="item.key">
                    <option :value="item.key" x-text="item.label"></option>
                </template>
            </select>
        </div>

        <nav class="table-navigation" aria-label="Tables comptables">
            <div class="table-count"><span x-text="filteredTables.length"></span> tables affichées</div>
            <template x-for="table in filteredTables" :key="table.name">
                <button class="table-nav-item" :class="{ active: selectedName === table.name }" :style="`--table-color: ${table.color}`" @click="selectTable(table.name)">
                    <span class="dot"></span>
                    <span><strong x-text="table.label"></strong></span>
                    <small x-text="table.columns.length"></small>
                </button>
            </template>
        </nav>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div>
                <p class="eyebrow">Carte vivante du modèle</p>
                <h2>Le grand livre, de bout en bout.</h2>
                <p>La vue est construite depuis le schéma PostgreSQL réellement migré. Sélectionnez une table pour isoler ses dépendances, puis consultez le rôle de chaque clé étrangère.</p>
            </div>
            <span class="live-badge">Schéma synchronisé</span>
        </header>

        <section class="stats-grid" aria-label="Statistiques du schéma">
            <article class="stat-card"><span>Tables métier</span><strong x-text="schema.stats.tables"></strong></article>
            <article class="stat-card"><span>Colonnes</span><strong x-text="schema.stats.columns"></strong></article>
            <article class="stat-card"><span>Relations</span><strong x-text="schema.stats.relations"></strong></article>
            <article class="stat-card"><span>Domaines</span><strong x-text="schema.stats.categories"></strong></article>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <span class="panel-title-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="6" height="5" rx="1"/><rect x="15" y="15" width="6" height="5" rx="1"/><path d="M9 6.5h4a4 4 0 0 1 4 4V15"/></svg>
                    </span>
                    <span><h3>Cartographie relationnelle</h3><p>La flèche pointe vers la table référencée.</p></span>
                </div>
                <div class="toggle" aria-label="Portée des relations">
                    <button :class="{ active: !showAllRelations }" @click="showAllRelations = false">Voisinage</button>
                    <button :class="{ active: showAllRelations }" @click="showAllRelations = true">Tout le schéma</button>
                </div>
            </div>

            <div class="graph-viewport">
                <div class="graph-canvas" :style="`width:${graphWidth}px;height:${graphHeight}px`">
                    <template x-for="(category, index) in schema.categories" :key="category.key">
                        <div class="category-heading" :style="`left:${28 + index * 252}px;top:24px;--category-color:${category.color}`" x-text="category.label"></div>
                    </template>

                    <svg class="graph-svg" :width="graphWidth" :height="graphHeight" aria-hidden="true">
                        <defs>
                            <marker id="arrow" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#94a3b8"/></marker>
                            <marker id="arrow-focus" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 Z" fill="#dc6b2f"/></marker>
                        </defs>
                        <template x-for="relation in schema.relations" :key="relation.id">
                            <path x-show="isRelationVisible(relation)" class="relation-line" :class="{ focused: relation.source === selectedName || relation.target === selectedName }" :d="relationPath(relation)" :marker-end="relation.source === selectedName || relation.target === selectedName ? 'url(#arrow-focus)' : 'url(#arrow)'"/>
                        </template>
                    </svg>

                    <template x-for="table in schema.tables" :key="table.name">
                        <button class="schema-node" :class="{ active: selectedName === table.name, 'hidden-node': !isNodeVisible(table.name) }" :style="`left:${positions[table.name].x}px;top:${positions[table.name].y}px;--node-color:${table.color}`" @click="selectTable(table.name)">
                            <span class="node-accent"></span>
                            <span class="node-copy"><strong x-text="table.label"></strong><small x-text="table.name"></small></span>
                            <span class="node-count" x-text="table.columns.length"></span>
                        </button>
                    </template>
                </div>
            </div>
        </section>

        <div class="detail-grid" x-show="selectedTable">
            <section class="panel" :style="`--selected-color:${selectedTable?.color}`">
                <div class="detail-hero">
                    <div class="detail-kicker" x-text="selectedTable?.category_label"></div>
                    <h3 x-text="selectedTable?.label"></h3>
                    <code x-text="selectedTable?.name"></code>
                    <p x-text="selectedTable?.description"></p>
                </div>
                <h4 class="section-label">Structure des colonnes</h4>
                <div style="overflow-x:auto">
                    <table class="columns-table">
                        <thead><tr><th>Colonne</th><th>Type</th><th>Contraintes</th><th>Défaut</th></tr></thead>
                        <tbody>
                        <template x-for="column in selectedTable?.columns ?? []" :key="column.name">
                            <tr>
                                <td x-text="column.name"></td>
                                <td x-text="column.type"></td>
                                <td><span class="column-flags">
                                    <span class="flag pk" x-show="column.primary">PK</span>
                                    <span class="flag fk" x-show="schema.relations.some(r => r.source === selectedName && r.source_column === column.name)">FK</span>
                                    <span class="flag" x-show="column.unique">Unique</span>
                                    <span class="flag" x-show="column.nullable">Nullable</span>
                                </span></td>
                                <td x-text="columnDefault(column) ?? '—'"></td>
                            </tr>
                        </template>
                        </tbody>
                    </table>
                </div>
                <div class="empty-state" x-show="!selectedTable?.columns.length">La table n’est pas disponible dans la connexion courante.</div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <div class="panel-title"><span><h3>Relations expliquées</h3><p><span x-text="selectedRelations.length"></span> liens directs pour cette table</p></span></div>
                </div>
                <h4 class="section-label" x-show="outgoingRelations.length">Références sortantes</h4>
                <div class="relation-list">
                    <template x-for="relation in outgoingRelations" :key="relation.id">
                        <article class="relation-card">
                            <div class="relation-route"><code x-text="`${relation.source}.${relation.source_column}`"></code><span class="relation-arrow">→</span><code x-text="`${relation.target}.${relation.target_column}`"></code></div>
                            <p x-text="relation.description"></p>
                            <span class="delete-action">Suppression : <span x-text="deleteLabel(relation.on_delete)"></span></span>
                        </article>
                    </template>
                </div>
                <h4 class="section-label" x-show="incomingRelations.length">Références entrantes</h4>
                <div class="relation-list">
                    <template x-for="relation in incomingRelations" :key="relation.id">
                        <article class="relation-card">
                            <div class="relation-route"><code x-text="`${relation.source}.${relation.source_column}`"></code><span class="relation-arrow">→</span><code x-text="`${relation.target}.${relation.target_column}`"></code></div>
                            <p x-text="relation.description"></p>
                            <span class="delete-action">Suppression : <span x-text="deleteLabel(relation.on_delete)"></span></span>
                        </article>
                    </template>
                </div>
                <div class="empty-state" x-show="!selectedRelations.length">Cette table ne porte aucune clé étrangère vers le domaine comptable.</div>
            </section>
        </div>

        <p class="footer-note">Les structures sont lues avec les API d’inspection de Laravel et les explications métier sont versionnées dans <code>config/accounting_schema.php</code>. Endpoint JSON : <a href="{{ route('schema.data') }}">{{ route('schema.data') }}</a>.</p>
    </main>
</div>
</body>
</html>
