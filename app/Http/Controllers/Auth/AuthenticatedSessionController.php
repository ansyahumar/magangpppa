<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
public function store(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (!Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    $request->session()->regenerate();

$role = Auth::user()->role;

return match ($role) {
    'admin'       => redirect()->route('admin.dashboard'),
    'verifikator' => redirect()->route('verifikator.verifikasi'), 
    'p1'          => redirect()->route('p1.chart'),             
    'p2'          => redirect()->route('p2.dashboard'),             
    'user'        => redirect()->route('dashboard'),
    'kordinator'  => redirect()->route('kordinator.chart'),
    default       => redirect('/dashboard'),
};
}



    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
protected function authenticated($request, $user)
{
    switch ($user->role) {
        case 'admin':
            return redirect()->route('admin.dashboard');

        case 'verifikator':
            return redirect()->route('verifikator.dashboard');

        case 'p2':
            return redirect()->route('p2.dashboard');

        default:
            return redirect('/login');
    }
}
}
