<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Реєстрація: пароль хешується кастом 'hashed' у моделі User, роль за замовчуванням — user (з БД).
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('cabinet'))->with('success', 'Вітаємо в APEX TRACK! Обліковий запис створено.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Невірна електронна пошта або пароль.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $home = $request->user()->isAdmin() ? route('admin.dashboard') : route('cabinet');

        return redirect()->intended($home)->with('success', 'Ви увійшли як '.$request->user()->name.'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Ви вийшли з системи.');
    }
}
