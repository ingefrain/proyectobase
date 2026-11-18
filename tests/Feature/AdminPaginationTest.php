<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\InteractsWithBaseAdmin;
use Tests\TestCase;

class AdminPaginationTest extends TestCase
{
    use InteractsWithBaseAdmin;
    use RefreshDatabase;

    public function test_links_and_get_forms_preserve_filters_order_and_nested_query(): void
    {
        $this->actingAsAdmin();
        User::factory()->count(23)->create(['name' => 'Paginable', 'is_active' => true]);
        User::factory()->create(['name' => 'Paginable', 'is_active' => false]);
        $query = ['q' => 'Paginable', 'status' => 'active', 'per_page' => 10, 'order' => 'name', 'extra' => ['tag' => 'A&B']];
        $response = $this->get(route('admin.users.index', $query))->assertOk();
        $paginator = $response->viewData('users');
        $this->assertSame(23, $paginator->total());
        $this->assertCount(10, $paginator);
        parse_str(parse_url($paginator->url(2), PHP_URL_QUERY), $actual);
        $this->assertEquals([...$query, 'page' => 2], $actual);
        $response->assertSee('name="extra[tag]" value="A&amp;B"', false)
            ->assertSee('name="q" value="Paginable"', false)
            ->assertSee('name="page" value="1"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('type="button" disabled>Anterior', false)
            ->assertSee('Registros por página')->assertSee('Mostrando 1–10 de 23');
        $changed = $this->get(route('admin.users.index', [...$query, 'per_page' => 20]))->assertOk()->viewData('users');
        $this->assertSame(23, $changed->total());
        $this->assertCount(20, $changed);
    }

    public function test_sizes_are_bounded_and_pages_clamped_without_changing_authorized_query(): void
    {
        $this->actingAsAdmin();
        User::factory()->count(23)->create(['name' => 'Paginable']);
        foreach ([10, 20, 50, 100] as $size) {
            $result = $this->get(route('admin.users.index', ['q' => 'Paginable', 'per_page' => $size]))->assertOk()->viewData('users');
            $this->assertSame($size, $result->perPage());
            $this->assertCount(min(23, $size), $result);
        }
        foreach ([1000000, 0, -1, 'invalid', [100]] as $size) {
            $result = $this->get(route('admin.users.index', ['q' => 'Paginable', 'per_page' => $size, 'page' => 999]))->assertOk()->viewData('users');
            $this->assertSame(20, $result->perPage());
            $this->assertSame(2, $result->currentPage());
            $this->assertCount(3, $result);
        }
        foreach ([0, -2, 'invalid', [2]] as $page) {
            $result = $this->get(route('admin.users.index', ['q' => 'Paginable', 'page' => $page]))->assertOk()->viewData('users');
            $this->assertSame(1, $result->currentPage());
        }
        $this->get(route('admin.users.index', ['q' => 'Sin coincidencias', 'page' => 999]))->assertOk()
            ->assertSee('Mostrando 0–0 de 0')->assertSee('type="button" disabled>Siguiente', false);
    }

    public function test_utilities_demo_has_bounded_pagination_and_does_not_create_records(): void
    {
        $this->app->instance('env', 'local');
        config(['admin_components.enabled' => true]);
        $this->actingAsAdmin();
        $before = User::count();
        $this->get(route('admin.components.utilities', ['page' => 999, 'per_page' => 10]))->assertOk()
            ->assertSee('Mostrando 131–137 de 137')->assertSee('Jerarquía de acciones')
            ->assertSee('Barra de filtros')->assertSee('data-admin-button', false);
        $this->assertSame($before, User::count());
        config(['admin_components.enabled' => false]);
        $this->get(route('admin.components.utilities'))->assertNotFound();
        config(['admin_components.enabled' => true]);
        $this->actingAsAdmin(User::factory()->create(['is_active' => true]))->get(route('admin.components.utilities'))->assertForbidden();
    }
}
