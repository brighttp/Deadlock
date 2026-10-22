import React, { useState } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { SearchInput } from '../../components/common/SearchInput';
import { StatusBadge } from '../../components/common/StatusBadge';
import { DataTable, type Column } from '../../components/common/DataTable';
import { Modal } from '../../components/common/Modal';
import { PrimaryButton, SecondaryButton } from '../../components/common/Buttons';
import { FormField } from '../../components/common/FormField';
import { StatCard } from '../../components/common/StatCard';
import type { Loan } from '../../types';
import { DAILY_FINE_RATE } from '../../mock/data';
import {
  ArrowLeftRight,
  CheckCheck,
  Clock,
  AlertTriangle,
  CheckCircle2,
  Boxes,
  ShieldAlert,
  Calendar,
  User,
} from 'lucide-react';

export const LoansPage: React.FC = () => {
  const { loans, units, processReturn } = useDeadlock();

  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  // Modal Return State
  const [selectedLoanToReturn, setSelectedLoanToReturn] = useState<Loan | null>(null);
  const [returnDateInput, setReturnDateInput] = useState<string>(() => {
    return new Date().toISOString().split('T')[0];
  });

  // Calculate Metrics
  const activeLoans = loans.filter((l) => l.status === 'Aktif');
  const overdueLoans = loans.filter((l) => l.status === 'Terlambat');
  const completedLoans = loans.filter((l) => l.status === 'Selesai');
  const totalLoans = loans.length;

  const filteredLoans = loans.filter((l) => {
    const matchesSearch =
      l.survivorName.toLowerCase().includes(search.toLowerCase()) ||
      l.survivorEmail.toLowerCase().includes(search.toLowerCase()) ||
      l.unitName.toLowerCase().includes(search.toLowerCase()) ||
      l.unitCode.toLowerCase().includes(search.toLowerCase());

    const matchesStatus = !statusFilter || l.status === statusFilter;

    return matchesSearch && matchesStatus;
  });

  // Calculate return preview
  const getReturnCalculation = () => {
    if (!selectedLoanToReturn) return { overdueDays: 0, fineAmount: 0 };

    const due = new Date(selectedLoanToReturn.dueDate);
    const ret = new Date(returnDateInput);
    const diffTime = ret.getTime() - due.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const overdueDays = Math.max(0, diffDays);
    const fineAmount = overdueDays * DAILY_FINE_RATE;

    return { overdueDays, fineAmount };
  };

  const handleOpenReturnModal = (loan: Loan) => {
    setSelectedLoanToReturn(loan);
    setReturnDateInput(new Date().toISOString().split('T')[0]);
  };

  const handleConfirmReturn = () => {
    if (selectedLoanToReturn) {
      processReturn(selectedLoanToReturn.id, returnDateInput);
      setSelectedLoanToReturn(null);
    }
  };

  const { overdueDays, fineAmount } = getReturnCalculation();

  // Helper to calculate days remaining or overdue days from today
  const getTimeRemainingBadge = (loan: Loan) => {
    if (loan.status === 'Selesai') {
      return (
        <span className="text-meta" style={{ fontSize: '11px' }}>
          Dikembalikan {loan.returnDate}
        </span>
      );
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const dueDate = new Date(loan.dueDate);
    dueDate.setHours(0, 0, 0, 0);

    const diffDays = Math.round((dueDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays < 0 || loan.status === 'Terlambat') {
      const lateDays = Math.abs(diffDays) || 1;
      return (
        <span
          style={{
            fontFamily: 'var(--font-mono)',
            fontSize: '11px',
            color: '#EF4444',
            fontWeight: 700,
            display: 'inline-flex',
            alignItems: 'center',
            gap: '4px',
          }}
        >
          <span style={{ width: 6, height: 6, borderRadius: '50%', background: '#EF4444' }} />
          TERLAMBAT {lateDays} HARI
        </span>
      );
    }

    if (diffDays === 0) {
      return (
        <span
          style={{
            fontFamily: 'var(--font-mono)',
            fontSize: '11px',
            color: '#F0B247',
            fontWeight: 700,
          }}
        >
          Jatuh Tempo Hari Ini
        </span>
      );
    }

    return (
      <span
        style={{
          fontFamily: 'var(--font-mono)',
          fontSize: '11px',
          color: '#4ADE80',
        }}
      >
        {diffDays} Hari Tersisa
      </span>
    );
  };

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
      header: 'Unit Logistik',
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
      header: 'Tenggat & Durasi',
      render: (item) => (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '2px' }}>
          <span
            className="text-mono"
            style={{
              color: item.status === 'Terlambat' ? '#EF4444' : 'var(--text-main)',
              fontWeight: item.status === 'Terlambat' ? 700 : 500,
              fontSize: '12.5px',
            }}
          >
            {item.dueDate}
          </span>
          {getTimeRemainingBadge(item)}
        </div>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (item) => <StatusBadge status={item.status} />,
    },
    {
      key: 'actions',
      header: 'Aksi',
      align: 'right',
      render: (item) => {
        const isReturnable = item.status === 'Aktif' || item.status === 'Terlambat';

        if (!isReturnable) {
          return (
            <span
              className="text-meta"
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '5px',
                justifyContent: 'flex-end',
                fontFamily: 'var(--font-mono)',
                fontSize: '12px',
                color: '#4ADE80',
              }}
            >
              <CheckCheck size={15} />
              Selesai ({item.returnDate || '-'})
            </span>
          );
        }

        return (
          <PrimaryButton
            size="sm"
            onClick={() => handleOpenReturnModal(item)}
            icon={<ArrowLeftRight size={14} />}
          >
            Proses Pengembalian
          </PrimaryButton>
        );
      },
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '22px' }}>
      {/* =========================================
          TOP TACTICAL METRIC HUD
          ========================================= */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '18px' }}>
        <StatCard
          label="Pinjaman Sedang Berjalan"
          value={`${activeLoans.length} Unit`}
          subtext="Berada di lapangan dan dalam masa tenggat"
          icon={<Clock size={20} />}
          isAmber={true}
          progress={totalLoans > 0 ? (activeLoans.length / totalLoans) * 100 : 0}
        />
        <StatCard
          label="Terlambat (Perlu Tindakan)"
          value={`${overdueLoans.length} Unit`}
          subtext={
            overdueLoans.length > 0
              ? 'Telah melampaui tenggat pengembalian posko'
              : 'Seluruh unit logistik tepat waktu'
          }
          icon={<AlertTriangle size={20} />}
          accentColor={overdueLoans.length > 0 ? 'var(--color-danger)' : undefined}
          isAmber={overdueLoans.length === 0}
          progress={totalLoans > 0 ? (overdueLoans.length / totalLoans) * 100 : 0}
        />
        <StatCard
          label="Total Selesai Dikembalikan"
          value={`${completedLoans.length} Transaksi`}
          subtext="Telah diverifikasi kembali di pos komando"
          icon={<CheckCircle2 size={20} />}
          isAmber={true}
          progress={totalLoans > 0 ? (completedLoans.length / totalLoans) * 100 : 0}
        />
      </div>

      {/* =========================================
          URGENT OVERDUE ALERT BANNER
          ========================================= */}
      {overdueLoans.length > 0 && (
        <div className="urgent-overdue-alert">
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <ShieldAlert size={22} style={{ color: '#EF4444', flexShrink: 0 }} />
            <div>
              <strong style={{ color: '#F3F6EF', fontSize: '13.5px' }}>
                PERINGATAN TENGGAT: Terdapat {overdueLoans.length} unit logistik yang terlambat dikembalikan!
              </strong>
              <div className="text-meta" style={{ fontSize: '12px', marginTop: '2px' }}>
                Denda keterlambatan berjalan sebesar Rp {DAILY_FINE_RATE.toLocaleString('id-ID')} / hari sedang berlaku untuk unit ini.
              </div>
            </div>
          </div>

          <button
            type="button"
            className="btn-secondary btn-sm"
            style={{
              borderColor: 'rgba(239, 68, 68, 0.5)',
              color: '#EF4444',
              whiteSpace: 'nowrap',
            }}
            onClick={() => setStatusFilter(statusFilter === 'Terlambat' ? '' : 'Terlambat')}
          >
            {statusFilter === 'Terlambat' ? 'Tampilkan Semua' : 'Filter Unit Terlambat'}
          </button>
        </div>
      )}

      {/* =========================================
          FILTER, SEARCH & QUICK PRESETS BAR
          ========================================= */}
      <div className="filter-bar">
        <div className="filter-group-left">
          <SearchInput
            value={search}
            onChange={setSearch}
            placeholder="Cari survivor, unit, atau kode unit..."
          />

          {/* Quick Filter Status Presets */}
          <div className="filter-presets-group">
            <button
              type="button"
              className={`filter-preset-chip ${statusFilter === '' ? 'active' : ''}`}
              onClick={() => setStatusFilter('')}
            >
              Semua ({totalLoans})
            </button>
            <button
              type="button"
              className={`filter-preset-chip ${statusFilter === 'Aktif' ? 'active' : ''}`}
              onClick={() => setStatusFilter('Aktif')}
            >
              Aktif ({activeLoans.length})
            </button>
            <button
              type="button"
              className={`filter-preset-chip ${statusFilter === 'Terlambat' ? 'active' : ''}`}
              style={overdueLoans.length > 0 && statusFilter !== 'Terlambat' ? { color: '#EF4444' } : {}}
              onClick={() => setStatusFilter('Terlambat')}
            >
              Terlambat ({overdueLoans.length})
            </button>
            <button
              type="button"
              className={`filter-preset-chip ${statusFilter === 'Selesai' ? 'active' : ''}`}
              onClick={() => setStatusFilter('Selesai')}
            >
              Selesai ({completedLoans.length})
            </button>
          </div>
        </div>

        <div className="text-meta">
          Menampilkan <strong>{filteredLoans.length}</strong> dari {loans.length} transaksi
        </div>
      </div>

      {/* =========================================
          LOANS DATA TABLE
          ========================================= */}
      <DataTable
        columns={columns}
        data={filteredLoans}
        keyExtractor={(item) => item.id}
        emptyTitle="Tidak Ada Data Peminjaman"
        emptyDescription="Tidak ada data transaksi peminjaman yang cocok dengan kriteria pencarian atau status filter Anda."
      />

      {/* =========================================
          PROCESS RETURN MODAL
          ========================================= */}
      <Modal
        isOpen={selectedLoanToReturn !== null}
        onClose={() => setSelectedLoanToReturn(null)}
        title="Verifikasi Pengembalian Unit Fisik"
        maxWidth="520px"
        footer={
          <>
            <SecondaryButton size="sm" onClick={() => setSelectedLoanToReturn(null)}>
              Batal
            </SecondaryButton>
            <PrimaryButton size="sm" onClick={handleConfirmReturn} icon={<CheckCheck size={15} />}>
              Konfirmasi Selesai
            </PrimaryButton>
          </>
        }
      >
        {selectedLoanToReturn && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            <div
              style={{
                padding: '16px',
                backgroundColor: 'rgba(240, 178, 71, 0.05)',
                border: '1px solid rgba(240, 178, 71, 0.35)',
                borderLeft: '4px solid #F0B247',
                borderRadius: 'var(--radius-md)',
                display: 'flex',
                flexDirection: 'column',
                gap: '9px',
                fontSize: '13px',
              }}
            >
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span className="text-meta">Nama Survivor:</span>
                <span style={{ fontWeight: 700, color: 'var(--text-main)' }}>{selectedLoanToReturn.survivorName}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span className="text-meta">Nama Unit Logistik:</span>
                <span style={{ fontWeight: 600 }}>{selectedLoanToReturn.unitName}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span className="text-meta">Kode Inventaris:</span>
                <span className="text-mono" style={{ color: '#F0B247', fontWeight: 700 }}>
                  {selectedLoanToReturn.unitCode}
                </span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <span className="text-meta">Batas Tenggat Karantina:</span>
                <span className="text-mono">{selectedLoanToReturn.dueDate}</span>
              </div>
            </div>

            <FormField
              label="Tanggal Pengembalian Fisik"
              hint="Dapat disesuaikan untuk simulasi perhitungan keterlambatan posko."
            >
              <input
                type="date"
                className="input-control text-mono"
                value={returnDateInput}
                onChange={(e) => setReturnDateInput(e.target.value)}
              />
            </FormField>

            {/* Calculations Card */}
            <div
              style={{
                padding: '16px',
                backgroundColor: overdueDays > 0 ? 'rgba(239, 68, 68, 0.12)' : 'rgba(74, 222, 128, 0.08)',
                border: `1px solid ${overdueDays > 0 ? 'rgba(239, 68, 68, 0.45)' : 'rgba(74, 222, 128, 0.35)'}`,
                borderLeft: overdueDays > 0 ? '4px solid #EF4444' : '4px solid #4ADE80',
                borderRadius: 'var(--radius-md)',
                display: 'flex',
                flexDirection: 'column',
                gap: '8px',
              }}
            >
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontSize: '13px' }}>Keterlambatan:</span>
                <strong style={{ color: overdueDays > 0 ? '#EF4444' : '#4ADE80', fontFamily: 'var(--font-mono)' }}>
                  {overdueDays} Hari
                </strong>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span style={{ fontSize: '13px' }}>Denda Pemeliharaan:</span>
                <strong style={{ fontSize: '17px', color: overdueDays > 0 ? '#EF4444' : '#4ADE80', fontFamily: 'var(--font-mono)' }}>
                  Rp {fineAmount.toLocaleString('id-ID')}
                </strong>
              </div>
              <div className="text-meta" style={{ fontSize: '11px', marginTop: '2px' }}>
                Tarif Denda Operasional: Rp {DAILY_FINE_RATE.toLocaleString('id-ID')} / hari keterlambatan.
              </div>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};
