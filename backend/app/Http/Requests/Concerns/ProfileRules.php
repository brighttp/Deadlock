<?php

namespace App\Http\Requests\Concerns;

/**
 * Aturan field profil yang dipakai bersama oleh form Anggota (Admin) dan form Profil (user sendiri).
 */
trait ProfileRules
{
    protected function profileRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:'.config('gudang.regex_hp')],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => [
                'nullable', 'image',
                'mimes:'.implode(',', config('gudang.foto_mimes')),
                'max:'.config('gudang.foto_maks_kb'),
            ],
        ];
    }

    protected function profileMessages(): array
    {
        return [
            'phone.regex' => 'Format nomor HP tidak valid.', // MSG-07
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
            'photo.mimes' => 'Foto harus berformat jpg, jpeg, png, atau webp.',
        ];
    }

    protected function prepareProfileInput(): void
    {
        // Spasi/strip di nomor HP dibuang supaya "0812-3344-5566" tetap valid.
        $phone = preg_replace('/[\s-]/', '', (string) $this->input('phone'));

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'phone' => $phone === '' ? null : $phone,
        ]);
    }
}
