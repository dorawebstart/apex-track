<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TrackSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Механізм бронювання: створення, дублікати, ліміт місць, скасування, повторна активація, кабінет.
 */
class BookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_book_a_published_future_session(): void
    {
        $user = User::factory()->create();
        $session = TrackSession::factory()->create();

        $this->actingAs($user)->post(route('bookings.store', $session))->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', ['user_id' => $user->id, 'track_session_id' => $session->id, 'status' => 'active']);
    }

    public function test_repeated_booking_does_not_create_duplicate(): void
    {
        $user = User::factory()->create();
        $session = TrackSession::factory()->create();

        $this->actingAs($user)->post(route('bookings.store', $session));
        $this->actingAs($user)->post(route('bookings.store', $session))->assertSessionHas('error');

        $this->assertSame(1, Booking::where('user_id', $user->id)->count());
    }

    public function test_control_example_twenty_seats_fifteen_booked_leaves_four_after_booking(): void
    {
        $session = TrackSession::factory()->create(['capacity' => 20]);
        Booking::factory()->count(15)->for($session)->create();

        $this->assertSame(5, $session->fresh()->available_seats);

        $this->actingAs(User::factory()->create())->post(route('bookings.store', $session));

        $this->assertSame(4, $session->fresh()->available_seats);
    }

    public function test_booking_is_blocked_when_all_seats_are_taken(): void
    {
        $session = TrackSession::factory()->create(['capacity' => 2]);
        Booking::factory()->count(2)->for($session)->create();

        $this->actingAs(User::factory()->create())->post(route('bookings.store', $session))->assertSessionHas('error');

        $this->assertSame(2, $session->activeBookings()->count());
    }

    public function test_cancelled_bookings_do_not_take_seats(): void
    {
        $session = TrackSession::factory()->create(['capacity' => 1]);
        Booking::factory()->cancelled()->for($session)->create();

        $this->actingAs(User::factory()->create())->post(route('bookings.store', $session))->assertSessionHas('success');
    }

    public function test_cannot_book_archived_draft_or_started_sessions(): void
    {
        $user = User::factory()->create();

        foreach ([
            TrackSession::factory()->archived()->create(),
            TrackSession::factory()->draft()->create(),
            TrackSession::factory()->past()->create(),
        ] as $session) {
            $this->actingAs($user)->post(route('bookings.store', $session))->assertSessionHas('error');
        }

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_cancel_frees_seat_and_rebooking_reactivates_same_record(): void
    {
        $user = User::factory()->create();
        $session = TrackSession::factory()->create(['capacity' => 5]);
        $booking = Booking::factory()->for($user)->for($session)->create();

        $this->actingAs($user)->delete(route('bookings.destroy', $booking))->assertSessionHas('success');
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->assertSame(5, $session->fresh()->available_seats);

        $this->actingAs($user)->post(route('bookings.store', $session))->assertSessionHas('success');
        $this->assertSame('active', $booking->fresh()->status);
        $this->assertSame(1, Booking::count());
    }

    public function test_user_cannot_cancel_someone_elses_booking(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->create())->delete(route('bookings.destroy', $booking))->assertForbidden();

        $this->assertSame('active', $booking->fresh()->status);
    }

    public function test_cabinet_lists_only_own_bookings(): void
    {
        $user = User::factory()->create();
        $own = Booking::factory()->for($user)->create();
        $foreign = Booking::factory()->create();

        $this->actingAs($user)->get(route('cabinet'))
            ->assertOk()
            ->assertSee($user->email)
            ->assertSee($own->trackSession->title)
            ->assertDontSee($foreign->trackSession->title);
    }
}
