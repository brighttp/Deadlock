import React, { useState } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { SearchInput } from '../../components/common/SearchInput';
import { DataTable, type Column } from '../../components/common/DataTable';
import { Modal } from '../../components/common/Modal';
import { ConfirmDialog } from '../../components/common/ConfirmDialog';
import { PrimaryButton, SecondaryButton } from '../../components/common/Buttons';
import { FormField } from '../../components/common/FormField';
import { StatCard } from '../../components/common/StatCard';
import type { User, Loan } from '../../types';
import {
  Eye,
  Trash2,
  Phone,
  Mail,
  MapPin,
  UserPlus,
  Edit2,
  Shield,
  Lock,
  User as UserIcon,
  Users,
  ShieldCheck,
  Boxes,
  LayoutGrid,
  List,
  Activity,
  CheckCircle,
} from 'lucide-react';
import { StatusBadge } from '../../components/common/StatusBadge';

export const MembersPage: React.FC = () => {
  const { users, loans, addMember, updateMember, deleteMember, getSurvivorActiveLoansCount } = useDeadlock();

  const [search, setSearch] = useState('');
  const [viewMode, setViewMode] = useState<'grid' | 'table'>('grid');
  const [detailUser, setDetailUser] = useState<User | null>(null);
  const [deleteConfirmUser, setDeleteConfirmUser] = useState<User | null>(null);

  // Create / Edit modal state
  const [modalMode, setModalMode] = useState<'create' | 'edit' | null>(null);
  const [targetUser, setTargetUser] = useState<User | null>(null);
  const [formName, setFormName] = useState('');
  const [formEmail, setFormEmail] = useState('');
  const [formPhone, setFormPhone] = useState('');
  const [formBasecamp, setFormBasecamp] = useState('');
  const [formPassword, setFormPassword] = useState('');
  const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});

  const handleOpenCreate = () => {
    setTargetUser(null);
    setFormName('');
    setFormEmail('');
    setFormPhone('');
    setFormBasecamp('');
    setFormPassword('');
    setFormErrors({});
    setModalMode('create');
  };

  const handleOpenEdit = (user: User) => {
    setTargetUser(user);
    setFormName(user.name);
    setFormEmail(user.email);
    setFormPhone(user.phone);
    setFormBasecamp(user.basecamp);
    setFormPassword('');
    setFormErrors({});
    setModalMode('edit');
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    const newErrors: { [key: string]: string } = {};

    if (!formName.trim()) {
      newErrors.name = 'Nama personel wajib diisi.';
    }
    if (!formEmail.trim()) {
      newErrors.email = 'Email personel wajib diisi.';
    } else if (!formEmail.includes('@') || !formEmail.includes('.')) {
      newErrors.email = 'Format email tidak valid.';
    }
    if (!formPhone.trim()) {
      newErrors.phone = 'Saluran radio / telepon lapangan wajib diisi.';
    }
    if (!formBasecamp.trim()) {
      newErrors.basecamp = 'Lokasi penempatan barak wajib diisi.';
    }
    if (modalMode === 'create' && formPassword.trim() && formPassword.trim().length < 6) {
      newErrors.password = 'Password minimal 6 karakter.';
    }

    if (Object.keys(newErrors).length > 0) {
      setFormErrors(newErrors);
      return;
    }

    if (modalMode === 'create') {
      const res = addMember({
        name: formName,
        email: formEmail,
        phone: formPhone,
        basecamp: formBasecamp,
        password: formPassword.trim() || 'password123',
        role: 'Survivor',
      });
      if (!res.success) {
        if (res.message?.toLowerCase().includes('email')) {
          setFormErrors({ email: res.message });
        }
        return;
      }
    } else if (modalMode === 'edit' && targetUser) {
      const res = updateMember(targetUser.id, {
        name: formName,
        email: formEmail,
        phone: formPhone,
        basecamp: formBasecamp,
      });
      if (!res.success) {
        if (res.message?.toLowerCase().includes('email')) {
          setFormErrors({ email: res.message });
        }
        return;
      }
    }

    setModalMode(null);
  };

  // Survivors only
  const survivors = users.filter((u) => u.role === 'Survivor');

  const filteredSurvivors = survivors.filter(
    (u) =>
      u.name.toLowerCase().includes(search.toLowerCase()) ||
      u.email.toLowerCase().includes(search.toLowerCase()) ||
      u.phone.toLowerCase().includes(search.toLowerCase()) ||
      u.basecamp.toLowerCase().includes(search.toLowerCase())
  );

  // Top Metrics Calculation
  const activePersonnel = survivors.filter((u) => getSurvivorActiveLoansCount(u.id) > 0);
  const activeUnitsBorrowed = loans.filter((l) => l.status === 'Aktif' || l.status === 'Terlambat').length;

  // Get loans for detail modal
  const getUserLoans = (userId: string): Loan[] => {
    return loans.filter((l) => l.survivorId === userId);
  };

  const columns: Column<User>[] = [
    {
      key: 'name',
      header: 'Nama Personel & Sektor',
      render: (u) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
          <div className="user-avatar" style={{ width: '38px', height: '38px', fontSize: '13px', border: '1.5px solid rgba(240, 178, 71, 0.5)' }}>
            {u.name.substring(0, 2).toUpperCase()}
          </div>
          <div>
            <div style={{ fontWeight: 700, color: 'var(--text-main)', fontSize: '14px' }}>{u.name}</div>
            <div className="text-meta" style={{ display: 'flex', alignItems: 'center', gap: '4px', fontSize: '11px' }}>
              <MapPin size={11} style={{ color: '#F0B247' }} />
              {u.basecamp}
            </div>
          </div>
        </div>
      ),
    },
    {
      key: 'email',
      header: 'Email & Saluran Radio',
      render: (u) => (
        <div>
          <div style={{ fontSize: '13px', color: 'var(--text-main)' }}>{u.email}</div>
          <div className="text-meta text-mono" style={{ fontSize: '11.5px', color: '#8C998A' }}>{u.phone}</div>
        </div>
      ),
    },
    {
      key: 'activeLoans',
      header: 'Pinjaman Aktif',
      render: (u) => {
        const count = getSurvivorActiveLoansCount(u.id);
        const percent = (count / 2) * 100;
        return (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '4px', maxWidth: '160px' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span
                style={{
                  fontFamily: 'var(--font-mono)',
                  fontWeight: 700,
                  fontSize: '13px',
                  color: count > 0 ? '#F0B247' : '#4ADE80',
                }}
              >
                {count} / 2 Unit
              </span>
              <span className="text-meta" style={{ fontSize: '10.5px' }}>
                {count === 0 ? 'Bebas Pinjam' : count === 2 ? 'Kuota Penuh' : '1 Slot Tersisa'}
              </span>
            </div>
            <div className="stat-meter-track" style={{ height: '4px' }}>
              <div
                className="stat-meter-fill"
                style={{
                  width: `${percent}%`,
                  background: count >= 2 ? '#E5A83B' : '#4ADE80',
                }}
              />
            </div>
          </div>
        );
      },
    },
    {
      key: 'status',
      header: 'Status Biometrik',
      render: () => (
        <span
          style={{
            display: 'inline-flex',
            alignItems: 'center',
            gap: '5px',
            fontSize: '11px',
            fontFamily: 'var(--font-mono)',
            padding: '3px 8px',
            borderRadius: 'var(--radius-xs)',
            background: 'rgba(74, 222, 128, 0.1)',
            border: '1px solid rgba(74, 222, 128, 0.35)',
            color: '#4ADE80',
            fontWeight: 700,
          }}
        >
          <span style={{ width: 6, height: 6, borderRadius: '50%', background: '#4ADE80' }} />
          NON-TERINFEKSI
        </span>
      ),
    },
    {
      key: 'actions',
      header: 'Aksi',
      align: 'right',
      render: (u) => {
        const activeCount = getSurvivorActiveLoansCount(u.id);
        return (
          <div style={{ display: 'flex', gap: '6px', justifyContent: 'flex-end' }}>
            <button
              type="button"
              className="btn-secondary btn-sm"
              onClick={() => setDetailUser(u)}
              title="Lihat Dossier Personel"
            >
              <Eye size={13} />
              Dossier
            </button>
            <button
              type="button"
              className="btn-secondary btn-sm"
              onClick={() => handleOpenEdit(u)}
              title="Perbarui data personel"
            >
              <Edit2 size={13} />
              Edit
            </button>
            <button
              type="button"
              className="btn-secondary btn-sm"
              style={{ color: activeCount > 0 ? 'var(--text-muted)' : 'var(--color-danger)' }}
              onClick={() => setDeleteConfirmUser(u)}
              title={activeCount > 0 ? 'Memiliki pinjaman aktif di gudang' : 'Hapus anggota'}
            >
              <Trash2 size={13} />
              Hapus
            </button>
          </div>
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
          label="Total Survivor Terdaftar"
          value={`${survivors.length} Personel`}
          subtext="Personel aktif di manifest Haven Sector 4"
          icon={<Users size={20} />}
          isAmber={true}
          progress={100}
        />
        <StatCard
          label="Personel Bertugas Luar"
          value={`${activePersonnel.length} Personel`}
          subtext="Membawa persenjataan & perlengkapan aktif"
          icon={<ShieldCheck size={20} />}
          isAmber={true}
          progress={survivors.length > 0 ? (activePersonnel.length / survivors.length) * 100 : 0}
        />
        <StatCard
          label="Unit Dipinjam Personel"
          value={`${activeUnitsBorrowed} Unit`}
          subtext="Total perbekalan yang beredar di lapangan"
          icon={<Boxes size={20} />}
          isAmber={true}
          progress={activeUnitsBorrowed > 0 ? 60 : 0}
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
            placeholder="Cari nama personel, email, nomor radio, atau barak..."
          />
        </div>

        <div className="filter-group-right">
          <div className="text-meta" style={{ marginRight: '6px' }}>
            Menampilkan <strong>{filteredSurvivors.length}</strong> dari {survivors.length} personel
          </div>

          {/* View Mode Toggle: Kotak vs Tabel */}
          <div className="view-mode-toggle">
            <button
              type="button"
              className={`view-mode-btn ${viewMode === 'grid' ? 'active' : ''}`}
              onClick={() => setViewMode('grid')}
              title="Tampilan Kotak Dossier Personel"
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

          <PrimaryButton icon={<UserPlus size={16} />} onClick={handleOpenCreate}>
            Tambah Personel
          </PrimaryButton>
        </div>
      </div>

      {/* =========================================
          CONTENT: GRID OR TABLE
          ========================================= */}
      {viewMode === 'grid' ? (
        filteredSurvivors.length === 0 ? (
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
            <Users size={40} style={{ color: '#F0B247', opacity: 0.6 }} />
            <h3 style={{ fontSize: '17px', fontWeight: 700 }}>Personel Tidak Ditemukan</h3>
            <p className="text-meta" style={{ maxWidth: '400px' }}>
              Tidak ada catatan survivor yang sesuai dengan kata kunci pencarian "{search}".
            </p>
          </div>
        ) : (
          <div className="member-grid">
            {filteredSurvivors.map((u) => {
              const activeCount = getSurvivorActiveLoansCount(u.id);
              const percent = (activeCount / 2) * 100;

              return (
                <div key={u.id} className="member-card">
                  {/* Member Card Header */}
                  <div className="member-card-header">
                    <div className="member-avatar-box">
                      {u.name.substring(0, 2).toUpperCase()}
                      <span
                        className="pulse"
                        style={{
                          position: 'absolute',
                          bottom: -2,
                          right: -2,
                          width: 10,
                          height: 10,
                          borderRadius: '50%',
                          background: '#4ADE80',
                          border: '2px solid #141914',
                        }}
                      />
                    </div>
                    <div className="member-card-info">
                      <h3 className="member-card-name">{u.name}</h3>
                      <div className="member-card-basecamp">
                        <MapPin size={12} style={{ color: '#F0B247' }} />
                        <span>{u.basecamp}</span>
                      </div>
                    </div>
                    <span
                      style={{
                        fontFamily: 'var(--font-mono)',
                        fontSize: '10px',
                        padding: '2px 7px',
                        borderRadius: 'var(--radius-xs)',
                        background: 'rgba(240, 178, 71, 0.1)',
                        border: '1px solid rgba(240, 178, 71, 0.35)',
                        color: '#F0B247',
                        fontWeight: 700,
                      }}
                    >
                      {u.id.toUpperCase()}
                    </span>
                  </div>

                  {/* Telemetry Information Box */}
                  <div className="member-telemetry-box">
                    <div className="member-telemetry-item">
                      <span className="member-telemetry-key">
                        <Mail size={12} /> Email
                      </span>
                      <span className="member-telemetry-val" style={{ fontSize: '11.5px' }}>
                        {u.email}
                      </span>
                    </div>

                    <div className="member-telemetry-item">
                      <span className="member-telemetry-key">
                        <Phone size={12} /> Frekuensi Radio
                      </span>
                      <span className="member-telemetry-val text-mono" style={{ fontSize: '11.5px', color: '#4ADE80' }}>
                        {u.phone}
                      </span>
                    </div>

                    <div className="member-telemetry-item">
                      <span className="member-telemetry-key">
                        <Activity size={12} /> Status Biometrik
                      </span>
                      <span style={{ color: '#4ADE80', fontWeight: 700, fontSize: '11px', display: 'flex', alignItems: 'center', gap: '4px' }}>
                        <CheckCircle size={11} /> NON-TERINFEKSI
                      </span>
                    </div>
                  </div>

                  {/* Loan Quota Meter */}
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontSize: '11.5px', color: '#8C998A' }}>Kuota Pinjaman Unit:</span>
                      <span
                        style={{
                          fontFamily: 'var(--font-mono)',
                          fontSize: '12.5px',
                          fontWeight: 700,
                          color: activeCount > 0 ? '#F0B247' : '#4ADE80',
                        }}
                      >
                        {activeCount} / 2 Unit {activeCount === 2 ? '(Penuh)' : ''}
                      </span>
                    </div>
                    <div className="stat-meter-track" style={{ height: '5px' }}>
                      <div
                        className="stat-meter-fill"
                        style={{
                          width: `${percent}%`,
                          background: activeCount >= 2 ? '#E5A83B' : '#4ADE80',
                        }}
                      />
                    </div>
                  </div>

                  {/* Card Actions Footer */}
                  <div className="member-card-footer">
                    <button
                      type="button"
                      className="btn-secondary btn-sm"
                      style={{ flex: 1, justifyContent: 'center' }}
                      onClick={() => setDetailUser(u)}
                    >
                      <Eye size={13} />
                      Detail Dossier
                    </button>
                    <button
                      type="button"
                      className="btn-secondary btn-sm"
                      onClick={() => handleOpenEdit(u)}
                      title="Edit Personel"
                    >
                      <Edit2 size={13} />
                      Edit
                    </button>
                    <button
                      type="button"
                      className="btn-secondary btn-sm"
                      style={{ color: activeCount > 0 ? 'var(--text-muted)' : 'var(--color-danger)' }}
                      onClick={() => setDeleteConfirmUser(u)}
                      title={activeCount > 0 ? 'Memiliki pinjaman aktif' : 'Hapus anggota'}
                    >
                      <Trash2 size={13} />
                    </button>
                  </div>
                </div>
              );
            })}
          </div>
        )
      ) : (
        <DataTable
          columns={columns}
          data={filteredSurvivors}
          keyExtractor={(u) => u.id}
          emptyTitle="Tidak Ada Anggota Ditemukan"
          emptyDescription="Belum ada survivor yang sesuai dengan filter pencarian Anda."
        />
      )}

      {/* =========================================
          MEMBER DETAIL MODAL
          ========================================= */}
      <Modal
        isOpen={detailUser !== null}
        onClose={() => setDetailUser(null)}
        title="Dossier Personel Survivor"
        maxWidth="560px"
        footer={
          <SecondaryButton size="sm" onClick={() => setDetailUser(null)}>
            Tutup Dossier
          </SecondaryButton>
        }
      >
        {detailUser && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            <div
              style={{
                display: 'flex',
                alignItems: 'center',
                gap: '14px',
                padding: '16px',
                backgroundColor: 'rgba(240, 178, 71, 0.05)',
                border: '1px solid rgba(240, 178, 71, 0.35)',
                borderLeft: '4px solid #F0B247',
                borderRadius: 'var(--radius-md)',
              }}
            >
              <div
                className="user-avatar"
                style={{
                  width: '52px',
                  height: '52px',
                  fontSize: '18px',
                  border: '2px solid rgba(240, 178, 71, 0.6)',
                  boxShadow: '0 0 14px rgba(240, 178, 71, 0.25)',
                }}
              >
                {detailUser.name.substring(0, 2).toUpperCase()}
              </div>
              <div style={{ flex: 1 }}>
                <div style={{ fontSize: '17px', fontWeight: 700, color: 'var(--text-main)' }}>{detailUser.name}</div>
                <div style={{ display: 'flex', gap: '8px', alignItems: 'center', marginTop: '4px' }}>
                  <StatusBadge status={detailUser.role} />
                  <span className="text-meta text-mono">ID: {detailUser.id}</span>
                </div>
              </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
              <div
                style={{
                  padding: '10px 12px',
                  backgroundColor: 'var(--bg-input)',
                  border: '1px solid rgba(240, 178, 71, 0.25)',
                  borderRadius: 'var(--radius-sm)',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '8px',
                  fontSize: '13px',
                }}
              >
                <Mail size={15} style={{ color: '#F0B247' }} />
                <span>{detailUser.email}</span>
              </div>

              <div
                style={{
                  padding: '10px 12px',
                  backgroundColor: 'var(--bg-input)',
                  border: '1px solid rgba(240, 178, 71, 0.25)',
                  borderRadius: 'var(--radius-sm)',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '8px',
                  fontSize: '13px',
                }}
              >
                <Phone size={15} style={{ color: '#4ADE80' }} />
                <span className="text-mono">{detailUser.phone}</span>
              </div>
            </div>

            <div
              style={{
                padding: '10px 12px',
                backgroundColor: 'var(--bg-input)',
                border: '1px solid rgba(240, 178, 71, 0.25)',
                borderRadius: 'var(--radius-sm)',
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                fontSize: '13px',
              }}
            >
              <MapPin size={15} style={{ color: '#F0B247' }} />
              <span>Lokasi Penempatan: <strong>{detailUser.basecamp}</strong></span>
            </div>

            {/* List of loans for this user */}
            <div>
              <div
                style={{
                  fontSize: '13px',
                  fontWeight: 700,
                  marginBottom: '10px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'space-between',
                }}
              >
                <span style={{ color: '#F0B247' }}>Catatan Transaksi Peminjaman Unit</span>
                <span className="text-meta">
                  Aktif: <strong>{getSurvivorActiveLoansCount(detailUser.id)} / 2</strong>
                </span>
              </div>

              {getUserLoans(detailUser.id).length === 0 ? (
                <div
                  style={{
                    padding: '16px',
                    textAlign: 'center',
                    color: 'var(--text-muted)',
                    fontSize: '12.5px',
                    backgroundColor: 'rgba(255, 255, 255, 0.02)',
                    borderRadius: 'var(--radius-sm)',
                    border: '1px solid rgba(255, 255, 255, 0.05)',
                  }}
                >
                  Belum pernah melakukan transaksi peminjaman perbekalan.
                </div>
              ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                  {getUserLoans(detailUser.id).map((loan) => (
                    <div
                      key={loan.id}
                      style={{
                        padding: '11px 14px',
                        backgroundColor: 'rgba(255, 255, 255, 0.02)',
                        border: '1px solid rgba(240, 178, 71, 0.25)',
                        borderLeft: loan.status === 'Terlambat' ? '3px solid #EF4444' : '3px solid #F0B247',
                        borderRadius: 'var(--radius-sm)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                      }}
                    >
                      <div>
                        <div style={{ fontWeight: 600, fontSize: '13px', color: 'var(--text-main)' }}>{loan.unitName}</div>
                        <div className="text-meta text-mono" style={{ fontSize: '11px', marginTop: '2px' }}>
                          {loan.unitCode} &bull; Pinjam: {loan.loanDate} &bull; Tenggat: {loan.dueDate}
                        </div>
                      </div>
                      <StatusBadge status={loan.status} />
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        )}
      </Modal>

      {/* =========================================
          DELETE CONFIRMATION DIALOG
          ========================================= */}
      <ConfirmDialog
        isOpen={deleteConfirmUser !== null}
        onClose={() => setDeleteConfirmUser(null)}
        onConfirm={() => {
          if (deleteConfirmUser) {
            deleteMember(deleteConfirmUser.id);
          }
        }}
        title="Hapus Data Personel"
        message={
          deleteConfirmUser && getSurvivorActiveLoansCount(deleteConfirmUser.id) > 0
            ? `Survivor "${deleteConfirmUser.name}" saat ini memiliki ${getSurvivorActiveLoansCount(
                deleteConfirmUser.id
              )} PINJAMAN AKTIF dan TIDAK DAPAT DIHAPUS sebelum unit dikembalikan ke gudang.`
          : `Apakah Anda yakin ingin menghapus catatan keanggotaan survivor "${deleteConfirmUser?.name}" dari sistem?`
        }
        confirmLabel={
          deleteConfirmUser && getSurvivorActiveLoansCount(deleteConfirmUser.id) > 0
            ? 'Tutup'
            : 'Hapus Personel'
        }
        danger={true}
      />

      {/* =========================================
          CREATE / EDIT MEMBER MODAL
          ========================================= */}
      <Modal
        isOpen={modalMode !== null}
        onClose={() => setModalMode(null)}
        title={modalMode === 'create' ? 'Registrasi Personel Survivor Baru' : 'Perbarui Data Personel'}
        maxWidth="540px"
        footer={
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '10px' }}>
            <SecondaryButton size="sm" onClick={() => setModalMode(null)}>
              Batal
            </SecondaryButton>
            <PrimaryButton size="sm" onClick={handleSave}>
              {modalMode === 'create' ? 'Daftarkan Personel' : 'Simpan Perubahan'}
            </PrimaryButton>
          </div>
        }
      >
        <form onSubmit={handleSave} style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
          <div
            style={{
              padding: '10px 14px',
              background: 'rgba(240, 178, 71, 0.08)',
              border: '1px solid rgba(240, 178, 71, 0.3)',
              borderRadius: 'var(--radius-sm)',
              display: 'flex',
              alignItems: 'center',
              gap: '10px',
              fontSize: '12px',
              color: '#DFC07E',
            }}
          >
            <Shield size={16} style={{ color: '#F0B247', flexShrink: 0 }} />
            <span>
              {modalMode === 'create'
                ? 'Personel yang didaftarkan akan langsung terdaftar di manifest Haven Sector 4 dan dapat meminjam logistik.'
                : `Memperbarui kredensial manifest untuk ${targetUser?.name} (${targetUser?.id}).`}
            </span>
          </div>

          <FormField label="Nama Lengkap Personel" required error={formErrors.name}>
            <div className="tactical-input-wrap">
              <UserIcon size={15} className="tactical-input-icon" />
              <input
                type="text"
                className={`input-control ${formErrors.name ? 'input-error' : ''}`}
                value={formName}
                placeholder="Contoh: Daryl Dixon"
                onChange={(e) => {
                  setFormName(e.target.value);
                  if (formErrors.name) setFormErrors((p) => ({ ...p, name: '' }));
                }}
              />
            </div>
          </FormField>

          <FormField
            label="Alamat Email Kredensial"
            required
            error={formErrors.email}
            hint="Digunakan untuk identifikasi terminal &amp; sesi login"
          >
            <div className="tactical-input-wrap">
              <Mail size={15} className="tactical-input-icon" />
              <input
                type="email"
                className={`input-control ${formErrors.email ? 'input-error' : ''}`}
                value={formEmail}
                placeholder="Contoh: daryl@haven.org"
                onChange={(e) => {
                  setFormEmail(e.target.value);
                  if (formErrors.email) setFormErrors((p) => ({ ...p, email: '' }));
                }}
              />
            </div>
          </FormField>

          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '12px' }}>
            <FormField
              label="Saluran Radio / Telepon"
              required
              error={formErrors.phone}
            >
              <div className="tactical-input-wrap">
                <Phone size={15} className="tactical-input-icon" />
                <input
                  type="text"
                  className={`input-control text-mono ${formErrors.phone ? 'input-error' : ''}`}
                  value={formPhone}
                  placeholder="0819-xxxx-xxxx"
                  onChange={(e) => {
                    setFormPhone(e.target.value);
                    if (formErrors.phone) setFormErrors((p) => ({ ...p, phone: '' }));
                  }}
                />
              </div>
            </FormField>

            <FormField
              label="Lokasi Penempatan Barak"
              required
              error={formErrors.basecamp}
            >
              <div className="tactical-input-wrap">
                <MapPin size={15} className="tactical-input-icon" />
                <input
                  type="text"
                  className={`input-control ${formErrors.basecamp ? 'input-error' : ''}`}
                  value={formBasecamp}
                  placeholder="Contoh: Barak Survivor 3B"
                  onChange={(e) => {
                    setFormBasecamp(e.target.value);
                    if (formErrors.basecamp) setFormErrors((p) => ({ ...p, basecamp: '' }));
                  }}
                />
              </div>
            </FormField>
          </div>

          {modalMode === 'create' && (
            <FormField
              label="Password Akses Terminal Awal"
              error={formErrors.password}
              hint="Opsional. Default: password123 jika dikosongkan."
            >
              <div className="tactical-input-wrap">
                <Lock size={15} className="tactical-input-icon" />
                <input
                  type="password"
                  className={`input-control ${formErrors.password ? 'input-error' : ''}`}
                  value={formPassword}
                  placeholder="Minimal 6 karakter (default: password123)"
                  onChange={(e) => {
                    setFormPassword(e.target.value);
                    if (formErrors.password) setFormErrors((p) => ({ ...p, password: '' }));
                  }}
                />
              </div>
            </FormField>
          )}
        </form>
      </Modal>
    </div>
  );
};
