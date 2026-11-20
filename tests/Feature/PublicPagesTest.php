<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\TrackSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Публічні сторінки: головна, каталог, пошук і фільтри, детальна сторінка, 404.
 */
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_upcoming_published_sessions(): void
    {
        TrackSession::factory()->create(['car_model' => 'Ferrari F40']);
        TrackSession::factory()->draft()->create(['car_model' => 'Hidden Draft Car']);

        $this->get('/')
            ->assertOk()
            ->assertSee('APEX')
            ->assertSee('Ferrari F40')
            ->assertDontSee('Hidden Draft Car');
    }

    public function test_catalog_shows_only_published_sessions(): void
    {
        TrackSession::factory()->create(['title' => 'Опублікована сесія']);
        TrackSession::factory()->draft()->create(['title' => 'Чернетка сесії']);
        TrackSession::factory()->archived()->create(['title' => 'Архівна сесія']);

        $this->get(route('sessions.index'))
            ->assertOk()
            ->assertSee('Опублікована сесія')
            ->assertDontSee('Чернетка сесії')
            ->assertDontSee('Архівна сесія');
    }

    public function test_catalog_search_filters_by_title(): void
    {
        TrackSession::factory()->create(['title' => 'Легенда F40 на треку']);
        TrackSession::factory()->create(['title' => 'Ранок з Porsche']);

        $this->get(route('sessions.index', ['q' => 'F40']))
            ->assertSee('Легенда F40 на треку')
            ->assertDontSee('Ранок з Porsche');
    }

    public function test_catalog_filters_by_category_and_brand(): void
    {
        $hyper = Category::factory()->create(['slug' => 'hypercars']);
        TrackSession::factory()->for($hyper)->create(['title' => 'Гіперкар-сесія', 'car_model' => 'McLaren P1', 'brand' => 'McLaren']);
        TrackSession::factory()->create(['title' => 'Інша сесія', 'car_model' => 'Ferrari Roma', 'brand' => 'Ferrari']);

        $this->get(route('sessions.index', ['category' => 'hypercars']))
            ->assertSee('Гіперкар-сесія')->assertDontSee('Інша сесія');

        $this->get(route('sessions.index', ['brand' => 'Ferrari']))
            ->assertSee('Інша сесія')->assertDontSee('Гіперкар-сесія');
    }

    public function test_catalog_paginates_by_nine(): void
    {
        TrackSession::factory()->count(10)->create();

        $response = $this->get(route('sessions.index'));

        $this->assertCount(9, $response->viewData('sessions'));
        $this->assertSame(10, $response->viewData('sessions')->total());
    }

    public function test_suggest_endpoint_returns_matching_sessions(): void
    {
        TrackSession::factory()->create(['title' => 'Ferrari Roma: гран-тур', 'car_model' => 'Ferrari Roma']);
        TrackSession::factory()->create(['title' => 'Porsche ранок', 'car_model' => 'Porsche 911 GT3']);

        $this->getJson(route('sessions.suggest', ['q' => 'fer']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Ferrari Roma: гран-тур');

        $this->getJson(route('sessions.suggest'))->assertOk()->assertJsonCount(2);
    }

    public function test_session_page_shows_details_and_available_seats(): void
    {
        $session = TrackSession::factory()->create(['capacity' => 20, 'top_speed_kmh' => 330]);

        $this->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee($session->title)
            ->assertSee('330');
    }

    public function test_missing_or_unpublished_session_returns_404(): void
    {
        $draft = TrackSession::factory()->draft()->create();

        $this->get('/sessions/999999')->assertNotFound();
        $this->get(route('sessions.show', $draft))->assertNotFound();
    }
}
