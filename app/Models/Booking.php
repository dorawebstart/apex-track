<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Бронювання заїзду (заявка користувача на трек-сесію).
 */
#[Fillable(['user_id', 'track_session_id', 'status'])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public const STATUSES = ['active' => 'Активне', 'cancelled' => 'Скасоване'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<TrackSession, $this>
     */
    public function trackSession(): BelongsTo
    {
        return $this->belongsTo(TrackSession::class);
    }
}
