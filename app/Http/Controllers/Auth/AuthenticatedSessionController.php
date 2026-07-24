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
	public function create(): View
	{
		return view('auth.login');
	}

	public function store(LoginRequest $request): RedirectResponse
	{
		$request->authenticate();
		$request->session()->regenerate();

		// ← Redirección según rol
		return redirect()->route(auth()->user()->dashboardRoute());
	}

	public function destroy(Request $request): RedirectResponse
	{
		Auth::guard('web')->logout();

		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return redirect('/');
	}

	// Logout simplificado que funciona incluso sin sesión activa
	public function destroySimple(): RedirectResponse
	{
		try {
			Auth::guard('web')->logout();
			session()->invalidate();
			session()->regenerateToken();
		} catch (\Exception $e) {
			// Si hay error (sin sesión, etc), simplemente ignorar y redirigir
		}

		return redirect('/');
	}
}