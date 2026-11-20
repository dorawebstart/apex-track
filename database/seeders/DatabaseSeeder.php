<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\TrackSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Демо-дані для захисту: 1 адмін, 4 пілоти, 4 класи, 23 заїзди (20 опубліковано),
     * 14 активних і 2 скасовані бронювання, одна сесія повністю заповнена.
     * Пароль усіх демо-акаунтів: Apex2026
     */
    public function run(): void
    {
        Storage::disk('public')->deleteDirectory('sessions');

        User::factory()->admin()->create(['name' => 'Адміністратор APEX', 'email' => 'admin@apex.test', 'password' => 'Apex2026']);
        $pilots = collect([
            ['Олена Коваль', 'olena@apex.test'],
            ['Максим Бондар', 'maksym@apex.test'],
            ['Ірина Шевчук', 'iryna@apex.test'],
            ['Андрій Мельник', 'andrii@apex.test'],
        ])->map(fn (array $p): User => User::factory()->create(['name' => $p[0], 'email' => $p[1], 'password' => 'Apex2026']));

        $this->call([CategorySeeder::class, TrackSessionSeeder::class]);

        // [авто сесії => [індекси пілотів з активною бронею], [індекси зі скасованою]]
        $plan = [
            'Huracán STO: атмосферний V10' => [[0, 1], []],   // місткість 2 — заповнено повністю
            'Легенда F40: аналоговий драйв' => [[0, 2], [1]],
            'Гібридний ранок на Slovakia Ring' => [[0, 1, 2, 3], []],
            '911 GT3 RS: школа точності' => [[1, 3], [2]],
            'Болід F1 Ferrari F2004' => [[0, 3], []],
            'P1 MSO: архівний заїзд' => [[0, 1], []],
        ];

        foreach ($plan as $title => [$active, $cancelled]) {
            $session = TrackSession::where('title', $title)->firstOrFail();
            foreach ($active as $i) {
                Booking::create(['user_id' => $pilots[$i]->id, 'track_session_id' => $session->id, 'status' => 'active']);
            }
            foreach ($cancelled as $i) {
                Booking::create(['user_id' => $pilots[$i]->id, 'track_session_id' => $session->id, 'status' => 'cancelled']);
            }
        }
    }
}
