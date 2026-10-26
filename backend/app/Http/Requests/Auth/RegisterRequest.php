<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Concerns\ProfileRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    use ProfileRules;

    public function authorize(): bool
    {
        return true; // dibatasi middleware guest
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProfileInput();
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            // Foto tidak diminta saat daftar; bisa ditambahkan nanti di halaman Profil.
            ...Arr::except($this->profileRules(), 'photo'),
            'email' => [
                'required', 'email', 'max:100',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            // Berbeda dengan form Admin: pendaftar wajib mengetik ulang password untuk mencegah salah ketik.
            'password' => ['required', 'string', 'min:'.config('gudang.password_min'), 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            ...$this->profileMessages(),
            'email.unique' => 'Email sudah terdaftar.', // MSG-04
            'password.min' => 'Password minimal :min karakter.', // MSG-06
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama lengkap'];
    }
}
