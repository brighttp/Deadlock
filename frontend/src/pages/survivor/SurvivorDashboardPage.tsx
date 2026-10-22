import React from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { PrimaryButton, SecondaryButton } from '../../components/common/Buttons';
import { StatCard } from '../../components/common/StatCard';
import { StatusBadge } from '../../components/common/StatusBadge';
import { DataTable, type Column } from '../../components/common/DataTable';
import type { Loan } from '../../types';
import { MAX_ACTIVE_LOANS } from '../../mock/data';
import {
  PackageSearch,
  AlertTriangle,
  ShieldCheck,
  Clock,
  CheckCircle,
  Radio,
  Crosshair,
  ArrowRight,
  Activity,
  MapPin,
  Sparkles,
  Boxes,
} from 'lucide-react';

export const SurvivorDashboardPage: React.FC = () => {
  const { currentUser, loans, units, categories, setCurrentPage } = useDeadlock();

  if (!currentUser) return null;

  const userLoans = loans.filter((l) => l.survivorId === currentUser.id);
  const activeUserLoans = userLoans.filter((l) => l.status === 'Aktif' || l.status === 'Terlambat');
  const activeCount = activeUserLoans.length;
  const remainingQuota = Math.max(0, MAX_ACTIVE_LOANS - activeCount);
  const hasOverdue = activeUserLoans.some((l) => l.status === 'Terlambat');

  // Quick recommendations: top available items from the warehouse
  const availableUnits = units.filter((u) => u.status === 'Tersedia').slice(0, 3);

  const getCategoryName = (catId: string) => {
    return categories.find((c) => c.id === catId)?.name || 'Taktis';
  };

  const getDueCountdown = (dueDateStr: string, status: string) => {
    if (status === 'Terlambat') {
      return (
        <span className="countdown-badge danger">
          <AlertTriangle size={12} /> Terlambat
        </span>
      );
    }
    const today = new Date();
    const due = new Date(dueDateStr);
    const diffDays = Math.ceil((due.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));

    if (diffDays <= 0) {
      return (
        <span className="countdown-badge danger">
          <Clock size={12} /> Hari Ini
        </span>
      );
    }
    if (diffDays <= 2) {
      return (
        <span className="countdown-badge warning">
          <Clock size={12} /> {diffDays} Hari Lagi
        </span>
      );
    }
    return (
      <span className="countdown-badge safe">
        <ShieldCheck size={12} /> {diffDays} Hari Lagi
      </span>
    );
  };

  const columns: Column<Loan>[] = [
    {
      key: 'unitName',
      header: 'Nama Unit',
      render: (item) => {
        const unit = units.find((u) => u.id === item.unitId || u.code === item.unitCode);
        const imageUrl = unit?.imageUrl;

        return (
          <div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
            <div
              style={{
                width: '48px',
                height: '48px',
                borderRadius: 'var(--radius-sm)',
                overflow: 'hidden',
                backgroundColor: 'rgba(255, 255, 255, 0.04)',
                border: '1px solid rgba(163, 184, 121, 0.25)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                flexShrink: 0,
                boxShadow: '0 2px 8px rgba(0, 0, 0, 0.4)',
              }}
            >
              {imageUrl ? (
                <img
                  src={imageUrl}
                  alt={item.unitName}
                  style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                />
              ) : (
                <PackageSearch size={22} style={{ color: 'var(--color-accent)', opacity: 0.7 }} />
              )}
            </div>
            <div>
              <div style={{ fontWeight: 600, color: 'var(--text-main)', fontSize: '13.5px' }}>
                {item.unitName}
              </div>
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
      header: 'Tenggat Waktu',
      render: (item) => (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '3px' }}>
          <span className="text-mono" style={{ fontSize: '12.5px' }}>
            {item.dueDate}
          </span>
          <div>{getDueCountdown(item.dueDate, item.status)}</div>
        </div>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      render: (item) => <StatusBadge status={item.status} />,
    },
    {
      key: 'fineAmount',
      header: 'Estimasi Denda',
      render: (item) =>
        item.fineAmount > 0 ? (
          <span style={{ color: 'var(--color-danger)', fontWeight: 700 }}>
            Rp {item.fineAmount.toLocaleString('id-ID')}
          </span>
        ) : (
          <span className="text-meta">Rp 0 (Bebas Denda)</span>
        ),
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '22px' }}>
      {/* =========================================
          HERO OPERATIONAL STATUS MISSION DECK
          ========================================= */}
      <div className="dashboard-mission-bar">
        <div className="mission-bar-left">
          <div className="mission-bar-tagline">
            <Radio size={13} className="text-pulse" />
            <span>TERMINAL OPERASIONAL SURVIVOR &bull; KODE SEKTOR 4</span>
          </div>
          <h1 className="mission-bar-title">Selamat Bertugas, {currentUser.name}</h1>
          <div className="mission-telemetry-chips" style={{ marginTop: '4px' }}>
            <span className="telemetry-chip chip-green">
              <span className="status-pip-dot pulse" style={{ width: 6, height: 6, borderRadius: '50%', background: '#4ADE80' }} />
              STATUS: SIAP PENUGASAN
            </span>
            <span className="telemetry-chip chip-amber">
              <Activity size={12} /> ANCAMAN: TINGKAT 2 (WASPADA)
            </span>
            <span className="telemetry-chip">
              <MapPin size={12} /> {currentUser.basecamp}
            </span>
          </div>
        </div>

        <div>
          <PrimaryButton
            icon={<PackageSearch size={16} />}
            onClick={() => setCurrentPage('survivor-catalog')}
          >
            Lihat Katalog Unit
          </PrimaryButton>
        </div>
      </div>

      {/* Warning banner if overdue */}
      {hasOverdue && (
        <div className="warning-banner">
          <AlertTriangle size={20} />
          <div style={{ flex: 1 }}>
            <strong>Peringatan Tenggat Karantina:</strong> Anda memiliki unit pinjaman yang telah melewati
            batas waktu tenggat pengembalian. Segera kembalikan unit ke posko gudang logistik untuk mencegah
            denda berjalan.
          </div>
        </div>
      )}

      {/* =========================================
          3 TACTICAL HUD STAT CARDS (WITH METERS)
          ========================================= */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '18px' }}>
        <StatCard
          label="Pinjaman Aktif"
          value={`${activeCount} / ${MAX_ACTIVE_LOANS}`}
          subtext={
            remainingQuota === 0
              ? 'Batas kuota maksimal peminjaman telah tercapai'
              : `Anda masih dapat meminjam ${remainingQuota} unit lagi`
          }
          icon={<Clock size={20} />}
          isAmber={true}
          progress={(activeCount / MAX_ACTIVE_LOANS) * 100}
          progressColor="amber"
        />
        <StatCard
          label="Unit Siap Diajukan"
          value={`${remainingQuota} Unit`}
          subtext="Sisa jatah unit yang siap diajukan penugasan"
          icon={<ShieldCheck size={20} />}
          isAmber={true}
          progress={(remainingQuota / MAX_ACTIVE_LOANS) * 100}
          progressColor="amber"
        />
        <StatCard
          label="Total Riwayat Peminjaman"
          value={`${userLoans.length} Transaksi`}
          subtext="Seluruh rekam jejak peminjaman Anda di Sektor 4"
          icon={<CheckCircle size={20} />}
          isAmber={true}
          progress={100}
          progressColor="amber"
        />
      </div>

      {/* =========================================
          2-COLUMN TACTICAL DASHBOARD WORKSPACE
          ========================================= */}
      <div className="dashboard-layout-grid">
        {/* LEFT COLUMN: ACTIVE LOANS TABLE & QUICK CATALOG */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '22px' }}>
          {/* Active Loans Section */}
          <div className="tactical-card" style={{ padding: 0, overflow: 'hidden' }}>
            <div
              style={{
                padding: '16px 20px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                borderBottom: '1px solid var(--border-color)',
                flexWrap: 'wrap',
                gap: '12px',
              }}
            >
              <div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <h2 className="section-title">Unit Sedang Anda Pinjam</h2>
                  <span
                    style={{
                      backgroundColor: 'rgba(240, 178, 71, 0.15)',
                      color: '#F0B247',
                      border: '1px solid rgba(240, 178, 71, 0.35)',
                      padding: '2px 8px',
                      borderRadius: '4px',
                      fontSize: '11px',
                      fontFamily: 'var(--font-mono)',
                      fontWeight: 700,
                    }}
                  >
                    AKTIF &bull; {activeCount} UNIT
                  </span>
                </div>
                <p className="text-secondary" style={{ fontSize: '12.5px', marginTop: '2px' }}>
                  Peralatan operasional di bawah penguasaan Anda saat ini. Pastikan unit dirawat sesuai SOP.
                </p>
              </div>

              <SecondaryButton size="sm" onClick={() => setCurrentPage('survivor-loans')}>
                Buka Halaman Lengkap
                <ArrowRight size={14} />
              </SecondaryButton>
            </div>

            <DataTable
              columns={columns}
              data={activeUserLoans}
              keyExtractor={(item) => item.id}
              emptyTitle="Belum Ada Unit yang Dipinjam"
              emptyDescription="Anda saat ini tidak memiliki pinjaman aktif. Buka katalog untuk mengajukan peminjaman perlengkapan taktis."
            />
          </div>

          {/* Quick Deploy Gear Showcase */}
          <div className="quick-units-card">
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Boxes size={18} style={{ color: 'var(--color-accent)' }} />
                <h3 style={{ fontSize: '14.5px', fontWeight: 700, color: 'var(--text-main)', margin: 0 }}>
                  Perlengkapan Siap Penugasan di Gudang
                </h3>
              </div>
              <button
                type="button"
                onClick={() => setCurrentPage('survivor-catalog')}
                style={{
                  background: 'none',
                  border: 'none',
                  color: 'var(--color-accent)',
                  fontFamily: 'var(--font-mono)',
                  fontSize: '11px',
                  fontWeight: 600,
                  cursor: 'pointer',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '4px',
                }}
              >
                Lihat Semua Katalog &rarr;
              </button>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '12px' }}>
              {availableUnits.map((u) => (
                <div
                  key={u.id}
                  className="quick-unit-item"
                  onClick={() => setCurrentPage('survivor-catalog')}
                  title="Klik untuk membuka katalog"
                >
                  <img src={u.imageUrl} alt={u.name} className="quick-unit-img" />
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '2px', minWidth: 0, flex: 1 }}>
                    <span style={{ fontSize: '12.5px', fontWeight: 600, color: 'var(--text-main)', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                      {u.name}
                    </span>
                    <span className="text-meta text-mono" style={{ fontSize: '10.5px' }}>
                      {u.code} &bull; {u.categoryIds[0] ? getCategoryName(u.categoryIds[0]) : 'Taktis'}
                    </span>
                    <span style={{ fontSize: '10.5px', color: '#4ADE80', fontWeight: 600, marginTop: '2px' }}>
                      &bull; Siap Diajukan
                    </span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* RIGHT COLUMN: PERIMETER RADAR & LOGISTICS SOP */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '22px' }}>
          {/* Animated Perimeter Radar Scanner */}
          <div className="radar-widget-card">
            <div className="radar-header">
              <span className="radar-title">
                <Crosshair size={14} /> RADAR PERIMETER SEKTOR 4
              </span>
              <span style={{ fontFamily: 'var(--font-mono)', fontSize: '10px', color: '#4ADE80', fontWeight: 700 }}>
                SCANNING
              </span>
            </div>

            {/* Radar Circular Screen */}
            <div className="radar-screen-wrap">
              <div className="radar-ring ring-1" />
              <div className="radar-ring ring-2" />
              <div className="radar-ring ring-3" />
              <div className="radar-crosshair-v" />
              <div className="radar-crosshair-h" />
              <div className="radar-sweep-arm" />
              <div className="radar-blip blip-1" title="Pos Pantau Logistik" />
              <div className="radar-blip blip-2" title="Gerbang Perimeter Utara" />
              <div className="radar-blip blip-3" title="Barak Survivor 4B" />
            </div>

            {/* Telemetry Status Rows */}
            <div className="radar-status-rows">
              <div className="radar-status-item">
                <span className="radar-status-key">Gerbang Perimeter:</span>
                <span className="radar-status-val">TERKUNCI (AMAN)</span>
              </div>
              <div className="radar-status-item">
                <span className="radar-status-key">Frekuensi Radio:</span>
                <span className="radar-status-val">142.85 MHz</span>
              </div>
              <div className="radar-status-item">
                <span className="radar-status-key">Kepadatan Ancaman:</span>
                <span className="radar-status-val" style={{ color: '#F0B247' }}>RENDAH-SEDANG</span>
              </div>
              <div className="radar-status-item">
                <span className="radar-status-key">Sensor Dekontaminasi:</span>
                <span className="radar-status-val">NORMAL</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
