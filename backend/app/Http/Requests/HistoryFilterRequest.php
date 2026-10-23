<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HistoryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    public function rules(): array
    {
        return [
            // Anggota yang sudah dihapus tetap bisa dipilih agar riwayatnya bisa dicetak.
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_ANGGOTA)],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ];
    }

    public function attributes(): array
    {
        return ['user_id' => 'anggota', 'dari' => 'tanggal dari', 'sampai' => 'tanggal sampai'];
    }
}
