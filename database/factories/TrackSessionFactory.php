<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\TrackSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackSession>
 */
class TrackSessionFactory extends Factory
{
    /**
     * Опублікована майбутня сесія.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('+2 days', '+60 days');

        return [
            'category_id' => Category::factory(),
            'created_by' => User::factory()->admin(),
            'title' => 'Трек-день '.fake()->unique()->bothify('??-###'),
            'description' => fake()->paragraph(3),
            'car_model' => fake()->randomElement(['Porsche 911 GT3', 'Ferrari 296 GTB', 'McLaren 750S']),
            'track_name' => fake()->randomElement(['Slovakia Ring', 'Hungaroring', 'Tor Poznań']),
            'horsepower' => fake()->numberBetween(400, 900),
            'top_speed_kmh' => fake()->numberBetween(280, 350),
            'zero_to_100' => fake()->randomFloat(1, 2.5, 4.5),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+3 hours'),
            'capacity' => 10,
            'image_path' => null,
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function archived(): static
    {
        return $this->state(['status' => 'archived']);
    }

    public function past(): static
    {
        return $this->state(fn (): array => ['starts_at' => now()->subDays(3), 'ends_at' => now()->subDays(3)->addHours(3)]);
    }
}
