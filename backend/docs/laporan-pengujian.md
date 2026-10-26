# Laporan Pengujian Unit Program — Sistem Gudang Deadlock

Dokumen ini memenuhi unit kompetensi **Melaksanakan Pengujian Unit Program**.
Tanggal pelaksanaan: 26 Oktober 2026.

## 1. Peralatan dan lingkungan pengujian

| Peralatan | Versi / keterangan |
|---|---|
| Framework uji | PHPUnit 12.5 (via `php artisan test`) |
| Bahasa | PHP 8.4.6 (Windows 11) |
| Mock / stub | Mockery 1.6 (`$this->mock(...)`), `Storage::fake`, `Notification::fake`, probe tiruan (anonymous class) |
| Database uji 1 | SQLite in-memory — cepat, tanpa instalasi (`phpunit.xml`) |
| Database uji 2 | PostgreSQL 17 di Docker, sama dengan versi Supabase (`phpunit.pgsql.xml`, `docker-compose.test.yml`) |
| Pengaman | `tests/TestCase.php` menolak menjalankan test bila koneksi mengarah ke host Supabase |
| Gaya kode | Laravel Pint |

Data uji dibuat ulang setiap test (`RefreshDatabase`), sehingga hasil tidak bergantung pada urutan test.

## 2. Tim pengujian

| Nama | Peran |
|---|---|
| Farras Ikhsanul Rafi | Menyusun dan menjalankan unit test & stress test backend |
| Raafi Naufal Fadhillah | *(isi sesuai pembagian tim, misalnya review hasil / UAT)* |
| Bright Eliezer Pinaria | *(isi sesuai pembagian tim)* |

## 3. Program driver dan stub

- **Driver**: test Feature memanggil route seperti pengguna sungguhan (`$this->actingAs($anggota)->post(route('pinjam', $unit))`)
  lalu memeriksa respons, pesan MSG, dan isi database.
- **Stub `LoanService`** (`PinjamControllerTest::test_pesan_dari_service_diteruskan_ke_flash_error`): service diganti stub
  yang selalu menolak, sehingga controller diuji terpisah dari logika bisnis.
- **Stub probe resource** (`MonitoringAlertTest::pakaiProbe`): probe tiruan mengembalikan nilai kritis/normal yang ditentukan,
  untuk menguji alur alert → pulih tanpa harus benar-benar membuat server kehabisan resource.
- **Simulasi database mati**: koneksi diarahkan ke `127.0.0.1:1` untuk menguji graceful failure.

## 4. Hasil pengujian unit & fitur

| Suite | SQLite | PostgreSQL |
|---|---|---|
| Total test | 57 (50 lulus, 7 dilewati*) | **57 lulus**, 176 assertion |

\* 7 test objek database (function, view, trigger, stored procedure) hanya bisa dijalankan di PostgreSQL.

| File test | Yang diuji | Hasil |
|---|---|---|
| `Unit/LoanDendaTest` | Rumus denda 5 kasus (sebelum/tepat/terlambat/lintas bulan), tarif dari config, status Terlambat/Aktif/Selesai, format rupiah | Lulus |
| `Unit/MetricTest` | Status normal/peringatan/kritis/gagal dari persen terhadap batas atas | Lulus |
| `Feature/LoanServiceTest` | Pinjam, jatuh tempo +5 hari, MSG-08, MSG-09, denda, kembali dua kali, unique index | Lulus |
| `Feature/AksesTest` | Login, MSG-01, anggota terhapus tidak bisa login, 403 per role | Lulus |
| `Feature/PinjamControllerTest` | Driver route + stub service, MSG-15, MSG-16, data pinjaman milik sendiri | Lulus |
| `Feature/MasterDataTest` | Hapus kategori terpakai, MSG-11, MSG-04/06/07, role tidak bisa disisipkan | Lulus |
| `Feature/DatabaseObjectsTest` | `hitung_denda`, `hari_ini_wib`, trigger status unit, view `v_pinjaman_aktif`, procedure `proses_pengembalian`, commit & rollback | Lulus |
| `Feature/MonitoringAlertTest` | Frekuensi alert, kabar pulih, `gudang:pantau`, mode terbatas saat DB mati lalu pulih otomatis, akses halaman Monitoring | Lulus |

## 5. Stress test

Data yang diukur: waktu respons (rata-rata, p95, maksimum), jumlah query per request, memori PHP puncak, ukuran tabel.
Kriteria lulus: p95 < 1.000 ms dan tidak ada respons gagal.
Data: 1.000 unit, 300 anggota, 10.000 riwayat pinjaman. Database PostgreSQL 17 lokal (latensi jaringan ≈ 0).

| Skenario | Ulang | Rata-rata (ms) | p95 (ms) | Maks (ms) | Query/request | Gagal | Hasil |
|---|---:|---:|---:|---:|---:|---:|---|
| Dashboard admin | 30 | 68.6 | 193.4 | 231.5 | 6 | 0 | Lulus |
| Katalog (cari + filter) | 30 | 57.5 | 104.8 | 135.6 | 5 | 0 | Lulus |
| Daftar pinjaman aktif | 30 | 16.9 | 29.4 | 33 | 1 | 0 | Lulus |
| Riwayat (paginasi) | 30 | 74.7 | 127.3 | 136.7 | 5 | 0 | Lulus |
| Daftar anggota (cari) | 30 | 26.1 | 35.2 | 50.6 | 3 | 0 | Lulus |
| Cetak riwayat 1 bulan | 5 | 151.7 | 205.1 | 205.1 | 3 | 0 | Lulus |
| Cetak riwayat semua | 3 | 4067.8 | 4279.6 | 4279.6 | 3 | 0 | **Tidak lulus** |
| Siklus pinjam + kembali | 200 | 34.5 | 70.9 | 168.2 | 9 | 0 | Lulus |

Memori PHP puncak 92 MB. Ukuran tabel + index: users 144 KB, units 360 KB, loans 1.664 KB.

## 6. Analisis hasil

| Kondisi | Diharapkan | Hasil | Keterangan |
|---|---|---|---|
| Seluruh unit & fitur test | Semua lulus | 57/57 lulus di PostgreSQL | Sesuai |
| Rumus denda di PHP vs fungsi SQL `hitung_denda` | Hasil sama | Sama untuk semua kasus | Sesuai |
| Halaman berpaginasi saat data 10.000 baris | p95 < 1 detik | 29–193 ms | Sesuai; waktu hampir tidak bergantung jumlah data karena paginasi + index |
| Cetak riwayat **tanpa filter** | p95 < 1 detik | 4,3 detik | **Tidak sesuai.** Semua baris dirender sekaligus. Usulan perbaikan: wajibkan rentang tanggal atau batasi jumlah baris (menunggu keputusan tim) |
| Database tidak dapat dihubungi | Aplikasi tidak error 500 | Halaman mode terbatas (503) + katalog terakhir, pulih otomatis | Sesuai |

Catatan: stress test dijalankan pada database lokal. Di Supabase, setiap query menambah ± 290 ms latensi jaringan
dari Indonesia (lihat `docs/analisis-skalabilitas.md`).

## 7. Cara menjalankan ulang

```bash
php artisan test                                                     # SQLite, tanpa Docker
docker compose -f docker-compose.test.yml up -d
php artisan test --configuration=phpunit.pgsql.xml                   # PostgreSQL, termasuk objek SQL
php artisan test --configuration=phpunit.pgsql.xml --group=stress    # stress test, laporan di storage/app/stress/
```
