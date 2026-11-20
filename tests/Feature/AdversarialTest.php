<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\TrackSession;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Адверсаріальні атаки. Кожен тест асертить ПРАВИЛЬНУ поведінку: падіння = знайдений дефект.
 */
class AdversarialTest extends TestCase
{
    use RefreshDatabase;

    private const XSS = '<script>alert(1)</script>';

    private function validSessionPayload(array $override = []): array
    {
        return array_merge([
            'title' => 'Новий трек-день',
            'description' => str_repeat('Опис заїзду. ', 3),
            'category_id' => Category::factory()->create()->id,
            'car_model' => 'Porsche 911 GT3',
            'brand' => 'Porsche',
            'track_name' => 'Slovakia Ring',
            'horsepower' => 500,
            'top_speed_kmh' => 300,
            'zero_to_100' => 3.4,
            'starts_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(5)->addHours(3)->format('Y-m-d\TH:i'),
            'capacity' => 10,
            'status' => 'published',
        ], $override);
    }

    // ── Пошук / фільтри ──────────────────────────────────────────────

    public function test_attack_like_wildcard_percent_matches_everything(): void
    {
        $plain = TrackSession::factory()->create(['title' => 'Звичайний заїзд', 'car_model' => 'Ferrari 296 GTB']);

        $this->get('/sessions?q=%25')->assertOk()->assertDontSee($plain->title);
    }

    public function test_attack_like_wildcard_underscore_matches_everything(): void
    {
        $plain = TrackSession::factory()->create(['title' => 'Звичайний заїзд', 'car_model' => 'Ferrari 296 GTB']);

        $this->get('/sessions?q=_')->assertOk()->assertDontSee($plain->title);
    }

    public function test_attack_suggest_like_wildcard_returns_unrelated(): void
    {
        TrackSession::factory()->create(['title' => 'Звичайний заїзд', 'car_model' => 'Ferrari 296 GTB']);

        $this->getJson('/sessions/suggest?q=%25')->assertOk()->assertJsonCount(0);
    }

    public function test_attack_suggest_array_query_does_not_500(): void
    {
        TrackSession::factory()->create();

        $status = $this->get('/sessions/suggest?q[]=x')->status();
        $this->assertLessThan(500, $status, "Гість отримав HTTP {$status} на /sessions/suggest?q[]=x");
    }

    public function test_attack_catalog_array_params_do_not_500(): void
    {
        foreach (['q[]=x', 'category[]=x', 'from[]=2026-01-01', 'to[a]=b', 'page[]=2', 'brand[]=x'] as $qs) {
            $this->assertLessThan(500, $this->get('/sessions?'.$qs)->status(), $qs);
        }
    }

    public function test_attack_catalog_hides_draft_and_archived(): void
    {
        $draft = TrackSession::factory()->draft()->create(['title' => 'Секретна чернетка']);
        $arch = TrackSession::factory()->archived()->create(['title' => 'Архівний заїзд']);

        $this->get('/sessions')->assertDontSee($draft->title)->assertDontSee($arch->title);
        $this->get('/sessions?q=Секретна')->assertDontSee($draft->title);
        $this->getJson('/sessions/suggest?q=Секретна')->assertJsonCount(0);
        $this->getJson('/sessions/suggest?q=Архівний')->assertJsonCount(0);
    }

    public function test_attack_date_filter_day_boundary_inclusive(): void
    {
        $day = now()->addDays(10)->startOfDay();
        $late = TrackSession::factory()->create(['title' => 'Пізній заїзд', 'starts_at' => $day->copy()->setTime(23, 59), 'ends_at' => $day->copy()->addDay()->setTime(2, 0)]);
        $early = TrackSession::factory()->create(['title' => 'Ранній заїзд', 'starts_at' => $day->copy()->setTime(0, 0), 'ends_at' => $day->copy()->setTime(3, 0)]);
        $next = TrackSession::factory()->create(['title' => 'Наступний день', 'starts_at' => $day->copy()->addDay()->setTime(0, 0), 'ends_at' => $day->copy()->addDay()->setTime(3, 0)]);

        $d = $day->format('Y-m-d');
        $this->get("/sessions?from={$d}&to={$d}")
            ->assertSee($late->title)->assertSee($early->title)->assertDontSee($next->title);
    }

    public function test_attack_date_filter_reversed_range_rejected(): void
    {
        $this->get('/sessions?from=2027-05-10&to=2027-05-01')->assertSessionHasErrors('to');
    }

    public function test_attack_pagination_nine_and_keeps_params(): void
    {
        TrackSession::factory()->count(10)->create(['title' => 'Пошуковий заїзд']);

        $res = $this->get('/sessions?q=Пошуковий');
        $this->assertCount(9, $res->viewData('sessions')->items());
        $this->assertStringContainsString('q=', $res->viewData('sessions')->nextPageUrl());
    }

    // ── Детальна сторінка ───────────────────────────────────────────

    public function test_attack_show_unpublished_is_404_for_guest_and_user(): void
    {
        $user = User::factory()->create();
        foreach ([TrackSession::factory()->draft()->create(), TrackSession::factory()->archived()->create()] as $s) {
            $this->get("/sessions/{$s->id}")->assertNotFound();
            $this->actingAs($user)->get("/sessions/{$s->id}")->assertNotFound();
            auth()->logout();
        }
        $this->get('/sessions/999999')->assertNotFound();
        $this->get('/sessions/abc')->assertNotFound();
    }

    // ── Бронювання: стан і межі ─────────────────────────────────────

    public function test_attack_guest_cannot_book(): void
    {
        $s = TrackSession::factory()->create();
        $this->post("/sessions/{$s->id}/book")->assertRedirect('/login');
        $this->assertSame(0, Booking::count());
    }

    public function test_attack_book_draft_archived_past_rejected(): void
    {
        $user = User::factory()->create();
        $sessions = [
            TrackSession::factory()->draft()->create(),
            TrackSession::factory()->archived()->create(),
            TrackSession::factory()->past()->create(),
            TrackSession::factory()->create(['starts_at' => now(), 'ends_at' => now()->addHour()]),
        ];
        foreach ($sessions as $s) {
            $this->actingAs($user)->post("/sessions/{$s->id}/book")->assertSessionHas('error');
        }
        $this->assertSame(0, Booking::count());
    }

    public function test_attack_reactivate_cancelled_on_archived_rejected(): void
    {
        $user = User::factory()->create();
        $s = TrackSession::factory()->archived()->create();
        Booking::factory()->cancelled()->create(['user_id' => $user->id, 'track_session_id' => $s->id]);

        $this->actingAs($user)->post("/sessions/{$s->id}/book")->assertSessionHas('error');
        $this->assertSame(0, Booking::where('status', 'active')->count());
    }

    public function test_attack_capacity_last_seat_and_reactivation_respects_capacity(): void
    {
        $s = TrackSession::factory()->create(['capacity' => 1]);
        [$a, $b] = User::factory()->count(2)->create();

        $this->actingAs($a)->post("/sessions/{$s->id}/book")->assertSessionHas('success');
        $this->actingAs($b)->post("/sessions/{$s->id}/book")->assertSessionHas('error');

        $bookingA = Booking::where('user_id', $a->id)->first();
        $this->actingAs($a)->delete("/bookings/{$bookingA->id}");
        $this->actingAs($b)->post("/sessions/{$s->id}/book")->assertSessionHas('success');
        // A намагається повторно активувати скасовану, коли місце вже зайняте
        $this->actingAs($a)->post("/sessions/{$s->id}/book")->assertSessionHas('error');

        $this->assertSame(1, $s->activeBookings()->count());
        $this->assertSame(2, Booking::count());
    }

    public function test_attack_reactivation_reuses_same_row(): void
    {
        $user = User::factory()->create();
        $s = TrackSession::factory()->create();
        $this->actingAs($user)->post("/sessions/{$s->id}/book");
        $id = Booking::first()->id;
        $this->actingAs($user)->delete("/bookings/{$id}");
        $this->actingAs($user)->post("/sessions/{$s->id}/book")->assertSessionHas('success');

        $this->assertSame(1, Booking::count());
        $this->assertSame('active', Booking::find($id)->status);
    }

    public function test_attack_db_unique_user_session_pair(): void
    {
        $user = User::factory()->create();
        $s = TrackSession::factory()->create();
        Booking::factory()->cancelled()->create(['user_id' => $user->id, 'track_session_id' => $s->id]);

        $this->expectException(QueryException::class);
        Booking::factory()->create(['user_id' => $user->id, 'track_session_id' => $s->id]);
    }

    /**
     * Справжньої паралельності в PHPUnit немає. Логічно: store() виконує SELECT ... FOR UPDATE
     * на рядку сесії всередині транзакції, тож другий запит чекає, поки перший закомітить запис,
     * і вже бачить оновлений лічильник. Тут перевіряємо, що lockForUpdate справді в запиті.
     */
    public function test_attack_concurrency_booking_uses_row_lock(): void
    {
        $user = User::factory()->create();
        $s = TrackSession::factory()->create(['capacity' => 1]);
        $sql = [];
        \DB::listen(function ($q) use (&$sql) {
            $sql[] = $q->sql;
        });

        $this->actingAs($user)->post("/sessions/{$s->id}/book");

        $this->assertNotEmpty(array_filter($sql, fn ($q) => str_contains(strtolower($q), 'for update')));
    }

    // ── IDOR / права ────────────────────────────────────────────────

    public function test_attack_idor_cancel_foreign_booking(): void
    {
        $victim = Booking::factory()->create();
        $attacker = User::factory()->create();

        $this->actingAs($attacker)->delete("/bookings/{$victim->id}")->assertForbidden();
        $this->assertSame('active', $victim->fresh()->status);
    }

    public function test_attack_guest_cancel_booking(): void
    {
        $b = Booking::factory()->create();
        $this->delete("/bookings/{$b->id}")->assertRedirect('/login');
        $this->assertSame('active', $b->fresh()->status);
    }

    public function test_attack_cabinet_shows_only_own_bookings(): void
    {
        $me = User::factory()->create();
        $foreignSession = TrackSession::factory()->create(['title' => 'Чужий заїзд XYZ']);
        Booking::factory()->create(['track_session_id' => $foreignSession->id]);

        $this->actingAs($me)->get('/cabinet')->assertOk()->assertDontSee('Чужий заїзд XYZ');
    }

    public function test_attack_user_cannot_reach_any_admin_route(): void
    {
        $user = User::factory()->create();
        $s = TrackSession::factory()->create();
        $this->actingAs($user);

        $this->get('/admin')->assertForbidden();
        $this->get('/admin/stats')->assertForbidden();
        $this->get('/admin/sessions')->assertForbidden();
        $this->get('/admin/sessions/create')->assertForbidden();
        $this->post('/admin/sessions', $this->validSessionPayload())->assertForbidden();
        $this->get("/admin/sessions/{$s->id}/edit")->assertForbidden();
        $this->put("/admin/sessions/{$s->id}", $this->validSessionPayload())->assertForbidden();
        $this->patch("/admin/sessions/{$s->id}/status", ['status' => 'archived'])->assertForbidden();
        $this->delete("/admin/sessions/{$s->id}")->assertForbidden();
        $this->get("/admin/sessions/{$s->id}/bookings")->assertForbidden();
        $this->get("/admin/sessions/{$s->id}/bookings.csv")->assertForbidden();

        $this->assertSame('published', $s->fresh()->status);
    }

    public function test_attack_guest_admin_routes_redirect_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->post('/admin/sessions', [])->assertRedirect('/login');
    }

    // ── Реєстрація ──────────────────────────────────────────────────

    public function test_attack_register_role_injection(): void
    {
        $this->post('/register', [
            'name' => 'Hacker', 'email' => 'h@example.com', 'role' => 'admin',
            'password' => 'Secret123', 'password_confirmation' => 'Secret123',
        ])->assertRedirect();

        $u = User::where('email', 'h@example.com')->firstOrFail();
        $this->assertSame('user', $u->role);
        $this->assertNotSame('Secret123', $u->getRawOriginal('password'));
        $this->assertTrue(\Hash::check('Secret123', $u->password));
    }

    public function test_attack_register_weak_passwords_rejected(): void
    {
        $bad = ['Short1A', 'alllower123', 'ALLUPPER123', 'NoDigitsHere', 'Has Space1', "Tab\tPass1", "Nbsp\u{00A0}Pass1", "Line\nPass1A", '        '];
        foreach ($bad as $i => $pwd) {
            $this->post('/register', [
                'name' => 'User', 'email' => "u{$i}@example.com",
                'password' => $pwd, 'password_confirmation' => $pwd,
            ])->assertSessionHasErrors('password');
        }
        $this->assertSame(0, User::count());
    }

    public function test_attack_register_array_fields_do_not_500(): void
    {
        $res = $this->post('/register', [
            'name' => ['x'], 'email' => ['a@b.c'], 'password' => ['Secret123'], 'password_confirmation' => ['Secret123'],
        ]);
        $this->assertLessThan(500, $res->status());
        $this->assertSame(0, User::count());
    }

    // ── Адмін: валідація ────────────────────────────────────────────

    public function test_attack_admin_store_boundaries(): void
    {
        $admin = User::factory()->admin()->create();
        $cases = [
            'title' => ['abcd', str_repeat('я', 151), '    '],
            'description' => [str_repeat('a', 19), str_repeat('a', 5001)],
            'category_id' => [999999, 'abc', ['1']],
            'capacity' => [0, -1, 10001, 'ten', 1.5, ''],
            'starts_at' => [now()->subMinute()->format('Y-m-d\TH:i'), 'garbage'],
        ];
        foreach ($cases as $field => $values) {
            foreach ($values as $v) {
                $this->actingAs($admin)->post('/admin/sessions', $this->validSessionPayload([$field => $v]))
                    ->assertSessionHasErrors($field);
            }
        }
        // кінець = початок і кінець раніше початку
        $start = now()->addDays(3)->format('Y-m-d\TH:i');
        $this->actingAs($admin)->post('/admin/sessions', $this->validSessionPayload(['starts_at' => $start, 'ends_at' => $start]))
            ->assertSessionHasErrors('ends_at');
        $this->assertSame(0, TrackSession::count());

        // граничні допустимі значення
        $this->actingAs($admin)->post('/admin/sessions', $this->validSessionPayload([
            'title' => str_repeat('я', 150), 'description' => str_repeat('я', 5000), 'capacity' => 10000,
        ]))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post('/admin/sessions', $this->validSessionPayload([
            'title' => 'abcde', 'description' => str_repeat('a', 20), 'capacity' => 1,
        ]))->assertSessionHasNoErrors();
        $this->assertSame(2, TrackSession::count());
    }

    public function test_attack_admin_store_mass_assignment(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->create();

        $this->actingAs($admin)->post('/admin/sessions', $this->validSessionPayload([
            'created_by' => $other->id, 'image_path' => '../../.env', 'id' => 777,
        ]))->assertSessionHasNoErrors();

        $s = TrackSession::firstOrFail();
        $this->assertSame($admin->id, $s->created_by);
        $this->assertNull($s->image_path);
        $this->assertNotSame(777, $s->id);
    }

    public function test_attack_admin_upload_php_disguised_as_jpeg_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $path = tempnam(sys_get_temp_dir(), 'adv');
        file_put_contents($path, '<?php echo phpversion(); ?>');
        $file = new UploadedFile($path, 'car.jpg', 'image/jpeg', null, true);

        $this->actingAs($admin)->post('/admin/sessions', $this->validSessionPayload(['image' => $file]))
            ->assertSessionHasErrors('image');
        $this->assertSame(0, TrackSession::count());
    }

    public function test_attack_admin_upload_svg_disguised_as_png_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $path = tempnam(sys_get_temp_dir(), 'adv');
        file_put_contents($path, '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><rect width="10" height="10"/></svg>');
        $file = new UploadedFile($path, 'car.png', 'image/png', null, true);

        $this->actingAs($admin)->post('/admin/sessions', $this->validSessionPayload(['image' => $file]))
            ->assertSessionHasErrors('image');
    }

    public function test_attack_admin_upload_too_big_rejected(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/sessions', $this->validSessionPayload([
            'image' => UploadedFile::fake()->image('big.jpg')->size(2049),
        ]))->assertSessionHasErrors('image');
    }

    public function test_attack_admin_update_past_session_with_unchanged_dates_allowed(): void
    {
        $admin = User::factory()->admin()->create();
        $s = TrackSession::factory()->past()->create();

        $this->actingAs($admin)->put("/admin/sessions/{$s->id}", $this->validSessionPayload([
            'category_id' => $s->category_id,
            'starts_at' => $s->starts_at->format('Y-m-d\TH:i'),
            'ends_at' => $s->ends_at->format('Y-m-d\TH:i'),
            'title' => 'Оновлена назва',
        ]))->assertSessionHasNoErrors();
        $this->assertSame('Оновлена назва', $s->fresh()->title);
    }

    public function test_attack_admin_update_capacity_below_active_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $s = TrackSession::factory()->create(['capacity' => 5]);
        Booking::factory()->count(3)->create(['track_session_id' => $s->id]);
        Booking::factory()->cancelled()->create(['track_session_id' => $s->id]);

        $payload = fn ($cap) => $this->validSessionPayload([
            'category_id' => $s->category_id, 'capacity' => $cap,
            'starts_at' => $s->starts_at->format('Y-m-d\TH:i'), 'ends_at' => $s->ends_at->format('Y-m-d\TH:i'),
        ]);
        $this->actingAs($admin)->put("/admin/sessions/{$s->id}", $payload(2))->assertSessionHasErrors('capacity');
        $this->actingAs($admin)->put("/admin/sessions/{$s->id}", $payload(3))->assertSessionHasNoErrors();
        $this->assertSame(3, $s->fresh()->capacity);
    }

    public function test_attack_admin_update_garbage_starts_at_does_not_500(): void
    {
        $admin = User::factory()->admin()->create();
        $s = TrackSession::factory()->create();

        foreach (['garbage', ['x']] as $v) {
            $res = $this->actingAs($admin)->put("/admin/sessions/{$s->id}", $this->validSessionPayload([
                'category_id' => $s->category_id, 'starts_at' => $v,
            ]));
            $this->assertLessThan(500, $res->status(), 'starts_at='.json_encode($v).' дав HTTP '.$res->status());
        }
    }

    public function test_attack_admin_index_array_status_filter_does_not_500(): void
    {
        $admin = User::factory()->admin()->create();
        TrackSession::factory()->create();

        $this->assertLessThan(500, $this->actingAs($admin)->get('/admin/sessions?status[]=draft')->status());
    }

    public function test_attack_admin_status_invalid_value_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $s = TrackSession::factory()->create();

        $this->actingAs($admin)->patch("/admin/sessions/{$s->id}/status", ['status' => 'deleted'])->assertSessionHasErrors('status');
        $this->actingAs($admin)->patch("/admin/sessions/{$s->id}/status", ['status' => ['published']])->assertSessionHasErrors('status');
        $this->assertSame('published', $s->fresh()->status);
    }

    public function test_attack_destroy_with_bookings_archives_not_deletes(): void
    {
        $admin = User::factory()->admin()->create();
        $withCancelled = TrackSession::factory()->create();
        Booking::factory()->cancelled()->create(['track_session_id' => $withCancelled->id]);
        $empty = TrackSession::factory()->create();

        $this->actingAs($admin)->delete("/admin/sessions/{$withCancelled->id}");
        $this->actingAs($admin)->delete("/admin/sessions/{$empty->id}");

        $this->assertSame('archived', $withCancelled->fresh()?->status);
        $this->assertNull($empty->fresh());
    }

    // ── XSS ─────────────────────────────────────────────────────────

    public function test_attack_xss_everywhere(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => self::XSS]);
        $cat = Category::factory()->create(['name' => self::XSS]);
        $s = TrackSession::factory()->create([
            'title' => self::XSS.' title', 'description' => self::XSS.str_repeat(' desc', 10),
            'car_model' => self::XSS, 'track_name' => self::XSS, 'brand' => self::XSS, 'category_id' => $cat->id,
        ]);
        Booking::factory()->create(['user_id' => $user->id, 'track_session_id' => $s->id]);

        $guestPages = ['/', '/sessions', "/sessions/{$s->id}", '/sessions?q='.urlencode(self::XSS)];
        foreach ($guestPages as $url) {
            $this->get($url)->assertOk()->assertDontSee(self::XSS, false);
        }
        $this->actingAs($user)->get('/cabinet')->assertOk()->assertDontSee(self::XSS, false);
        $this->actingAs($user)->get("/sessions/{$s->id}")->assertOk()->assertDontSee(self::XSS, false);

        foreach (['/admin', '/admin/stats', '/admin/sessions', "/admin/sessions/{$s->id}/edit", "/admin/sessions/{$s->id}/bookings"] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertDontSee(self::XSS, false);
        }

        $json = $this->getJson('/sessions/suggest?q=script');
        $json->assertOk()->assertHeader('Content-Type', 'application/json');
        $this->assertSame(self::XSS.' title', $json->json('0.title'));
    }

    public function test_attack_xss_data_confirm_attribute_breakout(): void
    {
        $user = User::factory()->create();
        $payload = '" onmouseover="alert(1)';
        $s = TrackSession::factory()->create(['title' => 'Заїзд '.$payload]);

        $this->actingAs($user)->get("/sessions/{$s->id}")->assertDontSee($payload, false);
        Booking::factory()->create(['user_id' => $user->id, 'track_session_id' => $s->id]);
        $this->actingAs($user)->get('/cabinet')->assertDontSee($payload, false);
        $this->actingAs(User::factory()->admin()->create())->get('/admin/sessions')->assertDontSee($payload, false);
    }

    // ── Статистика / CSV ────────────────────────────────────────────

    public function test_attack_stats_match_db(): void
    {
        $admin = User::factory()->admin()->create();
        $sessions = TrackSession::factory()->count(7)->create(['capacity' => 20]);
        TrackSession::factory()->draft()->create();
        TrackSession::factory()->archived()->create();
        foreach ($sessions as $i => $s) {
            Booking::factory()->count($i)->create(['track_session_id' => $s->id]);
            Booking::factory()->cancelled()->count(5)->create(['track_session_id' => $s->id]);
        }

        $res = $this->actingAs($admin)->get('/admin/stats')->assertOk();
        $stats = $res->viewData('stats');
        $this->assertSame(TrackSession::count(), $stats['sessions']);
        $this->assertSame(7, $stats['published']);
        $this->assertSame(User::count(), $stats['users']);
        $this->assertSame(21, $stats['activeBookings']);

        $top = $res->viewData('top');
        $this->assertCount(5, $top);
        $this->assertSame([6, 5, 4, 3, 2], $top->pluck('active_bookings_count')->map(fn ($v) => (int) $v)->all());
    }

    public function test_attack_csv_export_consistency(): void
    {
        $admin = User::factory()->admin()->create();
        $s = TrackSession::factory()->create();
        Booking::factory()->count(2)->create(['track_session_id' => $s->id]);
        Booking::factory()->cancelled()->create(['track_session_id' => $s->id]);
        Booking::factory()->create(); // чужа сесія

        $csv = $this->actingAs($admin)->get("/admin/sessions/{$s->id}/bookings.csv")->assertOk()->streamedContent();
        $lines = array_values(array_filter(explode("\n", trim($csv))));
        $this->assertCount(4, $lines); // заголовок + 3
        $this->assertSame(2, substr_count($csv, 'Активне'));
        $this->assertSame(1, substr_count($csv, 'Скасоване'));
    }

    /** Поза вимогами: CSV/formula injection через ім'я користувача. */
    public function test_attack_csv_formula_injection_out_of_scope(): void
    {
        $this->markTestSkipped('Поза вимогами завдання — за рішенням студента не виправлялось (адверсаріальне ревью, раунд 1).');
        $admin = User::factory()->admin()->create();
        $s = TrackSession::factory()->create();
        $evil = User::factory()->create(['name' => '=HYPERLINK("http://evil","x")']);
        Booking::factory()->create(['user_id' => $evil->id, 'track_session_id' => $s->id]);

        $csv = $this->actingAs($admin)->get("/admin/sessions/{$s->id}/bookings.csv")->streamedContent();
        $this->assertDoesNotMatchRegularExpression('/(^|;)"?=HYPERLINK/m', $csv);
    }

    /** Поза вимогами: скасування заявки на заїзд, що вже розпочався. */
    public function test_attack_cancel_after_session_started_out_of_scope(): void
    {
        $this->markTestSkipped('Поза вимогами завдання — за рішенням студента не виправлялось (адверсаріальне ревью, раунд 1).');
        $user = User::factory()->create();
        $s = TrackSession::factory()->past()->create();
        $b = Booking::factory()->create(['user_id' => $user->id, 'track_session_id' => $s->id]);

        $this->actingAs($user)->delete("/bookings/{$b->id}");
        $this->assertSame('active', $b->fresh()->status);
    }
}
