<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Membuat/mengubah user beserta profilnya dalam satu transaksi,
 * sehingga setiap user dijamin punya tepat satu profil.
 */
class ProfileService
{
    public function create(array $data, ?UploadedFile $photo): User
    {
        $path = $photo?->store('profiles', 'public');

        try {
            return DB::transaction(function () use ($data, $path) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'role' => User::ROLE_ANGGOTA, // role selalu anggota, tidak dari input
                ]);

                $user->profile()->create([
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'photo' => $path,
                ]);

                return $user;
            });
        } catch (\Throwable $e) {
            $path && Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    public function update(User $user, array $data, ?UploadedFile $photo): User
    {
        $oldPath = $user->profile?->photo;
        $newPath = $photo?->store('profiles', 'public');

        try {
            DB::transaction(function () use ($user, $data, $newPath) {
                $user->name = $data['name'];
                if (array_key_exists('email', $data)) {
                    $user->email = $data['email'];
                }
                // Mata uang hanya diubah lewat halaman Profil; form Admin tidak mengirim field ini.
                if (array_key_exists('currency', $data)) {
                    $user->currency = $data['currency'];
                }
                if (! empty($data['password'])) {
                    $user->password = $data['password'];
                }
                $user->save();

                $profile = ['phone' => $data['phone'] ?? null, 'address' => $data['address'] ?? null];
                if ($newPath) {
                    $profile['photo'] = $newPath;
                }

                // updateOrCreate tanpa kunci tambahan: user_id unique, jadi tidak mungkin profil kedua.
                $user->profile()->updateOrCreate([], $profile);
            });
        } catch (\Throwable $e) {
            $newPath && Storage::disk('public')->delete($newPath);
            throw $e;
        }

        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $user;
    }
}
