import React, { useState } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { PrimaryButton, SecondaryButton } from '../../components/common/Buttons';
import { SearchInput } from '../../components/common/SearchInput';
import { DataTable, type Column } from '../../components/common/DataTable';
import { Modal } from '../../components/common/Modal';
import { ConfirmDialog } from '../../components/common/ConfirmDialog';
import { FormField } from '../../components/common/FormField';
import { StatCard } from '../../components/common/StatCard';
import type { Category } from '../../types';
import {
  Plus,
  Edit2,
  Trash2,
  Layers,
  Boxes,
  Sparkles,
  LayoutGrid,
  List,
  Swords,
  Crosshair,
  Compass,
  HeartPulse,
  Radio,
  Truck,
  Tag,
  Package,
} from 'lucide-react';

export const CategoriesPage: React.FC = () => {
  const { categories, units, addCategory, updateCategory, deleteCategory } = useDeadlock();

  const [search, setSearch] = useState('');
  const [viewMode, setViewMode] = useState<'grid' | 'table'>('grid');
  const [modalMode, setModalMode] = useState<'create' | 'edit' | null>(null);
  const [targetCategory, setTargetCategory] = useState<Category | null>(null);
  const [categoryName, setCategoryName] = useState('');
  const [formError, setFormError] = useState('');

  const [deleteConfirmCat, setDeleteConfirmCat] = useState<Category | null>(null);

  // Filtered categories
  const filteredCategories = categories.filter((c) =>
    c.name.toLowerCase().includes(search.toLowerCase())
  );

  const getUnitCountForCategory = (catId: string) => {
    return units.filter((u) => u.categoryIds.includes(catId)).length;
  };

  const getUnitsForCategory = (catId: string) => {
    return units.filter((u) => u.categoryIds.includes(catId));
  };

  // Determine Category Tactical Icon
  const getCategoryIcon = (name: string, size = 18) => {
    const lower = name.toLowerCase();
    if (lower.includes('dekat') || lower.includes('melee') || lower.includes('pedang') || lower.includes('tajam')) {
      return <Swords size={size} />;
    }
    if (lower.includes('jauh') || lower.includes('senapan') || lower.includes('sniper') || lower.includes('busur')) {
      return <Crosshair size={size} />;
    }
    if (lower.includes('survival') || lower.includes('ransel') || lower.includes('tenda')) {
      return <Compass size={size} />;
    }
    if (lower.includes('medis') || lower.includes('obat') || lower.includes('perban') || lower.includes('darah')) {
      return <HeartPulse size={size} />;
    }
    if (lower.includes('komunikasi') || lower.includes('radio') || lower.includes('sinyal')) {
      return <Radio size={size} />;
    }
    if (lower.includes('kendaraan') || lower.includes('motor') || lower.includes('mobil') || lower.includes('truk')) {
      return <Truck size={size} />;
    }
    return <Tag size={size} />;
  };

  // Top Statistics
  const totalCategories = categories.length;
  const totalUnits = units.length;
  const topCategory = categories.reduce<{ cat: Category | null; count: number }>(
    (acc, curr) => {
      const c = getUnitCountForCategory(curr.id);
      return c > acc.count ? { cat: curr, count: c } : acc;
    },
    { cat: null, count: 0 }
  );

  const handleOpenCreate = () => {
    setTargetCategory(null);
    setCategoryName('');
    setFormError('');
    setModalMode('create');
  };

  const handleOpenEdit = (cat: Category) => {
    setTargetCategory(cat);
    setCategoryName(cat.name);
    setFormError('');
    setModalMode('edit');
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (!categoryName.trim()) {
      setFormError('Nama kategori wajib diisi.');
      return;
    }

    if (modalMode === 'create') {
      const res = addCategory(categoryName);
      if (!res.success) {
        setFormError(res.message || 'Gagal menambahkan kategori.');
        return;
      }
    } else if (modalMode === 'edit' && targetCategory) {
      const res = updateCategory(targetCategory.id, categoryName);
      if (!res.success) {
        setFormError(res.message || 'Gagal memperbarui kategori.');
        return;
      }
    }

    setModalMode(null);
  };

  const columns: Column<Category>[] = [
    {
      key: 'name',
      header: 'Nama Kategori',
      render: (cat) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
          <div className="category-icon-box" style={{ width: '34px', height: '34px' }}>
            {getCategoryIcon(cat.name, 16)}
          </div>
          <div>
            <div style={{ fontWeight: 700, color: 'var(--text-main)', fontSize: '14px' }}>{cat.name}</div>
            <div className="text-meta text-mono" style={{ fontSize: '10.5px' }}>ID: {cat.id}</div>
          </div>
        </div>
      ),
    },
    {
      key: 'unitsCount',
      header: 'Unit Terdaftar',
      render: (cat) => {
        const count = getUnitCountForCategory(cat.id);
        const percent = totalUnits > 0 ? Math.round((count / totalUnits) * 100) : 0;
        return (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', maxWidth: '180px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontWeight: 700, color: '#4ADE80', fontFamily: 'var(--font-mono)' }}>
                {count} Unit
              </span>
              <span className="text-meta" style={{ fontSize: '11px' }}>{percent}% inventaris</span>
            </div>
            <div className="stat-meter-track" style={{ height: '4px' }}>
              <div
                className="stat-meter-fill"
                style={{ width: `${percent}%` }}
              />
            </div>
          </div>
        );
      },
    },
    {
      key: 'preview',
      header: 'Contoh Unit Terdaftar',
      render: (cat) => {
        const catUnits = getUnitsForCategory(cat.id);
        if (catUnits.length === 0) {
          return <span className="text-meta" style={{ fontStyle: 'italic' }}>Belum ada unit terhubung</span>;
        }
        return (
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '4px', maxWidth: '380px' }}>
            {catUnits.slice(0, 2).map((u) => (
              <span key={u.id} className="category-unit-tag">
                {u.name}
              </span>
            ))}
            {catUnits.length > 2 && (
              <span className="category-unit-tag" style={{ color: '#F0B247', borderColor: 'rgba(240, 178, 71, 0.4)' }}>
                +{catUnits.length - 2} lainnya
              </span>
            )}
          </div>
        );
      },
    },
    {
      key: 'actions',
      header: 'Aksi',
      align: 'right',
      render: (cat) => (
        <div style={{ display: 'flex', gap: '8px', justifyContent: 'flex-end' }}>
          <button
            type="button"
            className="btn-secondary btn-sm"
            onClick={() => handleOpenEdit(cat)}
            title="Edit Kategori"
          >
            <Edit2 size={13} />
            Edit
          </button>
          <button
            type="button"
            className="btn-secondary btn-sm"
            style={{ color: 'var(--color-danger)' }}
            onClick={() => setDeleteConfirmCat(cat)}
            title="Hapus Kategori"
          >
            <Trash2 size={13} />
            Hapus
          </button>
        </div>
      ),
    },
  ];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: '22px' }}>
      {/* =========================================
          TOP TACTICAL METRIC HUD
          ========================================= */}
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '18px' }}>
        <StatCard
          label="Total Kategori Aktif"
          value={`${totalCategories} Kategori`}
          subtext="Klasifikasi logistik & perbekalan karantina"
          icon={<Layers size={20} />}
          isAmber={true}
          progress={100}
        />
        <StatCard
          label="Total Unit Terinventaris"
          value={`${totalUnits} Unit`}
          subtext="Total seluruh persenjataan dan perbekalan"
          icon={<Boxes size={20} />}
          isAmber={true}
          progress={totalUnits > 0 ? 80 : 0}
        />
        <StatCard
          label="Kategori Terbanyak"
          value={topCategory.cat ? topCategory.cat.name : '-'}
          subtext={topCategory.cat ? `${topCategory.count} unit terhubung dalam posko` : 'Belum ada data'}
          icon={<Sparkles size={20} />}
          isAmber={true}
          progress={totalUnits > 0 ? (topCategory.count / totalUnits) * 100 : 0}
        />
      </div>

      {/* =========================================
          FILTER, SEARCH & VIEW SWITCHER CONTROL DECK
          ========================================= */}
      <div className="filter-bar">
        <div className="filter-group-left">
          <SearchInput
            value={search}
            onChange={setSearch}
            placeholder="Cari nama kategori logistik..."
          />
        </div>

        <div className="filter-group-right">
          <div className="text-meta" style={{ marginRight: '6px' }}>
            Menampilkan <strong>{filteredCategories.length}</strong> dari {categories.length} kategori
          </div>

          {/* View Mode Toggle: Kotak vs Tabel */}
          <div className="view-mode-toggle">
            <button
              type="button"
              className={`view-mode-btn ${viewMode === 'grid' ? 'active' : ''}`}
              onClick={() => setViewMode('grid')}
              title="Tampilan Kotak Taktis"
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
            Tambah Kategori
          </PrimaryButton>
        </div>
      </div>

      {/* =========================================
          CONTENT: GRID OR TABLE
          ========================================= */}
      {viewMode === 'grid' ? (
        filteredCategories.length === 0 ? (
          <div
            className="tactical-card"
            style={{
              padding: '40px',
              textAlign: 'center',
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: '12px',
            }}
          >
            <Package size={40} style={{ color: '#F0B247', opacity: 0.6 }} />
            <h3 style={{ fontSize: '17px', fontWeight: 700 }}>Kategori Tidak Ditemukan</h3>
            <p className="text-meta" style={{ maxWidth: '400px' }}>
              Tidak ada klasifikasi perbekalan yang sesuai dengan kata kunci "{search}".
            </p>
          </div>
        ) : (
          <div className="category-grid">
            {filteredCategories.map((cat) => {
              const catUnits = getUnitsForCategory(cat.id);
              const count = catUnits.length;
              const percent = totalUnits > 0 ? Math.round((count / totalUnits) * 100) : 0;

              return (
                <div key={cat.id} className="category-card">
                  {/* Card Header */}
                  <div className="category-card-header">
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                      <div className="category-icon-box">
                        {getCategoryIcon(cat.name, 22)}
                      </div>
                      <div>
                        <h3 className="category-card-title">{cat.name}</h3>
                        <div className="category-card-sub">KODE: {cat.id.toUpperCase()}</div>
                      </div>
                    </div>
                  </div>

                  {/* Meter Progress */}
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontSize: '12px', color: '#8C998A' }}>Kapasitas Inventaris:</span>
                      <span style={{ fontFamily: 'var(--font-mono)', fontSize: '13px', fontWeight: 700, color: '#4ADE80' }}>
                        {count} Unit ({percent}%)
                      </span>
                    </div>
                    <div className="stat-meter-track" style={{ height: '6px' }}>
                      <div
                        className="stat-meter-fill"
                        style={{ width: `${percent}%` }}
                      />
                    </div>
                  </div>

                  {/* Units Preview Box */}
                  <div className="category-units-preview">
                    <span className="category-preview-label">Contoh Unit Terdaftar:</span>
                    {count === 0 ? (
                      <span className="text-meta" style={{ fontSize: '12px', fontStyle: 'italic' }}>
                        Belum ada unit terhubung ke kategori ini.
                      </span>
                    ) : (
                      <div className="category-units-tags">
                        {catUnits.slice(0, 3).map((u) => (
                          <span key={u.id} className="category-unit-tag">
                            {u.name}
                          </span>
                        ))}
                        {count > 3 && (
                          <span
                            className="category-unit-tag"
                            style={{ color: '#F0B247', borderColor: 'rgba(240, 178, 71, 0.4)', fontWeight: 700 }}
                          >
                            +{count - 3} lainnya
                          </span>
                        )}
                      </div>
                    )}
                  </div>

                  {/* Card Actions Footer */}
                  <div className="category-card-footer">
                    <div className="text-meta text-mono" style={{ fontSize: '11px' }}>
                      Sektor 4 &bull; Terverifikasi
                    </div>
                    <div style={{ display: 'flex', gap: '6px' }}>
                      <button
                        type="button"
                        className="btn-secondary btn-sm"
                        onClick={() => handleOpenEdit(cat)}
                      >
                        <Edit2 size={13} />
                        Edit
                      </button>
                      <button
                        type="button"
                        className="btn-secondary btn-sm"
                        style={{ color: 'var(--color-danger)' }}
                        onClick={() => setDeleteConfirmCat(cat)}
                      >
                        <Trash2 size={13} />
                        Hapus
                      </button>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )
      ) : (
        <DataTable
          columns={columns}
          data={filteredCategories}
          keyExtractor={(c) => c.id}
          emptyTitle="Kategori Tidak Ditemukan"
          emptyDescription="Belum ada data kategori atau tidak ada yang sesuai dengan kata kunci pencarian Anda."
        />
      )}

      {/* =========================================
          ADD / EDIT MODAL
          ========================================= */}
      <Modal
        isOpen={modalMode !== null}
        onClose={() => setModalMode(null)}
        title={modalMode === 'create' ? 'Tambah Kategori Logistik' : 'Edit Kategori Logistik'}
        footer={
          <>
            <SecondaryButton size="sm" onClick={() => setModalMode(null)}>
              Batal
            </SecondaryButton>
            <PrimaryButton size="sm" onClick={handleSave}>
              Simpan Perubahan
            </PrimaryButton>
          </>
        }
      >
        <form onSubmit={handleSave}>
          <FormField label="Nama Kategori Logistik" required error={formError}>
            <input
              type="text"
              className={`input-control ${formError ? 'input-error' : ''}`}
              value={categoryName}
              onChange={(e) => {
                setCategoryName(e.target.value);
                setFormError('');
              }}
              placeholder="Contoh: Amunisi &amp; Perlengkapan Taktis"
              autoFocus
            />
          </FormField>
          <div className="text-meta" style={{ marginTop: '8px', fontSize: '11.5px' }}>
            Kategori ini akan langsung tersedia sebagai opsi filter dan penugasan perlengkapan survivor di gudang.
          </div>
        </form>
      </Modal>

      {/* =========================================
          DELETE CONFIRMATION DIALOG
          ========================================= */}
      <ConfirmDialog
        isOpen={deleteConfirmCat !== null}
        onClose={() => setDeleteConfirmCat(null)}
        title="Konfirmasi Hapus Kategori"
        message={`Apakah Anda yakin ingin menghapus kategori "${deleteConfirmCat?.name}"? Unit yang terhubung ke kategori ini mungkin kehilangan klasifikasi kelompoknya.`}
        confirmLabel="Ya, Hapus Kategori"
        danger={true}
        onConfirm={() => {
          if (deleteConfirmCat) {
            deleteCategory(deleteConfirmCat.id);
            setDeleteConfirmCat(null);
          }
        }}
      />
    </div>
  );
};
