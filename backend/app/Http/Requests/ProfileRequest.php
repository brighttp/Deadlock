<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ProfileRules;
use Illuminate\Foundation\Http\FormRequest;

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
        return $this->profileRules();
    }

    public function messages(): array
    {
        return $this->profileMessages();
    }

    public function attributes(): array
    {
        return ['name' => 'nama lengkap'];
    }
}
