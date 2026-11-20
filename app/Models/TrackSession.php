<?php

namespace App\Models;

use Database\Factories\TrackSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Трек-сесія: конкретне авто на треку в заданий час. capacity — кількість пілотів-слотів.
 */
#[Fillable([
    'category_id', 'created_by', 'title', 'description', 'car_model', 'track_name',
    'horsepower', 'top_speed_kmh', 'zero_to_100', 'starts_at', 'ends_at', 'capacity', 'image_path', 'status',
])]
class TrackSession extends Model
{
    /** @use HasFactory<TrackSessionFactory> */
    use HasFactory;

    public const STATUSES = ['draft' => 'Чернетка', 'published' => 'Опубліковано', 'archived' => 'Архів'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'zero_to_100' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function activeBookings(): HasMany
    {
        return $this->bookings()->where('status', 'active');
    }

    /**
     * Лише опубліковані сесії.
     *
     * @param  Builder<TrackSession>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /**
     * «Актуальні»: опубліковані з майбутньою датою початку.
     *
     * @param  Builder<TrackSession>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->published()->where('starts_at', '>', now());
    }

    /**
     * Кількість активних бронювань (бере withCount, якщо вже завантажено).
     */
    protected function activeBookingsTotal(): Attribute
    {
        return Attribute::get(fn (): int => $this->active_bookings_count ?? $this->activeBookings()->count());
    }

    protected function availableSeats(): Attribute
    {
        return Attribute::get(fn (): int => max(0, $this->capacity - $this->active_bookings_total));
    }

    /**
     * Оригінал фото (повна якість) — для детальної сторінки та слайдера.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : asset('images/placeholder-car.svg'));
    }

    /**
     * Копія 1280px для карток каталогу, щоб сторінка з 9 картками не вантажила десятки МБ.
     */
    protected function thumbUrl(): Attribute
    {
        return Attribute::get(function (): string {
            $thumb = $this->image_path ? self::thumbPathFor($this->image_path) : null;

            return $thumb && Storage::disk('public')->exists($thumb) ? Storage::disk('public')->url($thumb) : $this->image_url;
        });
    }

    public static function thumbPathFor(string $imagePath): string
    {
        return 'sessions/thumbs/'.pathinfo($imagePath, PATHINFO_FILENAME).'.webp';
    }

    /**
     * Зберігає оригінал на диску public і створює WebP-мініатюру для карток.
     *
     * @return string шлях оригіналу відносно диска public
     */
    public static function storeImage(string $sourcePath, string $extension): string
    {
        $path = 'sessions/'.uniqid('car_', true).'.'.strtolower($extension);
        Storage::disk('public')->put($path, file_get_contents($sourcePath));

        $image = imagecreatefromstring(file_get_contents($sourcePath));
        if ($image !== false) {
            $thumb = imagescale($image, min(1280, imagesx($image)));
            ob_start();
            imagewebp($thumb, null, 88);
            Storage::disk('public')->put(self::thumbPathFor($path), ob_get_clean());
        }

        return $path;
    }

    /**
     * Видаляє оригінал і мініатюру.
     */
    public static function deleteImage(?string $imagePath): void
    {
        if ($imagePath) {
            Storage::disk('public')->delete([$imagePath, self::thumbPathFor($imagePath)]);
        }
    }

    /**
     * Чи можна зараз бронювати (опубліковано, ще не почалось, є місця).
     */
    public function isBookable(): bool
    {
        return $this->status === 'published' && $this->starts_at->isFuture() && $this->available_seats > 0;
    }
}
