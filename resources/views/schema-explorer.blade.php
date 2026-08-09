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
            <div class="learning-switch" role="group" aria-label="Niveau d’explication">
                <button :class="{ active: discoveryMode }" @click="mode = 'discovery'">🧭 Découverte</button>
                <button :class="{ active: !discoveryMode }" @click="mode = 'expert'">⚙️ Expert</button>
            </div>
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
                <p class="eyebrow" x-text="discoveryMode ? 'La comptabilité racontée simplement' : 'Carte vivante du modèle'"></p>
                <h2 x-text="discoveryMode ? 'Suis le voyage de l’argent.' : 'Le grand livre, de bout en bout.'"></h2>
                <p x-show="discoveryMode">Une boîte garde une sorte d’information. Une flèche montre à quelle autre boîte elle est reliée. Un déclencheur est un petit robot qui vérifie les calculs automatiquement.</p>
                <p x-show="!discoveryMode">La vue est construite depuis le schéma PostgreSQL réellement migré. Sélectionnez une table pour isoler ses dépendances, puis consultez les clés étrangères et les déclencheurs.</p>
            </div>
            <span class="live-badge">Schéma synchronisé</span>
        </header>

        <section class="stats-grid" aria-label="Statistiques du schéma">
            <article class="stat-card"><span>Tables métier</span><strong x-text="schema.stats.tables"></strong></article>
            <article class="stat-card"><span>Colonnes</span><strong x-text="schema.stats.columns"></strong></article>
            <article class="stat-card"><span>Relations</span><strong x-text="schema.stats.relations"></strong></article>
            <article class="stat-card trigger-stat"><span>Petits robots</span><strong x-text="schema.stats.triggers"></strong><small>déclencheurs</small></article>
            <article class="stat-card"><span>Domaines</span><strong x-text="schema.stats.categories"></strong></article>
        </section>

        <section class="stories" x-show="discoveryMode" aria-label="Exemples simples">
            <template x-for="story in schema.stories" :key="story.title">
                <article class="story-card">
                    <span class="story-icon" x-text="story.icon"></span>
                    <div><h3 x-text="story.title"></h3><ol><template x-for="step in story.steps" :key="step"><li x-text="step"></li></template></ol></div>
                </article>
            </template>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div class="panel-title">
                    <span class="panel-title-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="6" height="5" rx="1"/><rect x="15" y="15" width="6" height="5" rx="1"/><path d="M9 6.5h4a4 4 0 0 1 4 4V15"/></svg>
                    </span>
                    <span><h3 x-text="discoveryMode ? 'La carte des boîtes' : 'Cartographie relationnelle'"></h3><p x-text="discoveryMode ? 'Clique sur une boîte. Les flèches montrent ses voisines.' : 'La flèche pointe vers la table référencée.'"></p></span>
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
                            <span class="node-copy"><strong x-text="table.label"></strong><small x-show="!discoveryMode" x-text="table.name"></small></span>
                            <span class="node-count" x-show="!discoveryMode" x-text="table.columns.length"></span>
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
                    <code x-show="!discoveryMode" x-text="selectedTable?.name"></code>
                    <p class="simple-description" x-text="discoveryMode ? selectedTable?.simple_description : selectedTable?.description"></p>
                </div>
                <div class="kid-explanation" x-show="discoveryMode">
                    <span>💡</span><p><strong>À quoi sert cette boîte ?</strong><br><span x-text="selectedTable?.simple_description"></span></p>
                </div>
                <h4 class="section-label" x-show="!discoveryMode">Structure des colonnes</h4>
                <div style="overflow-x:auto" x-show="!discoveryMode">
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
                <div class="empty-state" x-show="!discoveryMode && !selectedTable?.columns.length">La table n’est pas disponible dans la connexion courante.</div>
            </section>

            <section class="panel">
                <div class="panel-header">
                    <div class="panel-title"><span><h3 x-text="discoveryMode ? 'Les flèches expliquées' : 'Relations expliquées'"></h3><p><span x-text="selectedRelations.length"></span> liens directs pour cette table</p></span></div>
                </div>
                <h4 class="section-label" x-show="outgoingRelations.length">Références sortantes</h4>
                <div class="relation-list">
                    <template x-for="relation in outgoingRelations" :key="relation.id">
                        <article class="relation-card">
                            <div class="relation-route" :class="{ simple: discoveryMode }"><span x-text="discoveryMode ? tableLabel(relation.source) : `${relation.source}.${relation.source_column}`"></span><span class="relation-arrow">→</span><span x-text="discoveryMode ? tableLabel(relation.target) : `${relation.target}.${relation.target_column}`"></span></div>
                            <p x-text="discoveryMode ? relation.simple_description : relation.description"></p>
                            <span class="delete-action" x-show="!discoveryMode">Suppression : <span x-text="deleteLabel(relation.on_delete)"></span></span>
                        </article>
                    </template>
                </div>
                <h4 class="section-label" x-show="incomingRelations.length">Références entrantes</h4>
                <div class="relation-list">
                    <template x-for="relation in incomingRelations" :key="relation.id">
                        <article class="relation-card">
                            <div class="relation-route" :class="{ simple: discoveryMode }"><span x-text="discoveryMode ? tableLabel(relation.source) : `${relation.source}.${relation.source_column}`"></span><span class="relation-arrow">→</span><span x-text="discoveryMode ? tableLabel(relation.target) : `${relation.target}.${relation.target_column}`"></span></div>
                            <p x-text="discoveryMode ? relation.simple_description : relation.description"></p>
                            <span class="delete-action" x-show="!discoveryMode">Suppression : <span x-text="deleteLabel(relation.on_delete)"></span></span>
                        </article>
                    </template>
                </div>
                <div class="empty-state" x-show="!selectedRelations.length">Cette table ne porte aucune clé étrangère vers le domaine comptable.</div>
            </section>
        </div>

        <section class="panel trigger-panel" aria-labelledby="trigger-title">
            <div class="panel-header">
                <div class="panel-title">
                    <span class="robot-icon" aria-hidden="true">🤖</span>
                    <span><h3 id="trigger-title" x-text="discoveryMode ? 'Les petits robots de cette boîte' : 'Déclencheurs PostgreSQL'"></h3><p><span x-text="selectedTriggers.length"></span> automatismes liés à <span x-text="selectedTable?.label"></span></p></span>
                </div>
                <span class="safety-pill">Le reste ne descend jamais sous 0</span>
            </div>
            <div class="trigger-grid">
                <template x-for="trigger in selectedTriggers" :key="trigger.name">
                    <article class="trigger-card" @click="selectTriggerTable(trigger)">
                        <div class="trigger-card-head"><span class="trigger-bolt">⚡</span><div><h4 x-text="trigger.title"></h4><code x-show="!discoveryMode" x-text="trigger.name"></code></div><span class="installed" :class="{ muted: !trigger.installed }" x-text="trigger.installed ? 'Installé' : 'Documenté'"></span></div>
                        <div class="trigger-flow"><span x-text="tableLabel(trigger.source)"></span><b>→</b><span x-text="tableLabel(trigger.target)"></span></div>
                        <dl>
                            <div><dt>Quand ?</dt><dd x-text="trigger.event"></dd></div>
                            <div><dt>Alors</dt><dd x-text="trigger.simple"></dd></div>
                            <div><dt>Pourquoi ?</dt><dd x-text="trigger.why"></dd></div>
                        </dl>
                    </article>
                </template>
                <div class="empty-state" x-show="!selectedTriggers.length">Cette boîte n’a pas de petit robot direct. Ses règles viennent de ses relations avec les autres boîtes.</div>
            </div>
        </section>

        <section class="panel glossary-panel" x-show="discoveryMode">
            <div class="panel-header"><div class="panel-title"><span class="robot-icon">📖</span><span><h3>Le mini-dictionnaire</h3><p>Les mots difficiles traduits en mots simples</p></span></div></div>
            <div class="glossary-grid"><template x-for="item in schema.glossary" :key="item.term"><article><h4 x-text="item.term"></h4><p x-text="item.simple"></p></article></template></div>
        </section>

        <p class="footer-note">Les structures sont lues avec les API d’inspection de Laravel et les explications métier sont versionnées dans <code>config/accounting_schema.php</code>. Endpoint JSON : <a href="{{ route('schema.data') }}">{{ route('schema.data') }}</a>.</p>
    </main>
</div>
</body>
</html>
