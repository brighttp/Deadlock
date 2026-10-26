<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberRequest;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));

        $members = User::anggota()
            ->with('profile')
            ->withCount('activeLoans')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->whereLike('name', "%{$q}%")
                ->orWhereLike('email', "%{$q}%")))
            ->orderBy('name')
            ->paginate(config('gudang.per_halaman'))
            ->withQueryString();

        return view('admin.anggota.index', compact('members', 'q'));
    }

    public function create(): View
    {
        return view('admin.anggota.form', ['member' => new User]);
    }

    public function store(MemberRequest $request, ProfileService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()->route('anggota.index')->with('success', 'Data berhasil disimpan.'); // MSG-19
    }

    public function edit(User $member): View
    {
        $this->pastikanAnggota($member);

        return view('admin.anggota.form', ['member' => $member->load('profile')]);
    }

    public function update(MemberRequest $request, User $member, ProfileService $service): RedirectResponse
    {
        $this->pastikanAnggota($member);

        $service->update($member, $request->validated());

        return redirect()->route('anggota.index')->with('success', 'Data berhasil disimpan.');
    }

    public function destroy(User $member): RedirectResponse
    {
        $this->pastikanAnggota($member);

        if ($member->activeLoans()->exists()) {
            return back()->with('error', 'Anggota masih memiliki pinjaman aktif dan tidak dapat dihapus.'); // MSG-11
        }

        $member->delete(); // soft delete: riwayat pinjaman tetap utuh, email bisa dipakai lagi

        return redirect()->route('anggota.index')->with('success', 'Data berhasil dihapus.');
    }

    // Halaman Anggota hanya untuk akun ber-role anggota, bukan akun Admin.
    private function pastikanAnggota(User $member): void
    {
        abort_unless($member->isAnggota(), 404);
    }
}
