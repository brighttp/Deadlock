import React, { useState, useEffect } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { FormField } from '../../components/common/FormField';
import { PrimaryButton } from '../../components/common/Buttons';
import { StatusBadge } from '../../components/common/StatusBadge';
import {
  User,
  Mail,
  Phone,
  MapPin,
  Lock,
  KeyRound,
  Shield,
  ShieldCheck,
  Save,
  CheckCircle2,
  Radio,
  Activity,
  BadgeCheck,
  Eye,
  EyeOff,
} from 'lucide-react';

export const ProfilePage: React.FC = () => {
  const { currentUser, updateProfile, changePassword, loans } = useDeadlock();

  // Profile data state
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [basecamp, setBasecamp] = useState('');
  const [profileErrors, setProfileErrors] = useState<{ [key: string]: string }>({});

  // Password data state
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [passwordErrors, setPasswordErrors] = useState<{ [key: string]: string }>({});

  useEffect(() => {
    if (currentUser) {
      setName(currentUser.name);
      setEmail(currentUser.email);
      setPhone(currentUser.phone);
      setBasecamp(currentUser.basecamp);
    }
  }, [currentUser]);

  if (!currentUser) return null;

  const isAdmin = currentUser.role === 'Admin';

  // Calculate live personnel stats from actual loan records
  const userLoans = loans.filter((l) => l.survivorId === currentUser.id);
  const activeLoansCount = userLoans.filter((l) => l.status === 'Aktif' || l.status === 'Terlambat').length;
  const completedLoansCount = userLoans.filter((l) => l.status === 'Selesai').length;
  const overdueLoansCount = userLoans.filter((l) => l.status === 'Terlambat').length;

  const handleUpdateProfile = (e: React.FormEvent) => {
    e.preventDefault();
    const errors: { [key: string]: string } = {};

    if (!name.trim()) errors.name = 'Nama lengkap wajib diisi.';
    if (!phone.trim()) errors.phone = 'Nomor telepon/radio wajib diisi.';
    if (!basecamp.trim()) errors.basecamp = 'Lokasi barak/basecamp wajib diisi.';

    if (Object.keys(errors).length > 0) {
      setProfileErrors(errors);
      return;
    }

    setProfileErrors({});
    updateProfile({
      name: name.trim(),
      phone: phone.trim(),
      basecamp: basecamp.trim(),
    });
  };

  const handleChangePassword = (e: React.FormEvent) => {
    e.preventDefault();
    const errors: { [key: string]: string } = {};

    if (!newPassword) {
      errors.newPassword = 'Password baru wajib diisi.';
    } else if (newPassword.length < 6) {
      errors.newPassword = 'Password minimal 6 karakter.';
    }

    if (!confirmPassword) {
      errors.confirmPassword = 'Konfirmasi password wajib diisi.';
    } else if (newPassword !== confirmPassword) {
      errors.confirmPassword = 'Konfirmasi password tidak cocok.';
    }

    if (Object.keys(errors).length > 0) {
      setPasswordErrors(errors);
      return;
    }

    setPasswordErrors({});
    changePassword(newPassword);
    setNewPassword('');
    setConfirmPassword('');
  };

  return (
    <div className="profile-layout-grid">
      {/* =========================================
            LEFT COLUMN: TACTICAL PERSONNEL DOSSIER
            ========================================= */}
        <div className="profile-dossier-card">
          <div className="dossier-header-strip">
            <div className="dossier-tag">
              <Shield size={13} style={{ color: 'var(--color-accent)' }} />
              <span>DOSSIER PERSONEL</span>
            </div>
            <span style={{ color: 'var(--text-muted)', fontSize: '10px', letterSpacing: '0.08em' }}>SEKTOR 04</span>
          </div>

          <div className="dossier-body">
            {/* Identity Hero */}
            <div className="dossier-identity-hero">
              <div className="dossier-avatar-frame">
                <span>{currentUser.name.substring(0, 2).toUpperCase()}</span>
                <span className="dossier-pip-status" title="Status: Online & Terhubung" />
              </div>

              <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '4px' }}>
                <h2 className="dossier-name">{currentUser.name}</h2>
                <div style={{ marginTop: '2px' }}>
                  <StatusBadge status={currentUser.role} />
                </div>
                <span className="dossier-sub-id">
                  KODE ID: <strong className="text-mono" style={{ color: 'var(--color-accent)' }}>{currentUser.id}</strong>
                </span>
              </div>
            </div>

            {/* Micro Telemetry Stats */}
            <div>
              <div
                style={{
                  fontFamily: 'var(--font-mono)',
                  fontSize: '10px',
                  fontWeight: 700,
                  color: 'var(--text-muted)',
                  textTransform: 'uppercase',
                  letterSpacing: '0.08em',
                  marginBottom: '8px',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px',
                }}
              >
                <Activity size={12} style={{ color: 'var(--color-accent)' }} />
                <span>TELEMETRI LOGISTIK</span>
              </div>

              <div className="dossier-telemetry-row">
                <div className="dossier-stat-pill">
                  <span className="pill-val amber">{activeLoansCount}</span>
                  <span className="pill-lbl">Pinjaman</span>
                </div>
                <div className="dossier-stat-pill">
                  <span className="pill-val green">{completedLoansCount}</span>
                  <span className="pill-lbl">Selesai</span>
                </div>
                <div className="dossier-stat-pill">
                  <span className={`pill-val ${overdueLoansCount === 0 ? 'green' : 'amber'}`}>
                    {overdueLoansCount === 0 ? '100%' : `${overdueLoansCount}!`}
                  </span>
                  <span className="pill-lbl">Disiplin</span>
                </div>
              </div>
            </div>

            {/* Meta Specifications */}
            <div className="dossier-meta-list">
              <div className="dossier-meta-item">
                <span className="meta-key">
                  <MapPin size={12} /> Basis Operasional
                </span>
                <span className="meta-val" title={currentUser.basecamp}>
                  {currentUser.basecamp}
                </span>
              </div>
              <div className="dossier-meta-item">
                <span className="meta-key">
                  <Radio size={12} /> Saluran Radio
                </span>
                <span className="meta-val text-mono">{currentUser.phone}</span>
              </div>
              <div className="dossier-meta-item">
                <span className="meta-key">
                  <Mail size={12} /> Email Terdaftar
                </span>
                <span className="meta-val">{currentUser.email}</span>
              </div>
              <div className="dossier-meta-item">
                <span className="meta-key">
                  <BadgeCheck size={12} /> Status Biometrik
                </span>
                <span className="meta-val bio-clean">
                  NON-TERINFEKSI
                </span>
              </div>
            </div>

            {/* Tactical Barcode */}
            <div className="dossier-barcode-box">
              <svg width="220" height="24" viewBox="0 0 220 24" fill="none" xmlns="http://www.w3.org/2000/svg" style={{ opacity: 0.65 }}>
                <rect x="2" y="0" width="3" height="24" fill="#A3B879" />
                <rect x="8" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="13" y="0" width="5" height="24" fill="#A3B879" />
                <rect x="21" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="26" y="0" width="4" height="24" fill="#A3B879" />
                <rect x="33" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="38" y="0" width="6" height="24" fill="#A3B879" />
                <rect x="47" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="52" y="0" width="3" height="24" fill="#A3B879" />
                <rect x="58" y="0" width="5" height="24" fill="#A3B879" />
                <rect x="66" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="71" y="0" width="4" height="24" fill="#A3B879" />
                <rect x="78" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="83" y="0" width="5" height="24" fill="#A3B879" />
                <rect x="91" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="96" y="0" width="4" height="24" fill="#A3B879" />
                <rect x="103" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="108" y="0" width="6" height="24" fill="#A3B879" />
                <rect x="117" y="0" width="3" height="24" fill="#A3B879" />
                <rect x="123" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="128" y="0" width="5" height="24" fill="#A3B879" />
                <rect x="136" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="141" y="0" width="4" height="24" fill="#A3B879" />
                <rect x="148" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="153" y="0" width="6" height="24" fill="#A3B879" />
                <rect x="162" y="0" width="3" height="24" fill="#A3B879" />
                <rect x="168" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="173" y="0" width="5" height="24" fill="#A3B879" />
                <rect x="181" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="186" y="0" width="4" height="24" fill="#A3B879" />
                <rect x="193" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="198" y="0" width="4" height="24" fill="#A3B879" />
                <rect x="205" y="0" width="2" height="24" fill="#A3B879" />
                <rect x="210" y="0" width="5" height="24" fill="#A3B879" />
              </svg>
              <div className="dossier-barcode-text">RFID-TAG: 884-X99-SECTOR-4</div>
            </div>
          </div>
        </div>

        {/* =========================================
            RIGHT COLUMN: FORMS & SECURITY PANELS
            ========================================= */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
          {/* Panel 1: Personal Details */}
          <div className="profile-form-panel">
            <div className="profile-panel-header">
              <div className="profile-panel-title-group">
                <div className="profile-panel-icon">
                  <User size={18} />
                </div>
                <div>
                  <h3 className="profile-panel-title">Informasi Identitas Personel</h3>
                  <p className="profile-panel-sub">// VERIFIKASI BIOMETRIK &amp; PENUGASAN SEKTOR</p>
                </div>
              </div>
              <span className="profile-panel-tag">TERVERIFIKASI</span>
            </div>

            <form onSubmit={handleUpdateProfile} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))', gap: '16px' }}>
                <FormField label="Nama Lengkap Personel" required error={profileErrors.name}>
                  <div className="tactical-input-wrap">
                    <User size={16} className="tactical-input-icon" />
                    <input
                      type="text"
                      className={`input-control ${profileErrors.name ? 'input-error' : ''}`}
                      value={name}
                      placeholder="Masukkan nama lengkap"
                      onChange={(e) => {
                        setName(e.target.value);
                        if (profileErrors.name) setProfileErrors((p) => ({ ...p, name: '' }));
                      }}
                    />
                  </div>
                </FormField>

                <FormField label="Alamat Email Kredensial" hint="Terkunci ke basis data posko utama">
                  <div className="tactical-input-wrap">
                    <Mail size={16} className="tactical-input-icon" />
                    <input
                      type="email"
                      className="input-control"
                      value={email}
                      disabled
                      title="Alamat email telah diverifikasi sistem dan tidak dapat diubah sembarangan"
                    />
                  </div>
                </FormField>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))', gap: '16px' }}>
                <FormField label="Saluran Radio / Telepon Lapangan" required error={profileErrors.phone}>
                  <div className="tactical-input-wrap">
                    <Phone size={16} className="tactical-input-icon" />
                    <input
                      type="text"
                      className={`input-control text-mono ${profileErrors.phone ? 'input-error' : ''}`}
                      value={phone}
                      placeholder="Contoh: 0812-3344-5566"
                      onChange={(e) => {
                        setPhone(e.target.value);
                        if (profileErrors.phone) setProfileErrors((p) => ({ ...p, phone: '' }));
                      }}
                    />
                  </div>
                </FormField>

                <FormField label="Lokasi Penempatan Barak / Pos Jaga" required error={profileErrors.basecamp}>
                  <div className="tactical-input-wrap">
                    <MapPin size={16} className="tactical-input-icon" />
                    <input
                      type="text"
                      className={`input-control ${profileErrors.basecamp ? 'input-error' : ''}`}
                      value={basecamp}
                      placeholder="Contoh: Barak Survivor Blok 4B"
                      onChange={(e) => {
                        setBasecamp(e.target.value);
                        if (profileErrors.basecamp) setProfileErrors((p) => ({ ...p, basecamp: '' }));
                      }}
                    />
                  </div>
                </FormField>
              </div>

              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'space-between',
                  borderTop: '1px solid rgba(163, 184, 121, 0.12)',
                  paddingTop: '14px',
                  marginTop: '6px',
                  flexWrap: 'wrap',
                  gap: '12px',
                }}
              >
                <span className="text-meta" style={{ fontSize: '11.5px' }}>
                  Perubahan akan disinkronkan secara langsung ke manifest logistik karantina.
                </span>
                <PrimaryButton type="submit" size="sm" icon={<Save size={15} />}>
                  Simpan Perubahan Profil
                </PrimaryButton>
              </div>
            </form>
          </div>

          {/* Panel 2: Security & Password */}
          <div className="profile-form-panel">
            <div className="profile-panel-header">
              <div className="profile-panel-title-group">
                <div className="profile-panel-icon">
                  <Lock size={18} />
                </div>
                <div>
                  <h3 className="profile-panel-title">Keamanan Kunci Akses &amp; Sandi</h3>
                  <p className="profile-panel-sub">// ENKRIPSI OTORISASI TERMINAL LOKAL</p>
                </div>
              </div>
              <span className="profile-panel-tag">AES-256</span>
            </div>

            <form onSubmit={handleChangePassword} style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(260px, 1fr))', gap: '16px' }}>
                <FormField
                  label="Password Akses Baru"
                  required
                  error={passwordErrors.newPassword}
                  hint="Minimal 6 karakter alfanumerik"
                >
                  <div className="tactical-input-wrap">
                    <Lock size={16} className="tactical-input-icon" />
                    <input
                      type={showPassword ? 'text' : 'password'}
                      className={`input-control ${passwordErrors.newPassword ? 'input-error' : ''}`}
                      value={newPassword}
                      onChange={(e) => {
                        setNewPassword(e.target.value);
                        if (passwordErrors.newPassword) setPasswordErrors((p) => ({ ...p, newPassword: '' }));
                      }}
                      placeholder="••••••••"
                      style={{ paddingRight: '38px' }}
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      style={{
                        position: 'absolute',
                        right: '10px',
                        background: 'none',
                        border: 'none',
                        color: 'var(--text-muted)',
                        cursor: 'pointer',
                        display: 'flex',
                        alignItems: 'center',
                        padding: '4px',
                      }}
                      title={showPassword ? 'Sembunyikan password' : 'Lihat password'}
                    >
                      {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                    </button>
                  </div>
                </FormField>

                <FormField
                  label="Konfirmasi Password Akses Baru"
                  required
                  error={passwordErrors.confirmPassword}
                  hint="Ketik ulang password baru Anda"
                >
                  <div className="tactical-input-wrap">
                    <KeyRound size={16} className="tactical-input-icon" />
                    <input
                      type={showPassword ? 'text' : 'password'}
                      className={`input-control ${passwordErrors.confirmPassword ? 'input-error' : ''}`}
                      value={confirmPassword}
                      onChange={(e) => {
                        setConfirmPassword(e.target.value);
                        if (passwordErrors.confirmPassword) setPasswordErrors((p) => ({ ...p, confirmPassword: '' }));
                      }}
                      placeholder="••••••••"
                    />
                  </div>
                </FormField>
              </div>

              {/* Requirement indicators */}
              <div className="password-requirements-strip">
                <div className="pwd-req-chip">
                  <CheckCircle2 size={12} style={{ color: newPassword.length >= 6 ? 'var(--color-success)' : 'var(--text-muted)' }} />
                  <span>Min. 6 Karakter</span>
                </div>
                <div className="pwd-req-chip">
                  <CheckCircle2 size={12} style={{ color: newPassword && newPassword === confirmPassword ? 'var(--color-success)' : 'var(--text-muted)' }} />
                  <span>Kecocokan Konfirmasi</span>
                </div>
                <div className="pwd-req-chip">
                  <ShieldCheck size={12} style={{ color: 'var(--color-accent)' }} />
                  <span>Terenkripsi Standar SHA-256</span>
                </div>
              </div>

              <div
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'space-between',
                  borderTop: '1px solid rgba(163, 184, 121, 0.12)',
                  paddingTop: '14px',
                  marginTop: '6px',
                  flexWrap: 'wrap',
                  gap: '12px',
                }}
              >
                <span className="text-meta" style={{ fontSize: '11.5px' }}>
                  Gunakan password unik demi mencegah pembajakan akun terminal di zona karantina.
                </span>
                <PrimaryButton type="submit" size="sm" icon={<ShieldCheck size={15} />}>
                  Perbarui Kata Sandi
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
  );
};
