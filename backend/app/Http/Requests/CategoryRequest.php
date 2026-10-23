<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
                // "medis" dianggap sama dengan "Medis".
                function (string $attribute, string $value, \Closure $fail) {
                    $duplikat = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($value)])
                        ->when($this->route('category'), fn ($q, $category) => $q->whereKeyNot($category->id))
                        ->exists();

                    if ($duplikat) {
                        $fail('Nama kategori sudah digunakan.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama kategori sudah digunakan.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama kategori'];
    }
}
