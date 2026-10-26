<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Objek SQL di sisi database (PostgreSQL saja):
 * - hari_ini_wib()        : tanggal hari ini di Asia/Jakarta (server Supabase memakai UTC).
 * - hitung_denda()        : rumus denda yang sama dengan Loan::hitungDenda().
 * - v_pinjaman_aktif      : daftar pinjaman aktif beserta anggota, unit, dan hari terlambat.
 * - trg_loans_sinkron_status_unit : status unit ikut berubah saat pinjaman dibuat/selesai,
 *   sebagai pengaman kedua selain LoanService (hasilnya sama, jadi tidak bentrok).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION hari_ini_wib()
            RETURNS date
            LANGUAGE sql
            STABLE
            SET search_path = public
            AS $$
                SELECT (now() AT TIME ZONE 'Asia/Jakarta')::date;
            $$;

            -- Tarif dikirim dari Laravel (config gudang.denda_per_hari), tidak di-hardcode di database.
            CREATE OR REPLACE FUNCTION hitung_denda(p_due_date date, p_per date, p_tarif integer)
            RETURNS integer
            LANGUAGE sql
            IMMUTABLE
            SET search_path = public
            AS $$
                SELECT GREATEST(p_per - p_due_date, 0) * p_tarif;
            $$;

            -- security_invoker: view mengikuti hak akses pemanggil, jadi RLS tabel tetap berlaku.
            CREATE OR REPLACE VIEW v_pinjaman_aktif WITH (security_invoker = true) AS
            SELECT
                l.id                                         AS loan_id,
                l.user_id,
                u.name                                       AS nama_anggota,
                u.email,
                l.unit_id,
                un.unit_code,
                un.name                                      AS nama_unit,
                l.loan_date,
                l.due_date,
                GREATEST(hari_ini_wib() - l.due_date, 0)     AS hari_terlambat,
                l.due_date < hari_ini_wib()                  AS terlambat
            FROM loans l
            JOIN users u  ON u.id = l.user_id
            JOIN units un ON un.id = l.unit_id
            WHERE l.status = 'aktif';

            CREATE OR REPLACE FUNCTION sinkron_status_unit()
            RETURNS trigger
            LANGUAGE plpgsql
            SET search_path = public
            AS $$
            BEGIN
                IF TG_OP = 'INSERT' AND NEW.status = 'aktif' THEN
                    UPDATE units SET status = 'dipinjam', updated_at = now() WHERE id = NEW.unit_id;
                ELSIF TG_OP = 'UPDATE' AND OLD.status = 'aktif' AND NEW.status = 'selesai' THEN
                    UPDATE units SET status = 'tersedia', updated_at = now() WHERE id = NEW.unit_id;
                END IF;

                RETURN NULL;
            END;
            $$;

            CREATE TRIGGER trg_loans_sinkron_status_unit
            AFTER INSERT OR UPDATE OF status ON loans
            FOR EACH ROW
            EXECUTE FUNCTION sinkron_status_unit();

            -- Tutup akses lewat Data API Supabase, sama seperti tabel-tabelnya (migration RLS).
            REVOKE EXECUTE ON FUNCTION hari_ini_wib(), hitung_denda(date, date, integer), sinkron_status_unit() FROM PUBLIC;

            DO $$
            BEGIN
                IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'anon') THEN
                    REVOKE ALL ON v_pinjaman_aktif FROM anon, authenticated;
                    REVOKE EXECUTE ON FUNCTION hari_ini_wib(), hitung_denda(date, date, integer), sinkron_status_unit() FROM anon, authenticated;
                END IF;
            END;
            $$;
            SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS trg_loans_sinkron_status_unit ON loans;
            DROP FUNCTION IF EXISTS sinkron_status_unit();
            DROP VIEW IF EXISTS v_pinjaman_aktif;
            DROP FUNCTION IF EXISTS hitung_denda(date, date, integer);
            DROP FUNCTION IF EXISTS hari_ini_wib();
            SQL);
    }
};
