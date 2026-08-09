<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AccountingSchemaCatalog
{
    /**
     * Construit un catalogue enrichi à partir du schéma réellement migré.
     *
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $definitions = config('accounting_schema.tables', []);
        $categories = config('accounting_schema.categories', []);
        $tables = [];
        $relations = [];

        foreach ($definitions as $name => $definition) {
            $columns = [];
            $foreignKeys = [];
            $indexes = [];

            try {
                if (Schema::hasTable($name)) {
                    $columns = Schema::getColumns($name);
                    $foreignKeys = Schema::getForeignKeys($name);
                    $indexes = Schema::getIndexes($name);
                }
            } catch (Throwable) {
                // Le catalogue descriptif reste consultable si la base est momentanément indisponible.
            }

            $primaryColumns = collect($indexes)
                ->where('primary', true)
                ->flatMap(fn (array $index) => $index['columns'])
                ->all();
            $uniqueColumns = collect($indexes)
                ->where('unique', true)
                ->flatMap(fn (array $index) => count($index['columns']) === 1 ? $index['columns'] : [])
                ->all();

            $tables[$name] = [
                'name' => $name,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'category' => $definition['category'],
                'category_label' => $categories[$definition['category']]['label'],
                'color' => $categories[$definition['category']]['color'],
                'exists' => $columns !== [],
                'columns' => collect($columns)->map(fn (array $column) => [
                    'name' => $column['name'],
                    'type' => $column['type_name'] ?? $column['type'],
                    'nullable' => (bool) $column['nullable'],
                    'default' => $this->normalizeDefault($column['default']),
                    'comment' => $column['comment'],
                    'primary' => in_array($column['name'], $primaryColumns, true),
                    'unique' => in_array($column['name'], $uniqueColumns, true),
                ])->values()->all(),
            ];

            foreach ($foreignKeys as $foreignKey) {
                $target = $foreignKey['foreign_table'];

                if (! isset($definitions[$target])) {
                    continue;
                }

                foreach ($foreignKey['columns'] as $offset => $column) {
                    $targetColumn = $foreignKey['foreign_columns'][$offset] ?? 'id';
                    $relations[] = [
                        'id' => $name.'.'.$column.'->'.$target.'.'.$targetColumn,
                        'source' => $name,
                        'source_column' => $column,
                        'target' => $target,
                        'target_column' => $targetColumn,
                        'on_delete' => strtolower((string) ($foreignKey['on_delete'] ?? 'no action')),
                        'on_update' => strtolower((string) ($foreignKey['on_update'] ?? 'no action')),
                        'description' => $this->describeRelation($name, $column, $target, $targetColumn, $foreignKey['on_delete'] ?? null, $definitions),
                    ];
                }
            }
        }

        return [
            'categories' => collect($categories)->map(fn (array $category, string $key) => ['key' => $key, ...$category])->values()->all(),
            'tables' => array_values($tables),
            'relations' => $relations,
            'stats' => [
                'tables' => count($tables),
                'columns' => collect($tables)->sum(fn (array $table) => count($table['columns'])),
                'relations' => count($relations),
                'categories' => count($categories),
            ],
        ];
    }

    private function normalizeDefault(mixed $default): string|int|float|bool|null
    {
        return is_scalar($default) ? $default : null;
    }

    /** @param array<string, array<string, string>> $definitions */
    private function describeRelation(string $source, string $column, string $target, string $targetColumn, ?string $onDelete, array $definitions): string
    {
        $sourceLabel = Arr::get($definitions, "$source.label", $source);
        $targetLabel = Arr::get($definitions, "$target.label", $target);
        $behavior = match (strtolower((string) $onDelete)) {
            'cascade' => "La suppression d’un enregistrement « {$targetLabel} » supprime aussi les « {$sourceLabel} » associés.",
            'set null' => "Si l’enregistrement « {$targetLabel} » disparaît, la référence est conservée vide afin de préserver « {$sourceLabel} ».",
            'restrict', 'no action' => "La suppression de « {$targetLabel} » est bloquée tant que « {$sourceLabel} » le référence.",
            default => "Le comportement de suppression est « {$onDelete} ».",
        };

        return "{$sourceLabel}.{$column} référence {$targetLabel}.{$targetColumn}. {$behavior}";
    }
}
