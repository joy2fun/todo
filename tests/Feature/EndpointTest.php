<?php

namespace Tests\Feature;

use App\Filament\Resources\EndpointResource\Pages\ListEndpoints;
use App\Models\Endpoint;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_serves_the_configured_body_status_and_content_type(): void
    {
        Endpoint::factory()->json('api/health', ['status' => 'ok'])->create();

        $this->getJson('/api/health')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson(['status' => 'ok']);
    }

    public function test_it_serves_custom_headers(): void
    {
        Endpoint::factory()->create([
            'path' => 'api/headers',
            'headers' => ['X-Source' => 'panel', 'X-Version' => '1'],
        ]);

        $this->get('/api/headers')
            ->assertOk()
            ->assertHeader('X-Source', 'panel')
            ->assertHeader('X-Version', '1');
    }

    public function test_it_serves_non_json_content_types(): void
    {
        Endpoint::factory()->create([
            'path' => 'api/notes',
            'content_type' => 'text/plain',
            'body' => 'plain text body',
        ]);

        $this->get('/api/notes')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
            ->assertSee('plain text body');
    }

    public function test_it_serves_the_configured_status_code(): void
    {
        Endpoint::factory()->create([
            'path' => 'api/teapot',
            'status_code' => 418,
            'body' => '{"error":"teapot"}',
        ]);

        $this->get('/api/teapot')
            ->assertStatus(418)
            ->assertExactJson(['error' => 'teapot']);
    }

    public function test_it_normalizes_stored_paths(): void
    {
        $endpoint = Endpoint::factory()->create(['path' => 'api//nested/']);

        $this->assertSame('/api/nested', $endpoint->fresh()->path);

        $this->get('/api/nested/')->assertOk();
    }

    public function test_it_serves_post_requests_without_csrf_token(): void
    {
        Endpoint::factory()->create([
            'method' => 'POST',
            'path' => 'api/orders',
            'body' => '{"ok":true}',
        ]);

        $this->postJson('/api/orders', ['sku' => 'ABC'])
            ->assertOk()
            ->assertExactJson(['ok' => true]);
    }

    public function test_it_returns_404_for_unregistered_paths(): void
    {
        $this->get('/api/unknown')->assertNotFound();
    }

    public function test_it_returns_404_for_inactive_endpoints(): void
    {
        Endpoint::factory()->inactive()->json('api/disabled', ['status' => 'ok'])->create();

        $this->get('/api/disabled')->assertNotFound();
    }

    public function test_it_returns_405_when_the_path_is_registered_for_another_method(): void
    {
        Endpoint::factory()->create([
            'method' => 'GET',
            'path' => 'api/items',
        ]);

        $this->postJson('/api/items')
            ->assertStatus(405)
            ->assertHeader('Allow', 'GET');
    }

    public function test_inactive_endpoints_do_not_constrain_the_allowed_methods(): void
    {
        Endpoint::factory()->inactive()->create([
            'method' => 'GET',
            'path' => 'api/items',
        ]);

        $this->postJson('/api/items')->assertNotFound();
    }

    public function test_it_allows_the_same_path_for_different_methods(): void
    {
        Endpoint::factory()->create([
            'method' => 'GET',
            'path' => 'api/items',
            'body' => '{"read":true}',
        ]);

        Endpoint::factory()->create([
            'method' => 'POST',
            'path' => 'api/items',
            'body' => '{"created":true}',
        ]);

        $this->get('/api/items')->assertExactJson(['read' => true]);
        $this->postJson('/api/items')->assertExactJson(['created' => true]);
    }

    public function test_existing_routes_take_precedence_over_endpoints(): void
    {
        Endpoint::factory()->create([
            'path' => 'up',
            'body' => '{"served":true}',
        ]);

        $this->get('/up')->assertOk()->assertDontSee('{"served":true}');
    }

    public function test_endpoint_can_be_created_from_the_panel(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ListEndpoints::class)
            ->callAction('create', [
                'method' => 'POST',
                'path' => 'api/orders',
                'status_code' => 201,
                'content_type' => 'application/json',
                'body' => '{"id":1}',
                'headers' => ['X-Source' => 'panel'],
                'note' => 'Created for checkout tests',
                'is_active' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('endpoints', [
            'method' => 'POST',
            'path' => '/api/orders',
            'status_code' => 201,
        ]);

        $this->postJson('/api/orders')->assertCreated()->assertExactJson(['id' => 1]);
    }

    public function test_endpoint_can_be_edited_from_the_panel(): void
    {
        $this->actingAs(User::factory()->create());

        $endpoint = Endpoint::factory()->json('api/health', ['status' => 'ok'])->create();

        Livewire::test(ListEndpoints::class)
            ->callAction(TestAction::make('edit')->table($endpoint), [
                'status_code' => 503,
                'body' => '{"status":"down"}',
                'note' => 'Simulated outage',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('endpoints', [
            'id' => $endpoint->id,
            'status_code' => 503,
            'note' => 'Simulated outage',
        ]);

        $this->get('/api/health')
            ->assertStatus(503)
            ->assertExactJson(['status' => 'down']);
    }

    public function test_endpoint_can_be_deleted_from_the_panel(): void
    {
        $this->actingAs(User::factory()->create());

        $endpoint = Endpoint::factory()->create();

        Livewire::test(ListEndpoints::class)
            ->callAction(TestAction::make('delete')->table($endpoint));

        $this->assertDatabaseCount('endpoints', 0);
    }

    public function test_endpoint_path_must_be_unique_per_method(): void
    {
        $this->actingAs(User::factory()->create());

        Endpoint::factory()->create(['method' => 'GET', 'path' => 'api/items']);

        Livewire::test(ListEndpoints::class)
            ->callAction('create', [
                'method' => 'GET',
                'path' => 'api/items',
                'status_code' => 200,
                'content_type' => 'application/json',
            ])
            ->assertHasFormErrors(['path' => 'An endpoint already exists for GET api/items.']);

        $this->assertDatabaseCount('endpoints', 1);
    }

    public function test_endpoint_path_may_repeat_for_a_different_method(): void
    {
        $this->actingAs(User::factory()->create());

        Endpoint::factory()->create(['method' => 'GET', 'path' => 'api/items']);

        Livewire::test(ListEndpoints::class)
            ->callAction('create', [
                'method' => 'POST',
                'path' => 'api/items',
                'status_code' => 200,
                'content_type' => 'application/json',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('endpoints', 2);
    }

    public function test_json_body_is_rejected_when_content_type_is_json(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ListEndpoints::class)
            ->callAction('create', [
                'method' => 'GET',
                'path' => 'api/broken',
                'status_code' => 200,
                'content_type' => 'application/json',
                'body' => '{not json',
            ])
            ->assertHasFormErrors(['body']);

        $this->assertDatabaseMissing('endpoints', ['path' => '/api/broken']);
    }

    public function test_non_json_body_is_allowed_for_other_content_types(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ListEndpoints::class)
            ->callAction('create', [
                'method' => 'GET',
                'path' => 'api/markup',
                'status_code' => 200,
                'content_type' => 'text/html',
                'body' => '{not json',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('endpoints', ['path' => '/api/markup']);
    }
}
