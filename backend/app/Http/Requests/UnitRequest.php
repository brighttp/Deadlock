<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'unit_code' => mb_strtoupper(trim((string) $this->input('unit_code'))),
            'name' => trim((string) $this->input('name')),
        ]);
    }

    public function rules(): array
    {
        return [
            // Unik termasuk unit yang sudah dihapus: kode unit tidak didaur ulang.
            'unit_code' => [
                'required', 'string', 'max:20',
                Rule::unique('units', 'unit_code')->ignore($this->route('unit')),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_code.unique' => 'Kode unit sudah digunakan.', // MSG-05
            'categories.required' => 'Pilih minimal satu kategori.',
            'categories.min' => 'Pilih minimal satu kategori.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama unit'];
    }
}
