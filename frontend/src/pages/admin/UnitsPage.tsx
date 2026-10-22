import React, { useState } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { PrimaryButton, SecondaryButton } from '../../components/common/Buttons';
import { SearchInput } from '../../components/common/SearchInput';
import { FilterSelect } from '../../components/common/FilterSelect';
import { StatusBadge } from '../../components/common/StatusBadge';
import { DataTable, type Column } from '../../components/common/DataTable';
import { Modal } from '../../components/common/Modal';
import { ConfirmDialog } from '../../components/common/ConfirmDialog';
import { FormField } from '../../components/common/FormField';
import { UnitCard } from '../../components/common/UnitCard';
import { EmptyState } from '../../components/common/EmptyState';
import type { Unit } from '../../types';
import { Plus, Edit2, Trash2, LayoutGrid, List } from 'lucide-react';

export const UnitsPage: React.FC = () => {
  const { units, categories, addUnit, updateUnit, deleteUnit } = useDeadlock();

  const [search, setSearch] = useState('');
  const [selectedCategoryFilter, setSelectedCategoryFilter] = useState('');
  const [selectedStatusFilter, setSelectedStatusFilter] = useState('');
  const [viewMode, setViewMode] = useState<'grid' | 'table'>('grid');

  // Modal states
  const [modalMode, setModalMode] = useState<'create' | 'edit' | null>(null);
  const [targetUnit, setTargetUnit] = useState<Unit | null>(null);

  const [formCode, setFormCode] = useState('');
  const [formName, setFormName] = useState('');
  const [formDescription, setFormDescription] = useState('');
  const [formCategoryIds, setFormCategoryIds] = useState<string[]>([]);
  const [formImageUrl, setFormImageUrl] = useState('');
  const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});

  const [deleteConfirmUnit, setDeleteConfirmUnit] = useState<Unit | null>(null);

  // Filtering
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

  const handleOpenCreate = () => {
    setTargetUnit(null);
    setFormCode(`UNIT-0${units.length + 1}`.replace('010', '10'));
    setFormName('');
    setFormDescription('');
    setFormCategoryIds([]);
    setFormImageUrl('');
    setFormErrors({});
    setModalMode('create');
  };

  const handleOpenEdit = (unit: Unit) => {
    setTargetUnit(unit);
    setFormCode(unit.code);
    setFormName(unit.name);
    setFormDescription(unit.description);
    setFormCategoryIds([...unit.categoryIds]);
    setFormImageUrl(unit.imageUrl || '');
    setFormErrors({});
    setModalMode('edit');
  };

  const toggleCategorySelection = (catId: string) => {
    setFormCategoryIds((prev) =>
      prev.includes(catId) ? prev.filter((id) => id !== catId) : [...prev, catId]
    );
    if (formErrors.categoryIds) {
      setFormErrors((prev) => {
        const next = { ...prev };
        delete next.categoryIds;
        return next;
      });
    }
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    const newErrors: { [key: string]: string } = {};

    if (!formCode.trim()) {
      newErrors.code = 'Kode unit wajib diisi.';
    }
    if (!formName.trim()) {
      newErrors.name = 'Nama unit wajib diisi.';
    }
    if (formCategoryIds.length === 0) {
      newErrors.categoryIds = 'Pilih minimal satu kategori.';
    }

    if (Object.keys(newErrors).length > 0) {
      setFormErrors(newErrors);
      return;
    }

    if (modalMode === 'create') {
      const res = addUnit({
        code: formCode,
        name: formName,
        description: formDescription,
        categoryIds: formCategoryIds,
        imageUrl: formImageUrl.trim() || undefined,
      });
      if (!res.success) {
        setFormErrors({ code: res.message || 'Gagal menyimpan unit.' });
        return;
      }
    } else if (modalMode === 'edit' && targetUnit) {
      const res = updateUnit(targetUnit.id, {
        code: formCode,
        name: formName,
        description: formDescription,
        categoryIds: formCategoryIds,
        imageUrl: formImageUrl.trim() || undefined,
      });
      if (!res.success) {
        setFormErrors({ code: res.message || 'Gagal memperbarui unit.' });
        return;
      }
    }

    setModalMode(null);
  };

  const renderAdminActions = (item: Unit) => (
    <div style={{ display: 'flex', gap: '8px', width: '100%', justifyContent: 'flex-end' }}>
      <button
        type="button"
        className="btn-secondary btn-sm"
        style={{ flex: 1 }}
        onClick={() => handleOpenEdit(item)}
      >
        <Edit2 size={13} />
        Edit
      </button>
      <button
        type="button"
        className="btn-secondary btn-sm"
        style={{
          flex: 1,
          color: item.status === 'Dipinjam' ? 'var(--text-muted)' : 'var(--color-danger)',
        }}
        onClick={() => setDeleteConfirmUnit(item)}
        title={item.status === 'Dipinjam' ? 'Unit sedang dipinjam' : 'Hapus unit'}
      >
        <Trash2 size={13} />
        Hapus
      </button>
    </div>
  );

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
      width: '180px',
      render: (item) => renderAdminActions(item),
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
      {/* Filter and Search Bar */}
      <div className="filter-bar">
        <div className="filter-group-left">
          <SearchInput
            value={search}
            onChange={setSearch}
            placeholder="Cari kode, nama, atau deskripsi..."
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
          <div className="text-meta">
            Total: <strong>{filteredUnits.length}</strong> unit
          </div>

          {/* View Mode Switcher */}
          <div className="view-mode-toggle">
            <button
              type="button"
              className={`view-mode-btn ${viewMode === 'grid' ? 'active' : ''}`}
              onClick={() => setViewMode('grid')}
              title="Tampilan Kotak / Kartu"
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

          <PrimaryButton icon={<Plus size={16} />} onClick={handleOpenCreate}>
            Tambah Unit
          </PrimaryButton>
        </div>
      </div>

      {/* Inventory Display: Grid vs Table */}
      {viewMode === 'grid' ? (
        filteredUnits.length === 0 ? (
          <EmptyState
            title="Tidak Ada Unit Ditemukan"
            description="Tidak ada data unit inventaris yang cocok dengan kriteria pencarian dan filter saat ini."
          />
        ) : (
          <div className="units-grid">
            {filteredUnits.map((unit) => (
              <UnitCard
                key={unit.id}
                unit={unit}
                categories={categories}
                action={renderAdminActions(unit)}
              />
            ))}
          </div>
        )
      ) : (
        <DataTable
          columns={columns}
          data={filteredUnits}
          keyExtractor={(item) => item.id}
          emptyTitle="Tidak Ada Unit Ditemukan"
          emptyDescription="Tidak ada data unit inventaris yang cocok dengan kriteria pencarian dan filter saat ini."
        />
      )}

      {/* Add / Edit Modal */}
      <Modal
        isOpen={modalMode !== null}
        onClose={() => setModalMode(null)}
        title={modalMode === 'create' ? 'Tambah Unit Inventaris' : 'Edit Unit Inventaris'}
        maxWidth="560px"
        footer={
          <>
            <SecondaryButton size="sm" onClick={() => setModalMode(null)}>
              Batal
            </SecondaryButton>
            <PrimaryButton size="sm" onClick={handleSave}>
              Simpan Data
            </PrimaryButton>
          </>
        }
      >
        <form onSubmit={handleSave} style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: '12px' }}>
            <FormField label="Kode Unit" required error={formErrors.code} hint="Harus unik">
              <input
                type="text"
                className={`input-control text-mono ${formErrors.code ? 'input-error' : ''}`}
                value={formCode}
                onChange={(e) => {
                  setFormCode(e.target.value);
                  if (formErrors.code) {
                    setFormErrors((prev) => {
                      const next = { ...prev };
                      delete next.code;
                      return next;
                    });
                  }
                }}
                placeholder="UNIT-011"
              />
            </FormField>

            <FormField label="Nama Unit" required error={formErrors.name}>
              <input
                type="text"
                className={`input-control ${formErrors.name ? 'input-error' : ''}`}
                value={formName}
                onChange={(e) => {
                  setFormName(e.target.value);
                  if (formErrors.name) {
                    setFormErrors((prev) => {
                      const next = { ...prev };
                      delete next.name;
                      return next;
                    });
                  }
                }}
                placeholder="Contoh: Tactical Machete 500"
              />
            </FormField>
          </div>

          <FormField
            label="Kategori (Multi-select)"
            required
            error={formErrors.categoryIds}
            hint="Satu unit dapat memiliki lebih dari satu kategori."
          >
            <div className="category-tags" style={{ marginTop: '4px' }}>
              {categories.map((cat) => {
                const isSelected = formCategoryIds.includes(cat.id);
                return (
                  <button
                    key={cat.id}
                    type="button"
                    className={`tag-option ${isSelected ? 'selected' : ''}`}
                    onClick={() => toggleCategorySelection(cat.id)}
                  >
                    {isSelected ? '✓ ' : '+ '}
                    {cat.name}
                  </button>
                );
              })}
            </div>
          </FormField>

          <FormField
            label="URL Gambar Unit"
            hint="Masukkan tautan gambar peralatan atau foto spesifikasi teknis."
          >
            <div style={{ display: 'flex', gap: '8px' }}>
              <input
                type="url"
                className="input-control"
                value={formImageUrl}
                onChange={(e) => setFormImageUrl(e.target.value)}
                placeholder="https://images.unsplash.com/..."
              />
            </div>
          </FormField>

          {/* Image Preview if provided */}
          {formImageUrl && (
            <div
              style={{
                width: '100%',
                height: '130px',
                borderRadius: 'var(--radius-sm)',
                overflow: 'hidden',
                border: '1px solid var(--border-color)',
                position: 'relative',
              }}
            >
              <img
                src={formImageUrl}
                alt="Preview"
                style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                onError={(e) => {
                  (e.target as HTMLElement).style.display = 'none';
                }}
              />
              <span className="unit-card-code-badge">Preview Gambar</span>
            </div>
          )}

          <FormField label="Deskripsi &amp; Spesifikasi">
            <textarea
              className="input-control"
              value={formDescription}
              onChange={(e) => setFormDescription(e.target.value)}
              placeholder="Catatan kondisi, kapasitas, kaliber, atau spesifikasi teknis unit..."
              rows={3}
            />
          </FormField>

          {modalMode === 'edit' && targetUnit && (
            <div
              style={{
                padding: '10px 14px',
                backgroundColor: 'rgba(255, 255, 255, 0.02)',
                border: '1px solid var(--border-color)',
                borderRadius: 'var(--radius-sm)',
                fontSize: '12px',
                color: 'var(--text-secondary)',
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
              }}
            >
              <span>Status Peminjaman Saat Ini:</span>
              <StatusBadge status={targetUnit.status} />
            </div>
          )}
        </form>
      </Modal>

      {/* Delete Confirmation Dialog */}
      <ConfirmDialog
        isOpen={deleteConfirmUnit !== null}
        onClose={() => setDeleteConfirmUnit(null)}
        onConfirm={() => {
          if (deleteConfirmUnit) {
            deleteUnit(deleteConfirmUnit.id);
          }
        }}
        title="Hapus Unit Inventaris"
        message={
          deleteConfirmUnit?.status === 'Dipinjam'
            ? `Unit "${deleteConfirmUnit.name}" (${deleteConfirmUnit.code}) saat ini berstatus DIPINJAM dan TIDAK DAPAT DIHAPUS sebelum dikembalikan ke gudang.`
            : `Apakah Anda yakin ingin menghapus unit "${deleteConfirmUnit?.name}" (${deleteConfirmUnit?.code}) dari inventaris gudang?`
        }
        confirmLabel={deleteConfirmUnit?.status === 'Dipinjam' ? 'Tutup' : 'Hapus Unit'}
        variant="danger"
      />
    </div>
  );
};
