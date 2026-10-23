<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProfileRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    use ProfileRules;

    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProfileInput();
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        $creating = $this->route('member') === null;

        return [
            ...$this->profileRules(),
            'email' => [
                'required', 'email', 'max:100',
                Rule::unique('users', 'email')->whereNull('deleted_at')->ignore($this->route('member')),
            ],
            // Wajib saat tambah; saat ubah dikosongkan = password tidak diganti.
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:'.config('gudang.password_min')],
        ];
    }

    public function messages(): array
    {
        return [
            ...$this->profileMessages(),
            'email.unique' => 'Email sudah terdaftar.', // MSG-04
            'password.min' => 'Password minimal :min karakter.', // MSG-06
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama lengkap'];
    }
}
