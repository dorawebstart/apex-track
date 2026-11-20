<?php

namespace App\Http\Requests;

use App\Models\TrackSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Створення трек-сесії. Правила — за таблицею «Єдині правила валідації» із завдання.
 */
class StoreTrackSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'car_model' => ['required', 'string', 'max:100'],
            'brand' => ['required', 'string', 'max:50'],
            'track_name' => ['required', 'string', 'max:100'],
            'horsepower' => ['required', 'integer', 'between:1,2000'],
            'top_speed_kmh' => ['required', 'integer', 'between:1,500'],
            'zero_to_100' => ['required', 'numeric', 'between:1,20'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'between:1,10000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::in(array_keys(TrackSession::STATUSES))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'starts_at.after' => 'Дата початку нового заїзду має бути в майбутньому.',
            'ends_at.after' => 'Дата завершення має бути пізніше дати початку.',
            'image.max' => 'Зображення не може бути більшим за 2 МБ.',
        ];
    }
}
