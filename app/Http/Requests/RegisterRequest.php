<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Поле role відсутнє навмисно: роль нового користувача завжди user.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            // Пароль: ≥ 8 символів, великі й малі літери, цифра, без пробілів
            'password' => ['required', 'confirmed', 'regex:/^\S+$/u', Password::min(8)->letters()->mixedCase()->numbers()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['password.regex' => 'Пароль не може містити пробілів.'];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['name' => 'ім\'я', 'email' => 'електронна пошта', 'password' => 'пароль'];
    }
}
