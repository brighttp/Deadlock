<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request, ProfileService $service): RedirectResponse
    {
        // validated() hanya berisi field yang divalidasi, jadi input "role" tidak pernah ikut.
        // ProfileService juga selalu membuat akun ber-role anggota.
        $service->create($request->validated(), null);

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil. Silakan masuk.') // MSG-21
            ->withInput($request->only('email'));
    }
}
