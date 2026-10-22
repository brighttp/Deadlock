import React, { useState } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { PrimaryButton, SecondaryButton } from '../../components/common/Buttons';
import { FilterSelect } from '../../components/common/FilterSelect';
import { StatusBadge } from '../../components/common/StatusBadge';
import { DataTable, type Column } from '../../components/common/DataTable';
import { StatCard } from '../../components/common/StatCard';
import type { Loan } from '../../types';
import {
  Printer,
  Filter,
  FileText,
  Coins,
  CheckCircle2,
  Sparkles,
  Calendar,
  RotateCcw,
  Boxes,
  ShieldCheck,
  Clock,
} from 'lucide-react';

export const LoanHistoryPage: React.FC = () => {
  const { loans, users, units } = useDeadlock();

  // Filters
  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [memberFilter, setMemberFilter] = useState('');
  const [activePreset, setActivePreset] = useState<'all' | '7days' | '30days' | 'thisMonth'>('all');

  // Applied filter state
  const [appliedFilters, setAppliedFilters] = useState<{
    startDate: string;
    endDate: string;
    memberId: string;
  }>({
    startDate: '',
    endDate: '',
    memberId: '',
  });

  const survivors = users.filter((u) => u.role === 'Survivor');

  // Calculate High Level Metrics
  const totalArchive = loans.length;
  const totalFines = loans.reduce((acc, curr) => acc + (curr.fineAmount || 0), 0);
  const onTimeLoans = loans.filter((l) => (l.fineAmount || 0) === 0);
  const disciplineRate = totalArchive > 0 ? Math.round((onTimeLoans.length / totalArchive) * 100) : 100;

  // Find most borrowed unit
  const unitFrequency = loans.reduce<{ [key: string]: number }>((acc, curr) => {
    acc[curr.unitName] = (acc[curr.unitName] || 0) + 1;
    return acc;
  }, {});

  const topBorrowedUnitName = Object.keys(unitFrequency).reduce((a, b) =>
    unitFrequency[a] > unitFrequency[b] ? a : b,
    'Belum ada data'
  );

  const handleApplyPreset = (preset: 'all' | '7days' | '30days' | 'thisMonth') => {
    setActivePreset(preset);
    const today = new Date();
    const formatDate = (d: Date) => d.toISOString().split('T')[0];

    if (preset === 'all') {
      setStartDate('');
      setEndDate('');
      setAppliedFilters((prev) => ({ ...prev, startDate: '', endDate: '' }));
      return;
    }

    if (preset === '7days') {
      const past = new Date();
      past.setDate(today.getDate() - 7);
      const start = formatDate(past);
      const end = formatDate(today);
      setStartDate(start);
      setEndDate(end);
      setAppliedFilters((prev) => ({ ...prev, startDate: start, endDate: end }));
      return;
    }

    if (preset === '30days') {
      const past = new Date();
      past.setDate(today.getDate() - 30);
      const start = formatDate(past);
      const end = formatDate(today);
      setStartDate(start);
      setEndDate(end);
      setAppliedFilters((prev) => ({ ...prev, startDate: start, endDate: end }));
      return;
    }

    if (preset === 'thisMonth') {
      const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
      const start = formatDate(firstDay);
      const end = formatDate(today);
      setStartDate(start);
      setEndDate(end);
      setAppliedFilters((prev) => ({ ...prev, startDate: start, endDate: end }));
      return;
    }
  };

  const handleApplyFilters = (e: React.FormEvent) => {
    e.preventDefault();
    setAppliedFilters({
      startDate,
      endDate,
      memberId: memberFilter,
    });
  };

  const handleResetFilters = () => {
    setStartDate('');
    setEndDate('');
    setMemberFilter('');
    setActivePreset('all');
    setAppliedFilters({
      startDate: '',
      endDate: '',
      memberId: '',
    });
  };

  const handlePrint = () => {
    window.print();
  };

  const filteredHistory = loans.filter((loan) => {
    if (appliedFilters.memberId && loan.survivorId !== appliedFilters.memberId) {
      return false;
    }

    if (appliedFilters.startDate && loan.loanDate < appliedFilters.startDate) {
      return false;
    }

    if (appliedFilters.endDate && loan.loanDate > appliedFilters.endDate) {
      return false;
    }

    return true;
  });

  const filteredFinesTotal = filteredHistory.reduce((acc, curr) => acc + (curr.fineAmount || 0), 0);

  const columns: Column<Loan>[] = [
    {
      key: 'survivorName',
      header: 'Survivor',
      render: (item) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
          <div
            className="user-avatar"
            style={{
              width: '34px',
              height: '34px',
              fontSize: '12px',
              border: '1.5px solid rgba(240, 178, 71, 0.4)',
            }}
          >
            {item.survivorName.substring(0, 2).toUpperCase()}
          </div>
          <div>
            <div style={{ fontWeight: 700, color: 'var(--text-main)', fontSize: '13.5px' }}>
              {item.survivorName}
            </div>
            <div className="text-meta" style={{ fontSize: '11px' }}>{item.survivorEmail}</div>
          </div>
        </div>
      ),
    },
    {
      key: 'unitName',
      header: 'Unit Inventaris',
      render: (item) => {
        const unit = units.find((u) => u.id === item.unitId || u.code === item.unitCode);
        const imageUrl = unit?.imageUrl;

        return (
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <div
              style={{
                width: '42px',
                height: '42px',
                borderRadius: 'var(--radius-sm)',
                overflow: 'hidden',
                backgroundColor: 'rgba(255, 255, 255, 0.04)',
                border: '1px solid rgba(240, 178, 71, 0.35)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                flexShrink: 0,
              }}
            >
              {imageUrl ? (
                <img
                  src={imageUrl}
                  alt={item.unitName}
                  style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                />
              ) : (
                <Boxes size={18} style={{ color: '#F0B247', opacity: 0.8 }} />
              )}
            </div>
            <div>
              <div style={{ fontWeight: 700, color: 'var(--text-main)', fontSize: '13.5px' }}>{item.unitName}</div>
              <div className="text-meta text-mono" style={{ fontSize: '11px', color: '#F0B247' }}>{item.unitCode}</div>
            </div>
          </div>
        );
      },
    },
    {
      key: 'loanDate',
      header: 'Tanggal Pinjam',
      render: (item) => (
        <span className="text-mono" style={{ fontSize: '12.5px', color: 'var(--text-secondary)' }}>
          {item.loanDate}
        </span>
      ),
    },
    {
      key: 'dueDate',
      header: 'Tenggat',
      render: (item) => (
        <span className="text-mono" style={{ fontSize: '12.5px', color: 'var(--text-secondary)' }}>
          {item.dueDate}
        </span>
      ),
    },
    {
      key: 'returnDate',
      header: 'Tanggal Kembali',
      render: (item) =>
        item.returnDate ? (
          <span
            className="text-mono"
            style={{
              fontSize: '12.5px',
              color: '#4ADE80',
              fontWeight: 600,
            }}
          >
            {item.returnDate}
          </span>
        ) : (
          <span
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '4px',
              fontSize: '11px',
              fontFamily: 'var(--font-mono)',
              padding: '2px 7px',
              borderRadius: 'var(--radius-xs)',
              background: 'rgba(240, 178, 71, 0.12)',
              color: '#F0B247',
              border: '1px solid rgba(240, 178, 71, 0.35)',
            }}
          >
            <Clock size={11} /> Belum Kembali
          </span>
        ),
    },
    {
      key: 'fineAmount',
      header: 'Denda Pemeliharaan',
      render: (item) =>
        item.fineAmount > 0 ? (
          <span
            style={{
              display: 'inline-flex',
              alignItems: 'center',
              gap: '5px',
              padding: '3px 8px',
              borderRadius: 'var(--radius-xs)',
              background: 'rgba(239, 68, 68, 0.12)',
              border: '1px solid rgba(239, 68, 68, 0.4)',
              color: '#EF4444',
              fontFamily: 'var(--font-mono)',
              fontWeight: 700,
              fontSize: '12px',
            }}
          >
            Rp {item.fineAmount.toLocaleString('id-ID')}
          </span>
        ) : (
          <span className="text-meta text-mono" style={{ fontSize: '12px', color: '#718170' }}>
            Rp 0
          </span>
        ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (item) => <StatusBadge status={item.status} />,
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '22px' }}>
      {/* =========================================
          TOP TACTICAL METRIC HUD
          ========================================= */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '18px' }}>
        <StatCard
          label="Total Arsip Transaksi"
          value={`${totalArchive} Transaksi`}
          subtext="Seluruh rekam jejak keluar masuk gudang"
          icon={<FileText size={20} />}
          isAmber={true}
          progress={100}
        />
        <StatCard
          label="Total Denda Tercatat"
          value={`Rp ${totalFines.toLocaleString('id-ID')}`}
          subtext="Akumulasi denda pemeliharaan keterlambatan"
          icon={<Coins size={20} />}
          isAmber={true}
          progress={totalFines > 0 ? 70 : 0}
        />
        <StatCard
          label="Disiplin Logistik Sektor"
          value={`${disciplineRate}% Tepat`}
          subtext="Persentase pengembalian tanpa denda"
          icon={<CheckCircle2 size={20} />}
          isAmber={true}
          progress={disciplineRate}
        />
        <StatCard
          label="Unit Paling Dibutuhkan"
          value={topBorrowedUnitName}
          subtext="Peralatan dengan rotasi peminjaman tertinggi"
          icon={<Sparkles size={20} />}
          isAmber={true}
          progress={85}
        />
      </div>

      {/* =========================================
          TACTICAL FILTER CARD DECK
          ========================================= */}
      <div
        className="tactical-card no-print"
        style={{
          padding: '18px 22px',
          display: 'flex',
          flexDirection: 'column',
          gap: '16px',
        }}
      >
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '12px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <Calendar size={18} style={{ color: '#F0B247' }} />
            <span style={{ fontWeight: 700, fontSize: '14px', color: 'var(--text-main)' }}>
              Filter Arsip &amp; Audit Logistik
            </span>
          </div>

          {/* Quick Date Range Preset Buttons */}
          <div className="filter-presets-group">
            <button
              type="button"
              className={`filter-preset-chip ${activePreset === 'all' ? 'active' : ''}`}
              onClick={() => handleApplyPreset('all')}
            >
              Semua Waktu
            </button>
            <button
              type="button"
              className={`filter-preset-chip ${activePreset === '7days' ? 'active' : ''}`}
              onClick={() => handleApplyPreset('7days')}
            >
              7 Hari Terakhir
            </button>
            <button
              type="button"
              className={`filter-preset-chip ${activePreset === '30days' ? 'active' : ''}`}
              onClick={() => handleApplyPreset('30days')}
            >
              30 Hari Terakhir
            </button>
            <button
              type="button"
              className={`filter-preset-chip ${activePreset === 'thisMonth' ? 'active' : ''}`}
              onClick={() => handleApplyPreset('thisMonth')}
            >
              Bulan Ini
            </button>
          </div>
        </div>

        <form
          onSubmit={handleApplyFilters}
          style={{
            display: 'flex',
            flexWrap: 'wrap',
            alignItems: 'flex-end',
            justifyContent: 'space-between',
            gap: '14px',
            borderTop: '1px solid rgba(240, 178, 71, 0.15)',
            paddingTop: '14px',
          }}
        >
          <div style={{ display: 'flex', flexWrap: 'wrap', alignItems: 'flex-end', gap: '14px' }}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '5px' }}>
              <label className="text-meta" style={{ fontWeight: 600, fontSize: '11.5px', color: '#8C998A' }}>
                Dari Tanggal Pinjam
              </label>
              <input
                type="date"
                className="input-control text-mono"
                style={{ width: '160px', height: '38px', fontSize: '12.5px' }}
                value={startDate}
                onChange={(e) => {
                  setStartDate(e.target.value);
                  setActivePreset('all');
                }}
              />
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '5px' }}>
              <label className="text-meta" style={{ fontWeight: 600, fontSize: '11.5px', color: '#8C998A' }}>
                Sampai Tanggal
              </label>
              <input
                type="date"
                className="input-control text-mono"
                style={{ width: '160px', height: '38px', fontSize: '12.5px' }}
                value={endDate}
                onChange={(e) => {
                  setEndDate(e.target.value);
                  setActivePreset('all');
                }}
              />
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '5px' }}>
              <label className="text-meta" style={{ fontWeight: 600, fontSize: '11.5px', color: '#8C998A' }}>
                Personel Survivor
              </label>
              <FilterSelect
                value={memberFilter}
                onChange={setMemberFilter}
                placeholder="Semua Anggota"
                options={survivors.map((s) => ({ value: s.id, label: s.name }))}
              />
            </div>

            <div style={{ display: 'flex', gap: '8px' }}>
              <PrimaryButton type="submit" size="sm" icon={<Filter size={14} />}>
                Terapkan
              </PrimaryButton>
              {(startDate || endDate || memberFilter) && (
                <SecondaryButton type="button" size="sm" onClick={handleResetFilters} icon={<RotateCcw size={13} />}>
                  Reset
                </SecondaryButton>
              )}
            </div>
          </div>

          <SecondaryButton icon={<Printer size={16} />} onClick={handlePrint} className="no-print">
            Cetak Laporan Manifest
          </SecondaryButton>
        </form>
      </div>

      {/* =========================================
          PRINT HEADER BANNER (FOR PRINTER ONLY)
          ========================================= */}
      <div
        className="print-header"
        style={{
          display: 'none',
          paddingBottom: '16px',
          marginBottom: '16px',
          borderBottom: '2px solid #000',
        }}
      >
        <h2 style={{ fontSize: '20px', fontWeight: 700 }}>DEADLOCK — REKAP RIWAYAT PEMINJAMAN UNIT</h2>
        <p style={{ fontSize: '12px', marginTop: '4px' }}>
          Haven Sector 4 Logistics &bull; Cetak Tanggal: {new Date().toLocaleDateString('id-ID')}
        </p>
      </div>

      {/* =========================================
          HISTORY AUDIT TABLE & SUMMARY STRIP
          ========================================= */}
      <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
        <DataTable
          columns={columns}
          data={filteredHistory}
          keyExtractor={(item) => item.id}
          emptyTitle="Tidak Ada Riwayat Transaksi"
          emptyDescription="Tidak ada catatan peminjaman unit yang sesuai dengan filter rentang tanggal dan personel."
        />

        {filteredHistory.length > 0 && (
          <div
            style={{
              padding: '12px 18px',
              backgroundColor: 'rgba(240, 178, 71, 0.05)',
              border: '1px solid rgba(240, 178, 71, 0.3)',
              borderRadius: 'var(--radius-md)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              flexWrap: 'wrap',
              gap: '12px',
              fontSize: '12.5px',
            }}
          >
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <ShieldCheck size={16} style={{ color: '#4ADE80' }} />
              <span>
                Total Rekam Transaksi Terfilter: <strong>{filteredHistory.length} Transaksi</strong>
              </span>
            </div>

            <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
              <span>
                Total Denda Sesi Ini:{' '}
                <strong style={{ color: filteredFinesTotal > 0 ? '#EF4444' : '#4ADE80', fontFamily: 'var(--font-mono)' }}>
                  Rp {filteredFinesTotal.toLocaleString('id-ID')}
                </strong>
              </span>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};
