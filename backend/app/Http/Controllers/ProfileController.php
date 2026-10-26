<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordRequest;
use App\Http\Requests\ProfileRequest;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Selalu bekerja pada user yang login; tidak ada parameter id, jadi tidak bisa mengubah profil orang lain.
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profil', ['user' => $request->user()->load('profile')]);
    }

    public function update(ProfileRequest $request, ProfileService $service): RedirectResponse
    {
        $service->update($request->user(), $request->validated());

        return back()->with('success', 'Profil berhasil diperbarui.'); // MSG-17
    }

    public function updatePassword(PasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return back()->with('success', 'Password berhasil diganti.'); // MSG-18
    }
}
