<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordRequest extends FormRequest
{
    // Pakai error bag terpisah supaya error form password tidak tercampur dengan form profil.
    protected $errorBag = 'password';

    public function authorize(): bool
    {
        return true;
    }

    // OQ-03: tidak perlu password lama, cukup password baru + konfirmasi.
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:'.config('gudang.password_min'), 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min' => 'Password minimal :min karakter.', // MSG-06
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }

    public function attributes(): array
    {
        return ['password' => 'password baru'];
    }
}
