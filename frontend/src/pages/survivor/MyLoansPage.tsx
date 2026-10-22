import React, { useState } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { SearchInput } from '../../components/common/SearchInput';
import { FilterSelect } from '../../components/common/FilterSelect';
import { StatusBadge } from '../../components/common/StatusBadge';
import { DataTable, type Column } from '../../components/common/DataTable';
import { PrimaryButton } from '../../components/common/Buttons';
import type { Loan } from '../../types';
import { Info, PackageSearch } from 'lucide-react';

export const MyLoansPage: React.FC = () => {
  const { currentUser, loans, units, setCurrentPage } = useDeadlock();

  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  if (!currentUser) return null;

  // Filter only current user's loans
  const myLoans = loans.filter((l) => l.survivorId === currentUser.id);

  const filteredLoans = myLoans.filter((l) => {
    const matchesSearch =
      l.unitName.toLowerCase().includes(search.toLowerCase()) ||
      l.unitCode.toLowerCase().includes(search.toLowerCase());

    const matchesStatus = !statusFilter || l.status === statusFilter;

    return matchesSearch && matchesStatus;
  });

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
                width: '46px',
                height: '46px',
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
                <PackageSearch size={20} style={{ color: 'var(--color-accent)', opacity: 0.7 }} />
              )}
            </div>
            <div>
              <div style={{ fontWeight: 600, color: 'var(--text-main)', fontSize: '13.5px' }}>
                {item.unitName}
              </div>
              <div className="text-meta">ID Pinjaman: {item.id}</div>
            </div>
          </div>
        );
      },
    },
    {
      key: 'unitCode',
      header: 'Kode Unit',
      width: '130px',
      render: (item) => <span className="text-mono" style={{ fontWeight: 600 }}>{item.unitCode}</span>,
    },
    {
      key: 'loanDate',
      header: 'Tanggal Pinjam',
      width: '140px',
      render: (item) => <span className="text-mono">{item.loanDate}</span>,
    },
    {
      key: 'dueDate',
      header: 'Tenggat Waktu',
      width: '140px',
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
      key: 'returnDate',
      header: 'Tanggal Kembali',
      width: '140px',
      render: (item) =>
        item.returnDate ? (
          <span className="text-mono">{item.returnDate}</span>
        ) : (
          <span className="text-meta">-</span>
        ),
    },
    {
      key: 'status',
      header: 'Status',
      width: '120px',
      render: (item) => <StatusBadge status={item.status} />,
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      {/* Admin Contact Notice */}
      <div
        style={{
          backgroundColor: 'rgba(240, 178, 71, 0.07)',
          border: '1px solid rgba(240, 178, 71, 0.35)',
          borderLeft: '4px solid #F0B247',
          borderRadius: 'var(--radius-md)',
          padding: '12px 18px',
          display: 'flex',
          alignItems: 'center',
          gap: '12px',
          fontSize: '13.5px',
          color: 'var(--text-main)',
          boxShadow: '0 4px 16px rgba(0, 0, 0, 0.35), 0 0 14px rgba(240, 178, 71, 0.1)',
        }}
      >
        <Info size={18} style={{ color: '#F0B247', flexShrink: 0 }} />
        <span>
          <strong>Informasi Pengembalian:</strong> Hubungi Admin (Komandan Gudang) untuk
          mengembalikan unit fisik. Pengembalian akan diproses dan diverifikasi di pos komando.
        </span>
      </div>

      {/* Search and Filters */}
      <div className="filter-bar">
        <div className="filter-group-left">
          <SearchInput
            value={search}
            onChange={setSearch}
            placeholder="Cari nama atau kode unit..."
          />
          <FilterSelect
            value={statusFilter}
            onChange={setStatusFilter}
            placeholder="Semua Status"
            options={[
              { value: 'Aktif', label: 'Aktif' },
              { value: 'Terlambat', label: 'Terlambat' },
              { value: 'Selesai', label: 'Selesai' },
            ]}
          />
        </div>
        <div className="text-meta">
          Total: {filteredLoans.length} catatan peminjaman
        </div>
      </div>

      {/* Table */}
      <DataTable
        columns={columns}
        data={filteredLoans}
        keyExtractor={(item) => item.id}
        emptyTitle="Tidak Ada Pinjaman Tercatat"
        emptyDescription="Anda belum memiliki riwayat peminjaman unit logistik."
      />

      {myLoans.length === 0 && (
        <div style={{ display: 'flex', justifyContent: 'center', marginTop: '-10px' }}>
          <PrimaryButton
            icon={<PackageSearch size={16} />}
            onClick={() => setCurrentPage('survivor-catalog')}
          >
            Buka Katalog Unit
          </PrimaryButton>
        </div>
      )}
    </div>
  );
};
