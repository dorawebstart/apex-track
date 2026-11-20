<?php

namespace App\Http\Requests;

use App\Models\TrackSession;
use Illuminate\Support\Carbon;

/**
 * Редагування: незмінені дати минулої сесії можна зберегти, а місткість не може бути
 * меншою за кількість активних бронювань.
 */
class UpdateTrackSessionRequest extends StoreTrackSessionRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var TrackSession $session */
        $session = $this->route('trackSession');

        $rules = parent::rules();

        // Поле datetime-local не передає секунд — порівнюємо з точністю до хвилини
        $startsUnchanged = $this->filled('starts_at')
            && Carbon::parse($this->input('starts_at'))->format('Y-m-d H:i') === $session->starts_at->format('Y-m-d H:i');
        $rules['starts_at'] = $startsUnchanged ? ['required', 'date'] : ['required', 'date', 'after:now'];

        $rules['capacity'] = ['required', 'integer', 'between:'.max(1, $session->activeBookings()->count()).',10000'];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + [
            'capacity.between' => 'Кількість місць має бути від :min (активні бронювання) до :max.',
        ];
    }
}
