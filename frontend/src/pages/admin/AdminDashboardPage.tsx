import React from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { StatCard } from '../../components/common/StatCard';
import { StatusBadge } from '../../components/common/StatusBadge';
import { DataTable, type Column } from '../../components/common/DataTable';
import type { Loan } from '../../types';
import { Boxes, CheckCircle2, Clock, AlertTriangle, ArrowRight, ArrowUpRight } from 'lucide-react';

export const AdminDashboardPage: React.FC = () => {
  const { stats, loans, units, setCurrentPage, users } = useDeadlock();

  // Active or Overdue loans only
  const activeLoans = loans.filter((l) => l.status === 'Aktif' || l.status === 'Terlambat');

  const survivorCount = users.filter((u) => u.role === 'Survivor').length;

  const columns: Column<Loan>[] = [
    {
      key: 'survivorName',
      header: 'Survivor',
      render: (item) => (
        <div>
          <div style={{ fontWeight: 600 }}>{item.survivorName}</div>
          <div className="text-meta">{item.survivorEmail}</div>
        </div>
      ),
    },
    {
      key: 'unitName',
      header: 'Unit',
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
                border: '1px solid rgba(163, 184, 121, 0.25)',
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
                <Boxes size={18} style={{ color: 'var(--color-accent)', opacity: 0.7 }} />
              )}
            </div>
            <div>
              <div style={{ fontWeight: 600, color: 'var(--text-main)' }}>{item.unitName}</div>
              <div className="text-meta text-mono">{item.unitCode}</div>
            </div>
          </div>
        );
      },
    },
    {
      key: 'loanDate',
      header: 'Tanggal Pinjam',
      render: (item) => <span className="text-mono">{item.loanDate}</span>,
    },
    {
      key: 'dueDate',
      header: 'Tenggat',
      render: (item) => {
        const isOverdue = item.status === 'Terlambat';
        return (
          <span
            className="text-mono"
            style={{
              color: isOverdue ? 'var(--color-danger)' : 'inherit',
              fontWeight: isOverdue ? 600 : 400,
            }}
          >
            {item.dueDate}
          </span>
        );
      },
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
      render: () => (
        <button
          type="button"
          className="btn-secondary btn-sm"
          onClick={() => setCurrentPage('admin-loans')}
        >
          Proses
        </button>
      ),
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      {/* 4 Stat Cards */}
      <div className="stats-grid">
        <StatCard
          label="Total Unit"
          value={stats.totalUnits}
          subtext={`${survivorCount} personel survivor terdaftar`}
          icon={<Boxes size={20} />}
        />
        <StatCard
          label="Unit Tersedia"
          value={stats.availableUnits}
          subtext="Siap untuk penugasan"
          icon={<CheckCircle2 size={20} />}
          accentColor="var(--color-success)"
        />
        <StatCard
          label="Unit Dipinjam"
          value={stats.borrowedUnits}
          subtext="Berada di lapangan"
          icon={<Clock size={20} />}
          isAmber={true}
        />
        <StatCard
          label="Peminjaman Terlambat"
          value={stats.overdueLoansCount}
          subtext="Memerlukan tindakan penagihan"
          icon={<AlertTriangle size={20} />}
          accentColor={stats.overdueLoansCount > 0 ? 'var(--color-danger)' : undefined}
        />
      </div>

      {/* Active Loans Section */}
      <div className="tactical-card" style={{ padding: 0, overflow: 'hidden' }}>
        <div
          style={{
            padding: '16px 20px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            borderBottom: '1px solid var(--border-color)',
          }}
        >
          <div>
            <h2 className="section-title">Peminjaman Berjalan &amp; Terlambat</h2>
            <p className="text-secondary" style={{ fontSize: '12.5px', marginTop: '2px' }}>
              Daftar transaksi peminjaman aktif yang belum dikembalikan ke gudang.
            </p>
          </div>
          <button
            type="button"
            onClick={() => setCurrentPage('admin-loans')}
            style={{
              background: 'none',
              border: 'none',
              color: 'var(--color-accent)',
              fontSize: '13px',
              fontWeight: 600,
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              gap: '4px',
            }}
          >
            Lihat Semua
            <ArrowUpRight size={15} />
          </button>
        </div>

        <DataTable
          columns={columns}
          data={activeLoans}
          keyExtractor={(item) => item.id}
          emptyTitle="Tidak Ada Peminjaman Aktif"
          emptyDescription="Saat ini seluruh unit logistik berada di dalam gudang atau semua pinjaman telah dikembalikan."
        />
      </div>
    </div>
  );
};
