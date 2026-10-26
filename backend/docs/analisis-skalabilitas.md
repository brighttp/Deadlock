# Analisis Skalabilitas — Sistem Gudang Deadlock

Dokumen ini memenuhi unit kompetensi **Menganalisis Skalabilitas Perangkat Lunak**.
Angka yang ditandai **(terukur)** diambil dari server Supabase proyek ini, stress test
(`tests/Feature/StressTest.php`), dan perintah `php artisan gudang:pantau` pada 26 Oktober 2026.
Angka yang ditandai **(asumsi)** adalah perkiraan tim dan perlu disesuaikan bila kebutuhan berubah.

## 1. Lingkup sistem

| Aspek | Isi |
|---|---|
| Fungsi | Peminjaman unit senjata/perlengkapan: katalog, pinjam, kembali + denda, master kategori/unit/anggota, riwayat, cetak & PDF |
| Pengguna | 2 peran: Admin (Komandan Logistik) dan Anggota (Survivor). Tidak ada registrasi publik |
| Aturan yang memengaruhi beban | Maks. 2 pinjaman aktif per anggota, lama pinjam 5 hari, paginasi 10 baris |
| Di luar lingkup | API publik, aplikasi mobile, multi-gudang |

Perkiraan volume **(asumsi)**: 1 gudang, ≤ 100 anggota, ≤ 500 unit, ≤ 30 transaksi pinjam/kembali per hari,
≤ 10 pengguna aktif bersamaan pada jam sibuk.

## 2. Lingkungan operasi

| Komponen | Spesifikasi |
|---|---|
| Aplikasi | Laravel 13, PHP 8.4, Blade (render di server), session & cache berbasis file |
| Database | Supabase PostgreSQL 17.11, region Tokyo (`ap-northeast-1`), lewat **Session pooler** port 5432 |
| Server database **(terukur)** | `max_connections` = 60, `shared_buffers` = 224 MB, `work_mem` ≈ 2 MB (paket Free / Nano) |
| Kuota database | 500 MB (paket Free), dipakai 11 MB **(terukur)** (sebagian besar skema internal Supabase) |
| Penyimpanan foto | Disk lokal server (`storage/app/public/profiles`), maks. 2 MB per foto |
| Jaringan **(terukur)** | Latensi satu query `SELECT 1` dari laptop pengembang (Indonesia) ke Tokyo ≈ 290 ms |

## 3. Analisis masalah skalabilitas

### 3.1 Pertumbuhan data — bukan masalah
Stress test mengisi 10.000 riwayat pinjaman: tabel `loans` + index = **1.664 KB (terukur)**, sekitar 170 byte per baris.
Dengan 30 transaksi/hari **(asumsi)** ≈ 11.000 baris/tahun ≈ **1,8 MB/tahun**. Kuota 500 MB tidak akan jadi batas
dalam masa pakai aplikasi. Unit dan anggota jauh lebih kecil lagi (1.000 unit = 360 KB).

### 3.2 Latensi jaringan ke database — **hambatan utama**
Setiap halaman menjalankan 1–9 query secara berurutan **(terukur, kolom Query/request)**. Bila server aplikasi
berada di Indonesia dan database di Tokyo, waktu jaringan saja ≈ jumlah query × 290 ms:

| Halaman | Query | Perkiraan waktu jaringan |
|---|---:|---:|
| Daftar pinjaman aktif | 1 | ± 0,3 detik |
| Daftar anggota | 3 | ± 0,9 detik |
| Katalog, riwayat | 5 | ± 1,5 detik |
| Dashboard admin | 6 | ± 1,7 detik |
| Pinjam + kembali | 9 | ± 2,6 detik |

Di database lokal (latensi ~0 ms) halaman yang sama hanya 9–75 ms. Jadi yang lambat adalah **jarak**, bukan kode.

### 3.3 Batas koneksi database
Mode Session pooler memegang satu koneksi server selama satu request PHP. Saat ini sudah ada **14–15 koneksi aktif (terukur)**
dari total 60, sebagian dipakai layanan internal Supabase. Bila aplikasi dijalankan dengan banyak worker PHP-FPM,
jumlah worker × instance tidak boleh mendekati sisa koneksi (± 45). Halaman **Monitoring Sistem** menampilkan
pemakaian ini terhadap batas atasnya.

### 3.4 Kompleksitas pemrosesan
- **Katalog/riwayat/anggota/pinjaman**: dipaginasi 10 baris dan memakai index (`units.name`, `loans.loan_date`,
  `loans(user_id,status)`, `loans(unit_id,status)`), jadi waktunya hampir konstan walau data bertambah (≈ O(log n) per pencarian index).
- **Pencarian nama** memakai `ILIKE '%kata%'` yang tidak bisa memakai index B-tree, jadi O(n). Pada 1.000 unit masih 57 ms (terukur);
  baru perlu index trigram (`pg_trgm`) bila unit mencapai puluhan ribu.
- **Pinjam/kembali**: memakai `SELECT … FOR UPDATE` + partial unique index, sehingga aman dari race condition. Biayanya 9 query,
  tidak tergantung jumlah data.
- **Cetak/PDF riwayat tanpa filter**: memuat semua baris sekaligus, O(n) dalam waktu **dan memori**. Ini satu-satunya
  skenario yang **tidak lulus** stress test: 10.000 baris = **4,3 detik (terukur)**. PDF (dompdf) lebih berat lagi.

## 4. Kebutuhan perangkat keras

| Komponen | Minimum (≤ 10 pengguna bersamaan) | Catatan |
|---|---|---|
| Server aplikasi | 1 vCPU, 1 GB RAM, 5 GB disk | Request normal memakai ± 26 MB memori PHP (terukur); stress test dengan 10.000 baris memakai puncak 92 MB. 5 worker PHP-FPM × ± 40 MB ≈ 200 MB |
| Disk foto | ± 200 MB | 100 anggota × maks. 2 MB |
| Database | Paket Supabase Free | Kuota, RAM, dan koneksi masih jauh dari batas pada volume asumsi |
| Lokasi | **Satu region dengan database** | Lihat 3.2 |

## 5. Rekomendasi (urut prioritas)

1. **Letakkan server aplikasi di region yang sama dengan database** (Tokyo), atau pindahkan proyek Supabase ke region
   Singapura (`ap-southeast-1`) yang lebih dekat ke pengguna. Dampaknya paling besar: waktu halaman turun dari detik ke puluhan milidetik.
2. **Batasi cetak/PDF riwayat** — wajibkan rentang tanggal, atau batasi maksimal (misalnya 1.000 baris) dengan pesan
   agar admin mempersempit filter. Perlu keputusan tim karena mengubah perilaku fitur.
3. Bila worker PHP > 30, pindah ke **Transaction pooler** (port 6543) supaya koneksi server dipakai bergantian per transaksi.
4. Tambahkan index `pg_trgm` untuk pencarian nama hanya jika jumlah unit/anggota mencapai puluhan ribu.
5. Pantau rutin lewat `gudang:pantau` (tiap 5 menit) dan halaman Monitoring. Alert otomatis muncul bila resource mencapai 90% batas atas.

## 6. Cara mengulang pengukuran

```bash
docker compose -f docker-compose.test.yml up -d
php artisan test --configuration=phpunit.pgsql.xml --group=stress   # laporan di storage/app/stress/
php artisan gudang:pantau                                            # kondisi resource di server sebenarnya
```
