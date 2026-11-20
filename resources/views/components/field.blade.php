@props(['name', 'label', 'type' => 'text'])

{{-- Поле форми на світлому тлі: збереження введеного (old) та повідомлення про помилку --}}
<div>
    <label for="{{ $name }}" class="label !text-ink-600">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           @if ($type !== 'password') value="{{ old($name) }}" @endif
           {{ $attributes->class(['input-light', '!ring-rose-500' => $errors->has($name)]) }}
           @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror>
    @error($name)
        <p id="{{ $name }}-error" class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
    @enderror
</div>
