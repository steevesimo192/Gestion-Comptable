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
            ->assertSee('dossiers_comptables');
    }

    public function test_schema_catalog_is_available_as_json(): void
    {
        $response = $this->getJson('/api/schema');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'categories',
                'tables' => [['name', 'label', 'description', 'category', 'columns']],
                'relations',
                'stats' => ['tables', 'columns', 'relations', 'categories'],
            ])
            ->assertJsonPath('stats.tables', 39);
    }
}
