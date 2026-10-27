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

    /*
    | Pemantauan resource (halaman Monitoring + perintah gudang:pantau).
    | "batas" = batas atas sebelum aplikasi gagal. Status peringatan/kritis
    | ditentukan dari persentase terhadap batas tersebut.
    */
    'monitoring' => [
        'peringatan_persen' => 70,
        'kritis_persen' => 90,
        'riwayat_maks_sampel' => 288,                         // 24 jam jika dipantau tiap 5 menit
        'batas' => [
            'db_latensi_ms' => 1000,
            'db_ukuran_mb' => (int) env('MONITOR_DB_MAKS_MB', 500),           // kuota database Supabase Free
            'db_koneksi' => (int) env('MONITOR_DB_MAKS_KONEKSI', 60),         // max_connections Supabase Free (Nano)
            'foto_mb' => (int) env('MONITOR_FOTO_MAKS_MB', 1024),
        ],
    ],

    /*
    | Alert saat aplikasi bermasalah.
    | Alert yang sama dikirim ulang tiap "ulang_menit" selama masalah belum tertangani.
    */
    'alert' => [
        'email' => env('ALERT_EMAIL'),                        // kosong = hanya dicatat di storage/logs/alert-*.log
        'ulang_menit' => (int) env('ALERT_ULANG_MENIT', 30),
    ],

    /*
    | Mata uang tampilan (revisi asesor). Data tetap disimpan dalam Rupiah;
    | konversi hanya saat ditampilkan, memakai kurs harian dari API Frankfurter v2
    | (gabungan bank sentral dan sumber resmi; v1 sudah deprecated sejak Mei 2026).
    */
    'mata_uang' => [
        'default' => 'IDR',
        'api_url' => env('KURS_API_URL', 'https://api.frankfurter.dev/v2/rates'),
        'cache_menit' => (int) env('KURS_CACHE_MENIT', 60),
        'timeout_detik' => 3,
        'daftar' => [
            'IDR' => ['nama' => 'Rupiah', 'simbol' => 'Rp', 'desimal' => 0],
            'USD' => ['nama' => 'Dolar AS', 'simbol' => '$', 'desimal' => 2],
            'EUR' => ['nama' => 'Euro', 'simbol' => '€', 'desimal' => 2],
            'SGD' => ['nama' => 'Dolar Singapura', 'simbol' => 'S$', 'desimal' => 2],
            'JPY' => ['nama' => 'Yen Jepang', 'simbol' => '¥', 'desimal' => 0],
        ],
    ],

];
