<?php

// Pesan mengikuti katalog MSG-xx (PRD Bab 10). Pesan khusus per field ada di Form Request.
return [
    'required' => 'Kolom :attribute wajib diisi.',                  // MSG-02
    'required_with' => 'Kolom :attribute wajib diisi.',
    'email' => 'Format email tidak valid.',                         // MSG-03
    'unique' => ':Attribute sudah digunakan.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'string' => 'Kolom :attribute harus berupa teks.',
    'max' => [
        'string' => 'Kolom :attribute maksimal :max karakter.',
        'file' => 'Ukuran :attribute maksimal :max KB.',
    ],
    'min' => [
        'string' => 'Kolom :attribute minimal :min karakter.',
        'array' => 'Pilih minimal :min :attribute.',
    ],
    'regex' => 'Format :attribute tidak valid.',
    'array' => 'Kolom :attribute tidak valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'image' => ':Attribute harus berupa gambar.',
    'mimes' => ':Attribute harus berformat :values.',
    'uploaded' => ':Attribute gagal diunggah.',
    'date' => 'Kolom :attribute bukan tanggal yang valid.',
    'after_or_equal' => ':Attribute harus sama atau setelah :date.',
    'integer' => 'Kolom :attribute harus berupa angka.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'distinct' => ':Attribute tidak boleh duplikat.',

    'attributes' => [
        'name' => 'nama',
        'email' => 'email',
        'password' => 'password',
        'phone' => 'nomor HP',
        'address' => 'alamat/basecamp',
        'photo' => 'foto',
        'unit_code' => 'kode unit',
        'description' => 'deskripsi',
        'categories' => 'kategori',
        'categories.*' => 'kategori',
        'user_id' => 'anggota',
        'dari' => 'tanggal dari',
        'sampai' => 'tanggal sampai',
    ],
];
