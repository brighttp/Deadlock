<?php

return [

    /*
    | Aturan bisnis gudang (ketentuan dosen + keputusan OQ di PRD Bab 16).
    | Tarif denda diambil dari .env supaya tidak di-hardcode di controller.
    */

    'denda_per_hari' => (int) env('DENDA_PER_HARI', 15000),  // OQ-01

    'lama_pinjam_hari' => 5,

    'maks_pinjaman_aktif' => 2,

    'password_min' => 8,                                      // OQ-02

    'regex_hp' => '/^08[0-9]{8,11}$/',                        // OQ-02: diawali 08, total 10-13 digit

    'foto_maks_kb' => 2048,                                   // OQ-06

    'foto_mimes' => ['jpg', 'jpeg', 'png', 'webp'],           // OQ-06

    'per_halaman' => 10,                                      // OQ-07

];
