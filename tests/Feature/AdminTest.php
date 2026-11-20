<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\TrackSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Адмінпанель: доступ, CRUD, валідація, зображення, статуси, архівування, статистика, CSV.
 */
class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Нова сесія на треку',
            'description' => 'Опис нової сесії довжиною понад двадцять символів.',
            'category_id' => Category::factory()->create()->id,
            'car_model' => 'Porsche 911 GT3',
            'brand' => 'Porsche',
            'track_name' => 'Slovakia Ring',
            'horsepower' => 510,
            'top_speed_kmh' => 318,
            'zero_to_100' => 3.4,
            'starts_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(5)->addHours(3)->format('Y-m-d\TH:i'),
            'capacity' => 8,
            'status' => 'draft',
        ], $overrides);
    }

    public function test_regular_user_and_guest_cannot_open_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        foreach (['admin.dashboard', 'admin.stats', 'admin.sessions.index', 'admin.sessions.create'] as $route) {
            $this->actingAs($user)->get(route($route))->assertForbidden();
        }
        $this->actingAs($user)->post(route('admin.sessions.store'), $this->validData())->assertForbidden();
        $this->assertDatabaseCount('track_sessions', 0);
    }

    public function test_admin_sees_all_admin_pages(): void
    {
        $session = TrackSession::factory()->create();

        foreach ([route('admin.dashboard'), route('admin.stats'), route('admin.sessions.index'), route('admin.sessions.create'),
            route('admin.sessions.edit', $session), route('admin.sessions.bookings', $session)] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
        }
    }

    public function test_admin_creates_session_with_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('admin.sessions.store'), $this->validData(['image' => UploadedFile::fake()->image('car.jpg', 1600, 900)]))
            ->assertRedirect(route('admin.sessions.index'));

        $session = TrackSession::firstOrFail();
        $this->assertSame($this->admin->id, $session->created_by);
        Storage::disk('public')->assertExists($session->image_path);
        Storage::disk('public')->assertExists(TrackSession::thumbPathFor($session->image_path));
    }

    public function test_invalid_data_is_rejected_with_errors(): void
    {
        $this->actingAs($this->admin)->post(route('admin.sessions.store'), $this->validData([
            'title' => 'Ой',
            'description' => 'Коротко',
            'category_id' => 999,
            'starts_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->subDays(2)->format('Y-m-d\TH:i'),
            'capacity' => 0,
            'image' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasErrors(['title', 'description', 'category_id', 'starts_at', 'ends_at', 'capacity', 'image']);

        $this->assertDatabaseCount('track_sessions', 0);
    }

    public function test_image_larger_than_two_megabytes_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.sessions.store'), $this->validData(['image' => UploadedFile::fake()->image('big.jpg')->size(3000)]))
            ->assertSessionHasErrors('image');
    }

    public function test_update_keeps_unchanged_past_dates_but_not_capacity_below_active_bookings(): void
    {
        $session = TrackSession::factory()->past()->create(['capacity' => 10]);
        Booking::factory()->count(3)->for($session)->create();

        $data = $this->validData([
            'category_id' => $session->category_id,
            'starts_at' => $session->starts_at->format('Y-m-d\TH:i'),
            'ends_at' => $session->ends_at->format('Y-m-d\TH:i'),
            'title' => 'Оновлена назва сесії',
        ]);

        $this->actingAs($this->admin)->put(route('admin.sessions.update', $session), $data)->assertSessionHasNoErrors();
        $this->assertSame('Оновлена назва сесії', $session->fresh()->title);

        $this->actingAs($this->admin)->put(route('admin.sessions.update', $session), ['capacity' => 2] + $data)
            ->assertSessionHasErrors('capacity');
    }

    public function test_publishing_makes_session_visible_in_catalog(): void
    {
        $session = TrackSession::factory()->draft()->create(['title' => 'Секретна чернетка']);
        $this->get(route('sessions.index'))->assertDontSee('Секретна чернетка');

        $this->actingAs($this->admin)->patch(route('admin.sessions.status', $session), ['status' => 'published']);

        $this->get(route('sessions.index'))->assertSee('Секретна чернетка');
    }

    public function test_session_without_bookings_is_deleted_and_with_bookings_is_archived(): void
    {
        $empty = TrackSession::factory()->create();
        $booked = TrackSession::factory()->create();
        Booking::factory()->for($booked)->create();

        $this->actingAs($this->admin)->delete(route('admin.sessions.destroy', $empty));
        $this->actingAs($this->admin)->delete(route('admin.sessions.destroy', $booked));

        $this->assertModelMissing($empty);
        $this->assertSame('archived', $booked->fresh()->status);
    }

    public function test_statistics_match_database(): void
    {
        $top = TrackSession::factory()->create(['title' => 'Найпопулярніша сесія']);
        TrackSession::factory()->draft()->create();
        Booking::factory()->count(3)->for($top)->create();
        Booking::factory()->cancelled()->for($top)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.stats'))->assertOk();

        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats['sessions']);
        $this->assertSame(1, $stats['published']);
        $this->assertSame(User::count(), $stats['users']);
        $this->assertSame(3, $stats['activeBookings']);
        $this->assertSame('Найпопулярніша сесія', $response->viewData('top')->first()->title);
    }

    public function test_bookings_csv_export(): void
    {
        $session = TrackSession::factory()->create();
        $booking = Booking::factory()->for($session)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.sessions.bookings.export', $session))->assertOk();

        $this->assertStringContainsString($booking->user->email, $response->streamedContent());
    }
}
