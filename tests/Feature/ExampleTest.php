<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_schema_explorer_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('Cartographie relationnelle')
            ->assertSee('Relations expliquées')
            ->assertSee('Les petits robots de cette boîte')
            ->assertSee('Le mini-dictionnaire')
            ->assertSee('dossiers_comptables');
    }

    public function test_schema_catalog_is_available_as_json(): void
    {
        $response = $this->getJson('/api/schema');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'categories',
                'tables' => [['name', 'label', 'description', 'simple_description', 'category', 'columns']],
                'relations',
                'triggers' => [['name', 'source', 'target', 'event', 'title', 'simple', 'why', 'installed']],
                'stories',
                'glossary',
                'stats' => ['tables', 'columns', 'relations', 'categories', 'triggers'],
            ])
            ->assertJsonPath('stats.tables', 40)
            ->assertJsonPath('stats.triggers', 18)
            ->assertJsonPath('triggers.6.title', 'Bloquer le trop-payé');
    }
}
