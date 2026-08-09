import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('schemaExplorer', (schema) => ({
    schema,
    search: '',
    category: 'all',
    mode: 'discovery',
    selectedName: 'ecritures',
    showAllRelations: false,
    positions: {},
    graphWidth: 1900,
    graphHeight: 720,

    init() {
        if (!this.tableByName(this.selectedName)) {
            this.selectedName = this.schema.tables[0]?.name ?? null;
        }

        this.buildLayout();
    },

    buildLayout() {
        const positions = {};
        const columnWidth = 252;
        const rowHeight = 82;
        const top = 72;

        this.schema.categories.forEach((category, categoryIndex) => {
            const tables = this.schema.tables.filter((table) => table.category === category.key);

            tables.forEach((table, tableIndex) => {
                positions[table.name] = {
                    x: 28 + categoryIndex * columnWidth,
                    y: top + tableIndex * rowHeight,
                };
            });
        });

        const largestCategory = Math.max(...this.schema.categories.map((category) =>
            this.schema.tables.filter((table) => table.category === category.key).length
        ));

        this.positions = positions;
        this.graphWidth = Math.max(1120, this.schema.categories.length * columnWidth + 20);
        this.graphHeight = Math.max(620, top + largestCategory * rowHeight + 32);
    },

    tableByName(name) {
        return this.schema.tables.find((table) => table.name === name);
    },

    get selectedTable() {
        return this.tableByName(this.selectedName);
    },

    get filteredTables() {
        const query = this.search.trim().toLowerCase();

        return this.schema.tables.filter((table) => {
            const categoryMatches = this.category === 'all' || table.category === this.category;
            const searchMatches = !query || [table.name, table.label, table.description, table.simple_description]
                .some((value) => value.toLowerCase().includes(query));

            return categoryMatches && searchMatches;
        });
    },

    get selectedRelations() {
        return this.schema.relations.filter((relation) =>
            relation.source === this.selectedName || relation.target === this.selectedName
        );
    },

    get incomingRelations() {
        return this.schema.relations.filter((relation) => relation.target === this.selectedName);
    },

    get outgoingRelations() {
        return this.schema.relations.filter((relation) => relation.source === this.selectedName);
    },

    get selectedTriggers() {
        return this.schema.triggers.filter((trigger) =>
            trigger.source === this.selectedName || trigger.target === this.selectedName
        );
    },

    get discoveryMode() {
        return this.mode === 'discovery';
    },

    tableLabel(name) {
        return this.tableByName(name)?.label ?? name;
    },

    selectTriggerTable(trigger) {
        this.selectedName = trigger.source;
    },

    isNodeVisible(name) {
        if (this.showAllRelations) return true;
        if (name === this.selectedName) return true;

        return this.selectedRelations.some((relation) => relation.source === name || relation.target === name);
    },

    isRelationVisible(relation) {
        return this.showAllRelations || relation.source === this.selectedName || relation.target === this.selectedName;
    },

    selectTable(name) {
        this.selectedName = name;
    },

    relationPath(relation) {
        const source = this.positions[relation.source];
        const target = this.positions[relation.target];
        if (!source || !target) return '';

        const nodeWidth = 212;
        const nodeHeight = 58;

        if (relation.source === relation.target) {
            const x = source.x + nodeWidth;
            const y = source.y + nodeHeight / 2;
            return `M ${x} ${y} C ${x + 54} ${y - 48}, ${x + 54} ${y + 48}, ${x} ${y + 10}`;
        }

        const leftToRight = target.x >= source.x;
        const startX = source.x + (leftToRight ? nodeWidth : 0);
        const endX = target.x + (leftToRight ? 0 : nodeWidth);
        const startY = source.y + nodeHeight / 2;
        const endY = target.y + nodeHeight / 2;
        const curve = Math.max(46, Math.abs(endX - startX) * 0.42);
        const firstControl = startX + (leftToRight ? curve : -curve);
        const secondControl = endX - (leftToRight ? curve : -curve);

        return `M ${startX} ${startY} C ${firstControl} ${startY}, ${secondControl} ${endY}, ${endX} ${endY}`;
    },

    deleteLabel(action) {
        return ({
            cascade: 'Cascade',
            'set null': 'Mise à null',
            restrict: 'Restriction',
            'no action': 'Restriction',
        })[action] ?? action;
    },

    columnDefault(column) {
        if (column.default === null || column.default === undefined) return null;
        return String(column.default).replaceAll("'", '');
    },
}));

Alpine.start();
