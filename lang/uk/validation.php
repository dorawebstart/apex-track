<?php

/*
 * Українські повідомлення валідації для правил, що використовуються в APEX TRACK.
 */
return [
    'after' => 'Поле «:attribute» має бути датою пізніше за :date.',
    'after_or_equal' => 'Поле «:attribute» має бути датою не раніше за :date.',
    'between' => [
        'numeric' => 'Поле «:attribute» має бути від :min до :max.',
        'string' => 'Поле «:attribute» має містити від :min до :max символів.',
    ],
    'boolean' => 'Поле «:attribute» має бути так або ні.',
    'confirmed' => 'Підтвердження поля «:attribute» не збігається.',
    'date' => 'Поле «:attribute» має бути коректною датою.',
    'email' => 'Поле «:attribute» має бути коректною електронною адресою.',
    'exists' => 'Обране значення поля «:attribute» не існує.',
    'image' => 'Поле «:attribute» має бути зображенням.',
    'in' => 'Обране значення поля «:attribute» некоректне.',
    'integer' => 'Поле «:attribute» має бути цілим числом.',
    'max' => [
        'file' => 'Файл «:attribute» не може бути більшим за :max КБ.',
        'numeric' => 'Поле «:attribute» не може бути більшим за :max.',
        'string' => 'Поле «:attribute» не може містити більше :max символів.',
    ],
    'mimes' => 'Файл «:attribute» має бути типу: :values.',
    'min' => [
        'numeric' => 'Поле «:attribute» має бути не менше :min.',
        'string' => 'Поле «:attribute» має містити щонайменше :min символів.',
    ],
    'numeric' => 'Поле «:attribute» має бути числом.',
    'required' => 'Поле «:attribute» обов\'язкове.',
    'string' => 'Поле «:attribute» має бути текстом.',
    'unique' => 'Таке значення поля «:attribute» вже зайняте.',
    'uploaded' => 'Не вдалося завантажити файл «:attribute».',
    'password' => [
        'letters' => 'Поле «:attribute» має містити хоча б одну літеру.',
        'mixed' => 'Поле «:attribute» має містити великі та малі літери.',
        'numbers' => 'Поле «:attribute» має містити хоча б одну цифру.',
        'symbols' => 'Поле «:attribute» має містити хоча б один символ.',
        'uncompromised' => 'Цей пароль з\'являвся у витоках даних — оберіть інший.',
    ],

    'attributes' => [
        'name' => 'ім\'я',
        'email' => 'електронна пошта',
        'password' => 'пароль',
        'title' => 'назва',
        'description' => 'опис',
        'category_id' => 'клас авто',
        'car_model' => 'модель авто',
        'brand' => 'марка',
        'track_name' => 'автодром',
        'horsepower' => 'потужність',
        'top_speed_kmh' => 'максимальна швидкість',
        'zero_to_100' => 'розгін 0–100',
        'starts_at' => 'дата початку',
        'ends_at' => 'дата завершення',
        'capacity' => 'кількість місць',
        'image' => 'зображення',
        'status' => 'статус',
        'from' => 'дата від',
        'to' => 'дата до',
        'q' => 'пошук',
    ],
];
