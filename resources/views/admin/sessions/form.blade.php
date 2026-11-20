{{-- Спільна форма створення/редагування. old() зберігає введене після помилки валідації. --}}
@php
    $value = fn (string $field, $default = null) => old($field, $session->{$field} ?? $default);
    $dt = fn (string $field) => old($field, $session->{$field}?->format('Y-m-d\TH:i'));
@endphp

@if ($errors->any())
    <div class="mb-10 rounded-2xl bg-rose-50 px-6 py-4 text-rose-700 ring-1 ring-rose-200" role="alert">
        <p class="font-semibold">Перевірте форму — є помилки ({{ $errors->count() }}):</p>
        <ul class="mt-2 list-inside list-disc text-sm">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="grid gap-x-16 gap-y-10 lg:grid-cols-[1.4fr_1fr]">
    <div class="space-y-6">
        <p class="eyebrow text-ink-600">Основне</p>
        <x-field name="title" label="Назва заїзду (5–150 символів)" :value="$value('title')" required maxlength="150" />
        <div>
            <label for="description" class="label !text-ink-600">Опис (20–5000 символів)</label>
            <textarea id="description" name="description" rows="7" maxlength="5000" required
                      @class(['input-light', '!ring-rose-500' => $errors->has('description')])>{{ $value('description') }}</textarea>
            @error('description')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="category_id" class="label !text-ink-600">Клас авто</label>
                <select id="category_id" name="category_id" required @class(['input-light', '!ring-rose-500' => $errors->has('category_id')])>
                    <option value="">— оберіть —</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $value('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
            </div>
            <x-field name="track_name" label="Автодром" :value="$value('track_name')" required />
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <x-field name="car_model" label="Модель авто" :value="$value('car_model')" required />
            <x-field name="brand" label="Марка" :value="$value('brand')" required />
        </div>

        <p class="eyebrow pt-4 text-ink-600">Характеристики</p>
        <div class="grid gap-6 sm:grid-cols-3">
            <x-field name="horsepower" label="Потужність, к.с." type="number" :value="$value('horsepower')" min="1" max="2000" required />
            <x-field name="top_speed_kmh" label="Макс. км/год" type="number" :value="$value('top_speed_kmh')" min="1" max="500" required />
            <x-field name="zero_to_100" label="0–100, с" type="number" step="0.1" :value="$value('zero_to_100')" min="1" max="20" required />
        </div>
    </div>

    <div class="space-y-6">
        <p class="eyebrow text-ink-600">Розклад і місця</p>
        <x-field name="starts_at" label="Початок" type="datetime-local" :value="$dt('starts_at')" required />
        <x-field name="ends_at" label="Завершення" type="datetime-local" :value="$dt('ends_at')" required />
        <x-field name="capacity" label="Кількість місць (1–10000)" type="number" :value="$value('capacity')" min="{{ max(1, $session->active_bookings_count ?? 0) }}" max="10000" required />
        @if ($session->exists && $session->active_bookings_count)
            <p class="-mt-3 text-sm text-ink-600">Активних бронювань: {{ $session->active_bookings_count }} — менше місць встановити не можна.</p>
        @endif

        <div>
            <label for="status" class="label !text-ink-600">Статус публікації</label>
            <select id="status" name="status" class="input-light">
                @foreach (\App\Models\TrackSession::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected($value('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <p class="eyebrow pt-4 text-ink-600">Зображення</p>
        <div data-image-preview>
            <img src="{{ $session->image_url }}" alt="" class="aspect-[16/10] w-full rounded-2xl object-cover" data-preview-img>
            <label for="image" class="label mt-4 !text-ink-600">{{ $session->image_path ? 'Замінити фото' : 'Завантажити фото' }} (JPEG, PNG, WebP, до 2 МБ)</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                   class="block w-full text-sm file:mr-4 file:rounded-full file:border-0 file:bg-ink-900 file:px-5 file:py-2.5 file:font-semibold file:text-white hover:file:bg-night-800">
            @error('image')<p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="mt-12 flex flex-wrap gap-4 border-t border-ink-900/10 pt-8">
    <button class="btn-dark">{{ $session->exists ? 'Зберегти зміни' : 'Створити заїзд' }}</button>
    <a href="{{ route('admin.sessions.index') }}" class="btn border border-ink-900/20 hover:border-ink-900">Скасувати</a>
</div>
