import React, { useState } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { SearchInput } from '../../components/common/SearchInput';
import { FilterSelect } from '../../components/common/FilterSelect';
import { StatusBadge } from '../../components/common/StatusBadge';
import { DataTable, type Column } from '../../components/common/DataTable';
import { Modal } from '../../components/common/Modal';
import { PrimaryButton, SecondaryButton } from '../../components/common/Buttons';
import { UnitCard } from '../../components/common/UnitCard';
import { EmptyState } from '../../components/common/EmptyState';
import type { Unit } from '../../types';
import { LOAN_DURATION_DAYS, MAX_ACTIVE_LOANS } from '../../mock/data';
import { HandHelping, AlertCircle, LayoutGrid, List } from 'lucide-react';

export const CatalogPage: React.FC = () => {
  const { currentUser, units, categories, borrowUnit, getSurvivorActiveLoansCount } = useDeadlock();

  const [search, setSearch] = useState('');
  const [selectedCategoryFilter, setSelectedCategoryFilter] = useState('');
  const [selectedStatusFilter, setSelectedStatusFilter] = useState('');
  const [viewMode, setViewMode] = useState<'grid' | 'table'>('grid');

  // Borrow Modal State
  const [selectedUnitToBorrow, setSelectedUnitToBorrow] = useState<Unit | null>(null);

  if (!currentUser) return null;

  const activeLoanCount = getSurvivorActiveLoansCount(currentUser.id);
  const isQuotaExceeded = activeLoanCount >= MAX_ACTIVE_LOANS;

  // Filter units
  const filteredUnits = units.filter((u) => {
    const matchesSearch =
      u.code.toLowerCase().includes(search.toLowerCase()) ||
      u.name.toLowerCase().includes(search.toLowerCase()) ||
      u.description.toLowerCase().includes(search.toLowerCase());

    const matchesCategory =
      !selectedCategoryFilter || u.categoryIds.includes(selectedCategoryFilter);

    const matchesStatus =
      !selectedStatusFilter || u.status === selectedStatusFilter;

    return matchesSearch && matchesCategory && matchesStatus;
  });

  const getCategoryName = (id: string) => {
    return categories.find((c) => c.id === id)?.name || id;
  };

  // Dates for loan preview
  const today = new Date();
  const todayFormatted = today.toISOString().split('T')[0];
  const dueDate = new Date(today);
  dueDate.setDate(dueDate.getDate() + LOAN_DURATION_DAYS);
  const dueDateFormatted = dueDate.toISOString().split('T')[0];

  const handleConfirmBorrow = () => {
    if (selectedUnitToBorrow) {
      borrowUnit(selectedUnitToBorrow.id);
      setSelectedUnitToBorrow(null);
    }
  };

  const renderActionButton = (item: Unit) => {
    const isAvailable = item.status === 'Tersedia';

    if (!isAvailable) {
      return (
        <button
          type="button"
          className="btn-secondary btn-sm"
          disabled
          style={{ width: '100%', justifyContent: 'center' }}
          title="Unit sedang dipinjam oleh personel lain"
        >
          Dipinjam
        </button>
      );
    }

    if (isQuotaExceeded) {
      return (
        <button
          type="button"
          className="btn-secondary btn-sm"
          disabled
          style={{ width: '100%', justifyContent: 'center' }}
          title="Batas maksimal 2 unit pinjaman tercapai"
        >
          Batas Penuh (Maks. 2)
        </button>
      );
    }

    return (
      <PrimaryButton
        size="sm"
        style={{ width: '100%', justifyContent: 'center' }}
        onClick={() => setSelectedUnitToBorrow(item)}
        icon={<HandHelping size={14} />}
      >
        Pinjam Unit
      </PrimaryButton>
    );
  };

  const columns: Column<Unit>[] = [
    {
      key: 'code',
      header: 'Kode',
      width: '120px',
      render: (item) => <span className="text-mono" style={{ fontWeight: 600 }}>{item.code}</span>,
    },
    {
      key: 'name',
      header: 'Nama Unit & Deskripsi',
      render: (item) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
          {item.imageUrl && (
            <img
              src={item.imageUrl}
              alt={item.name}
              style={{
                width: '40px',
                height: '40px',
                borderRadius: 'var(--radius-sm)',
                objectFit: 'cover',
                border: '1px solid var(--border-color)',
                flexShrink: 0,
              }}
            />
          )}
          <div>
            <div style={{ fontWeight: 600 }}>{item.name}</div>
            <div className="text-meta" style={{ maxWidth: '380px' }}>
              {item.description || '-'}
            </div>
          </div>
        </div>
      ),
    },
    {
      key: 'categories',
      header: 'Kategori',
      render: (item) => (
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '4px' }}>
          {item.categoryIds.map((catId) => (
            <span
              key={catId}
              style={{
                fontSize: '11.5px',
                padding: '2px 7px',
                borderRadius: 'var(--radius-sm)',
                backgroundColor: 'rgba(255, 255, 255, 0.05)',
                border: '1px solid var(--border-color)',
                color: 'var(--text-secondary)',
              }}
            >
              {getCategoryName(catId)}
            </span>
          ))}
        </div>
      ),
    },
    {
      key: 'status',
      header: 'Status',
      width: '130px',
      render: (item) => <StatusBadge status={item.status} />,
    },
    {
      key: 'actions',
      header: 'Aksi',
      align: 'right',
      width: '150px',
      render: (item) => renderActionButton(item),
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      {/* Quota Notice Banner if limit reached */}
      {isQuotaExceeded && (
        <div className="warning-banner">
          <AlertCircle size={18} />
          <span>
            <strong>Batas Kuota Tercapai:</strong> Anda telah meminjam maksimal 2 unit logistik aktif.
            Kembalikan salah satu unit di pos komando gudang sebelum dapat mengajukan pinjaman baru.
          </span>
        </div>
      )}

      {/* Filter and Search Bar */}
      <div className="filter-bar">
        <div className="filter-group-left">
          <SearchInput
            value={search}
            onChange={setSearch}
            placeholder="Cari unit atau spesifikasi..."
          />
          <FilterSelect
            value={selectedCategoryFilter}
            onChange={setSelectedCategoryFilter}
            placeholder="Semua Kategori"
            options={categories.map((c) => ({ value: c.id, label: c.name }))}
          />
          <FilterSelect
            value={selectedStatusFilter}
            onChange={setSelectedStatusFilter}
            placeholder="Semua Status"
            options={[
              { value: 'Tersedia', label: 'Tersedia' },
              { value: 'Dipinjam', label: 'Dipinjam' },
            ]}
          />
        </div>

        <div className="filter-group-right">
          <div className="text-meta" style={{ marginRight: '4px' }}>
            Kuota: <strong>{activeLoanCount} / {MAX_ACTIVE_LOANS}</strong>
          </div>

          {/* View Mode Switcher: Kotak-kotak (Grid) vs Tabel (List) */}
          <div className="view-mode-toggle">
            <button
              type="button"
              className={`view-mode-btn ${viewMode === 'grid' ? 'active' : ''}`}
              onClick={() => setViewMode('grid')}
              title="Tampilan Kotak / Kartu Unit"
            >
              <LayoutGrid size={15} />
              <span>Kotak</span>
            </button>
            <button
              type="button"
              className={`view-mode-btn ${viewMode === 'table' ? 'active' : ''}`}
              onClick={() => setViewMode('table')}
              title="Tampilan Tabel"
            >
              <List size={15} />
              <span>Tabel</span>
            </button>
          </div>
        </div>
      </div>

      {/* Unit Display: Grid vs Table */}
      {viewMode === 'grid' ? (
        filteredUnits.length === 0 ? (
          <EmptyState
            title="Tidak Ada Unit Tersedia"
            description="Tidak ada perlengkapan yang sesuai dengan filter pencarian Anda."
          />
        ) : (
          <div className="units-grid">
            {filteredUnits.map((unit) => (
              <UnitCard
                key={unit.id}
                unit={unit}
                categories={categories}
                action={renderActionButton(unit)}
              />
            ))}
          </div>
        )
      ) : (
        <DataTable
          columns={columns}
          data={filteredUnits}
          keyExtractor={(item) => item.id}
          emptyTitle="Tidak Ada Unit Tersedia"
          emptyDescription="Tidak ada perlengkapan yang sesuai dengan filter pencarian Anda."
        />
      )}

      {/* Confirm Borrow Modal */}
      <Modal
        isOpen={selectedUnitToBorrow !== null}
        onClose={() => setSelectedUnitToBorrow(null)}
        title="Konfirmasi Pengajuan Peminjaman"
        maxWidth="480px"
        footer={
          <>
            <SecondaryButton size="sm" onClick={() => setSelectedUnitToBorrow(null)}>
              Batal
            </SecondaryButton>
            <PrimaryButton size="sm" onClick={handleConfirmBorrow}>
              Konfirmasi Pinjam
            </PrimaryButton>
          </>
        }
      >
        {selectedUnitToBorrow && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
            {selectedUnitToBorrow.imageUrl && (
              <div
                style={{
                  width: '100%',
                  height: '140px',
                  borderRadius: 'var(--radius-sm)',
                  overflow: 'hidden',
                  border: '1px solid var(--border-color)',
                  position: 'relative',
                }}
              >
                <img
                  src={selectedUnitToBorrow.imageUrl}
                  alt={selectedUnitToBorrow.name}
                  style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                />
                <span className="unit-card-code-badge">{selectedUnitToBorrow.code}</span>
              </div>
            )}

            <p style={{ fontSize: '13.5px', color: 'var(--text-secondary)' }}>
              Pastikan Anda memahami dan mematuhi batas waktu peminjaman unit operasional karantina:
            </p>

            <div
              style={{
                backgroundColor: 'rgba(255, 255, 255, 0.02)',
                border: '1px solid var(--border-color)',
                borderRadius: 'var(--radius-md)',
                padding: '16px',
                display: 'flex',
                flexDirection: 'column',
                gap: '10px',
              }}
            >
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span className="text-meta">Unit Dipilih:</span>
                <span style={{ fontWeight: 600 }}>{selectedUnitToBorrow.name}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span className="text-meta">Kode Inventaris:</span>
                <span className="text-mono" style={{ fontWeight: 600 }}>
                  {selectedUnitToBorrow.code}
                </span>
              </div>
              <div
                style={{
                  borderTop: '1px solid var(--border-subtle)',
                  paddingTop: '8px',
                  display: 'flex',
                  justifyContent: 'space-between',
                  alignItems: 'center',
                }}
              >
                <span className="text-meta">Tanggal Pinjam:</span>
                <span className="text-mono">{todayFormatted}</span>
              </div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <span className="text-meta">Batas Tenggat (5 Hari):</span>
                <span
                  className="text-mono"
                  style={{ color: 'var(--color-accent)', fontWeight: 600 }}
                >
                  {dueDateFormatted}
                </span>
              </div>
            </div>

            <div
              style={{
                fontSize: '12px',
                color: 'var(--text-muted)',
                lineHeight: '1.4',
                padding: '0 4px',
              }}
            >
              Catatan: Keterlambatan pengembalian unit dikenakan denda pemeliharaan inventaris sebesar
              Rp 15.000 / hari per unit.
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
};
