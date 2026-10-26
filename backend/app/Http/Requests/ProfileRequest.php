<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProfileRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
    use ProfileRules;

    public function authorize(): bool
    {
        return true; // selalu profil milik user yang login
    }

    protected function prepareForValidation(): void
    {
        $this->prepareProfileInput();
    }

    public function rules(): array
    {
        return [
            ...$this->profileRules(),
            'currency' => ['required', Rule::in(array_keys(config('gudang.mata_uang.daftar')))],
        ];
    }

    public function messages(): array
    {
        return [
            ...$this->profileMessages(),
            'currency.in' => 'Mata uang tidak dikenal.', // MSG-22
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama lengkap'];
    }
}
