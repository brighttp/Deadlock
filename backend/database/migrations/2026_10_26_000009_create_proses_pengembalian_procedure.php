<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stored procedure proses_pengembalian (PostgreSQL saja).
 * Input : id pinjaman, tanggal kembali, tarif denda per hari.
 * Output: p_denda (INOUT), denda yang tersimpan di baris pinjaman.
 * Status unit dikembalikan ke 'tersedia' oleh trigger trg_loans_sinkron_status_unit.
 * Pemanggil yang mengatur COMMIT/ROLLBACK (procedure ini tidak commit sendiri).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE PROCEDURE proses_pengembalian(
                IN    p_loan_id bigint,
                IN    p_tanggal date,
                IN    p_tarif   integer,
                INOUT p_denda   integer DEFAULT NULL
            )
            LANGUAGE plpgsql
            SET search_path = public
            AS $$
            DECLARE
                v_loan loans%ROWTYPE;
            BEGIN
                IF p_tarif IS NULL OR p_tarif < 0 THEN
                    RAISE EXCEPTION 'Tarif denda tidak valid.' USING ERRCODE = '22023';
                END IF;

                SELECT * INTO v_loan FROM loans WHERE id = p_loan_id FOR UPDATE;

                IF NOT FOUND THEN
                    RAISE EXCEPTION 'Pinjaman tidak ditemukan.' USING ERRCODE = 'P0002';
                END IF;

                IF v_loan.status <> 'aktif' THEN
                    RAISE EXCEPTION 'Pinjaman ini sudah dikembalikan.' USING ERRCODE = 'P0001';
                END IF;

                p_denda := hitung_denda(v_loan.due_date, p_tanggal, p_tarif);

                UPDATE loans
                SET status = 'selesai', return_date = p_tanggal, fine_amount = p_denda, updated_at = now()
                WHERE id = p_loan_id;
            END;
            $$;

            REVOKE EXECUTE ON PROCEDURE proses_pengembalian(bigint, date, integer, integer) FROM PUBLIC;

            DO $$
            BEGIN
                IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'anon') THEN
                    REVOKE EXECUTE ON PROCEDURE proses_pengembalian(bigint, date, integer, integer) FROM anon, authenticated;
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

        DB::unprepared('DROP PROCEDURE IF EXISTS proses_pengembalian(bigint, date, integer, integer);');
    }
};
