import React, { useState, useEffect } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { FormField } from '../../components/common/FormField';
import { PrimaryButton } from '../../components/common/Buttons';
import { ToastContainer } from '../../components/common/Toast';
import { Tactical3DHologram } from '../../components/auth/Tactical3DHologram';
import {
  Shield,
  ArrowRight,
  Mail,
  Lock,
  Phone,
  MapPin,
  Eye,
  EyeOff,
  User as UserIcon,
  Radio,
  Cpu,
  Activity,
  UserPlus,
  LogIn,
} from 'lucide-react';

export const RegisterPage: React.FC = () => {
  const { register, setCurrentPage } = useDeadlock();

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    basecamp: '',
    password: '',
    confirmPassword: '',
  });

  const [showPassword, setShowPassword] = useState(false);
  const [errors, setErrors] = useState<{ [key: string]: string }>({});

  // Live Tactical Clock
  const [currentTime, setCurrentTime] = useState('');
  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      setCurrentTime(
        now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB'
      );
    };
    updateTime();
    const interval = setInterval(updateTime, 1000);
    return () => clearInterval(interval);
  }, []);

  // 3D Card Tilt State
  const [tilt, setTilt] = useState({ x: 0, y: 0 });

  const handleCardMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    const card = e.currentTarget;
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;
    const rotateX = -(y / (rect.height / 2)) * 4.5;
    const rotateY = (x / (rect.width / 2)) * 4.5;
    setTilt({ x: rotateX, y: rotateY });
  };

  const handleCardMouseLeave = () => {
    setTilt({ x: 0, y: 0 });
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    if (errors[name]) {
      setErrors((prev) => {
        const next = { ...prev };
        delete next[name];
        return next;
      });
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const newErrors: { [key: string]: string } = {};

    if (!formData.name.trim()) {
      newErrors.name = 'Nama lengkap wajib diisi.';
    }

    if (!formData.email.trim()) {
      newErrors.email = 'Email wajib diisi.';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Format email tidak valid.';
    }

    if (!formData.phone.trim()) {
      newErrors.phone = 'Nomor telepon/radio wajib diisi.';
    }

    if (!formData.basecamp.trim()) {
      newErrors.basecamp = 'Lokasi barak / basecamp wajib diisi.';
    }

    if (!formData.password) {
      newErrors.password = 'Password wajib diisi.';
    } else if (formData.password.length < 6) {
      newErrors.password = 'Password minimal 6 karakter.';
    }

    if (!formData.confirmPassword) {
      newErrors.confirmPassword = 'Konfirmasi password wajib diisi.';
    } else if (formData.password !== formData.confirmPassword) {
      newErrors.confirmPassword = 'Konfirmasi password tidak cocok.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    setErrors({});
    register({
      name: formData.name,
      email: formData.email,
      phone: formData.phone,
      basecamp: formData.basecamp,
      password: formData.password,
    });
  };

  return (
    <div className="auth-viewport">
      <ToastContainer />
      <div className="auth-scanlines" />

      {/* TOP TACTICAL HUD STATUS BAR */}
      <header className="auth-header-hud">
        <div className="auth-hud-left">
          <span className="auth-hud-pip" />
          <span>// DEADLOCK TERMINAL v2.4.0</span>
          <span style={{ color: 'var(--text-muted)' }}>|</span>
          <span style={{ color: 'var(--text-muted)', display: 'flex', alignItems: 'center', gap: '5px' }}>
            <Radio size={12} style={{ color: '#4ADE80' }} /> 142.85 MHz
          </span>
        </div>

        <div className="auth-hud-center">
          <Shield size={13} style={{ color: 'var(--color-accent)' }} />
          <span>HAVEN SECTOR 04 &bull; REGISTRASI PERSONEL SURVIVOR</span>
        </div>

        <div className="auth-hud-right">
          <span style={{ color: '#8C998A' }}>{currentTime}</span>
          <span className="auth-hud-defcon">DEFCON-2 LOCKDOWN</span>
        </div>
      </header>

      {/* MAIN DUAL-PANE CONTAINER */}
      <main className="auth-main-layout">
        {/* LEFT COLUMN: 3D TACTICAL HOLO-COMMAND STAGE */}
        <section className="auth-holo-stage">
          <div className="auth-holo-header">
            <div className="auth-holo-tag">
              <Cpu size={15} style={{ color: '#4ADE80' }} />
              <span>3D TACTICAL VAULT CORE // PERSONEL MANIFEST</span>
            </div>
            <div className="holo-eq-bar-wrap" title="Audio / Radio Signal Active">
              <span className="holo-eq-bar" style={{ animationDelay: '0.1s' }} />
              <span className="holo-eq-bar" style={{ animationDelay: '0.4s' }} />
              <span className="holo-eq-bar" style={{ animationDelay: '0.2s' }} />
              <span className="holo-eq-bar" style={{ animationDelay: '0.5s' }} />
              <span className="holo-eq-bar" style={{ animationDelay: '0.3s' }} />
            </div>
          </div>

          {/* Real 3D Interactive Canvas Hologram Viewport */}
          <div className="auth-holo-viewport">
            <span className="holo-hud-badge top-left">[ MANIFEST ENCRYPTION ]</span>
            <Tactical3DHologram />
            <span className="holo-hud-badge bottom-right">ZOMBIE STRAIN: Z-EPSILON</span>
          </div>

          {/* Telemetry Pills Strip */}
          <div className="holo-telemetry-strip">
            <div className="holo-pill">
              <span className="pill-title">Otorisasi</span>
              <span className="pill-val">SURVIVOR TIER</span>
            </div>
            <div className="holo-pill">
              <span className="pill-title">Maks Pinjaman</span>
              <span className="pill-val amber">2 UNIT / ORANG</span>
            </div>
            <div className="holo-pill">
              <span className="pill-title">Biometrik</span>
              <span className="pill-val">NON-TERINFEKSI</span>
            </div>
          </div>

          {/* Tactical Military Briefing Quote */}
          <div className="holo-briefing-box">
            <p style={{ margin: 0, fontWeight: 500 }}>
              &ldquo;Setiap peluru, ransum, dan unit perlengkapan dicatat dalam buku induk posko. Jangan pernah
              meninggalkan rekan, jangan pernah menyalahgunakan logistik karantina.&rdquo;
            </p>
            <div
              style={{
                fontFamily: 'var(--font-mono)',
                fontSize: '10.5px',
                color: 'var(--color-accent)',
                marginTop: '6px',
                display: 'flex',
                alignItems: 'center',
                gap: '6px',
              }}
            >
              <Activity size={12} />
              <span>PROTOKOL EVAKUASI SEKTOR HAVEN 4</span>
            </div>
          </div>
        </section>

        {/* RIGHT COLUMN: 3D GLASSMORPHI REGISTRATION CARD */}
        <section
          className="auth-terminal-card"
          onMouseMove={handleCardMouseMove}
          onMouseLeave={handleCardMouseLeave}
          style={{
            transform: `perspective(1000px) rotateX(${tilt.x}deg) rotateY(${tilt.y}deg)`,
          }}
        >
          {/* Cyber Corner Brackets */}
          <span className="corner-bracket corner-tl" />
          <span className="corner-bracket corner-tr" />
          <span className="corner-bracket corner-bl" />
          <span className="corner-bracket corner-br" />

          {/* Brand Hero Header */}
          <div className="auth-brand-hero">
            <div className="auth-brand-logo-frame">
              <Shield size={28} />
            </div>
            <div>
              <h1 className="auth-brand-title">DEADLOCK</h1>
              <p className="auth-brand-subtitle">
                Registrasi Manifest Survivor // Haven Sector 4
              </p>
            </div>
          </div>

          {/* Mode Tabs (Login vs Register) */}
          <div className="auth-tab-bar">
            <button
              type="button"
              className="auth-tab-btn"
              onClick={() => setCurrentPage('login')}
            >
              <LogIn size={13} />
              <span>MASUK TERMINAL</span>
            </button>
            <button type="button" className="auth-tab-btn active">
              <UserPlus size={13} />
              <span>REGISTRASI BARU</span>
            </button>
          </div>

          {/* Registration Form */}
          <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
            <FormField label="Nama Lengkap Personel" required error={errors.name}>
              <div className="tactical-input-wrap">
                <UserIcon size={16} className="tactical-input-icon" />
                <input
                  type="text"
                  name="name"
                  className={`input-control ${errors.name ? 'input-error' : ''}`}
                  value={formData.name}
                  onChange={handleChange}
                  placeholder="Contoh: Marcus Aurelius"
                />
              </div>
            </FormField>

            <FormField label="Alamat Email Kredensial" required error={errors.email}>
              <div className="tactical-input-wrap">
                <Mail size={16} className="tactical-input-icon" />
                <input
                  type="email"
                  name="email"
                  className={`input-control ${errors.email ? 'input-error' : ''}`}
                  value={formData.email}
                  onChange={handleChange}
                  placeholder="nama@haven.org"
                  autoComplete="email"
                />
              </div>
            </FormField>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '12px' }}>
              <FormField label="Nomor Telepon / Radio" required error={errors.phone}>
                <div className="tactical-input-wrap">
                  <Phone size={15} className="tactical-input-icon" />
                  <input
                    type="text"
                    name="phone"
                    className={`input-control text-mono ${errors.phone ? 'input-error' : ''}`}
                    value={formData.phone}
                    onChange={handleChange}
                    placeholder="0812-xxxx-xxxx"
                  />
                </div>
              </FormField>

              <FormField label="Lokasi Barak / Pos Jaga" required error={errors.basecamp}>
                <div className="tactical-input-wrap">
                  <MapPin size={15} className="tactical-input-icon" />
                  <input
                    type="text"
                    name="basecamp"
                    className={`input-control ${errors.basecamp ? 'input-error' : ''}`}
                    value={formData.basecamp}
                    onChange={handleChange}
                    placeholder="Contoh: Barak Survivor 3B"
                  />
                </div>
              </FormField>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '12px' }}>
              <FormField label="Password Akses" required error={errors.password}>
                <div className="tactical-input-wrap" style={{ paddingRight: '6px' }}>
                  <Lock size={15} className="tactical-input-icon" />
                  <input
                    type={showPassword ? 'text' : 'password'}
                    name="password"
                    className={`input-control ${errors.password ? 'input-error' : ''}`}
                    value={formData.password}
                    onChange={handleChange}
                    placeholder="Minimal 6 karakter"
                    autoComplete="new-password"
                  />
                  <button
                    type="button"
                    className="input-eye-btn"
                    onClick={() => setShowPassword((p) => !p)}
                    title={showPassword ? 'Sembunyikan' : 'Tampilkan'}
                  >
                    {showPassword ? <EyeOff size={15} /> : <Eye size={15} />}
                  </button>
                </div>
              </FormField>

              <FormField label="Konfirmasi Password" required error={errors.confirmPassword}>
                <div className="tactical-input-wrap">
                  <Lock size={15} className="tactical-input-icon" />
                  <input
                    type={showPassword ? 'text' : 'password'}
                    name="confirmPassword"
                    className={`input-control ${errors.confirmPassword ? 'input-error' : ''}`}
                    value={formData.confirmPassword}
                    onChange={handleChange}
                    placeholder="Ulangi password"
                    autoComplete="new-password"
                  />
                </div>
              </FormField>
            </div>

            <div
              style={{
                fontFamily: 'var(--font-mono)',
                fontSize: '10.5px',
                color: 'var(--text-muted)',
                backgroundColor: 'rgba(0, 0, 0, 0.35)',
                border: '1px solid rgba(163, 184, 121, 0.15)',
                borderRadius: 'var(--radius-sm)',
                padding: '8px 10px',
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
              }}
            >
              <Shield size={13} style={{ color: '#4ADE80', flexShrink: 0 }} />
              <span>Hak akses registrasi ini ditetapkan secara otomatis sebagai <strong>Survivor</strong>.</span>
            </div>

            <PrimaryButton
              type="submit"
              size="lg"
              style={{ width: '100%', marginTop: '4px' }}
              icon={<ArrowRight size={17} />}
            >
              Daftarkan Diri &amp; Masuk Posko
            </PrimaryButton>
          </form>
        </section>
      </main>

      {/* BOTTOM TACTICAL FOOTER HUD */}
      <footer className="auth-footer-hud">
        <span>ENCRYPTED LOGISTICS MAINFRAME // HAVEN REFUGEE PROTECTION PROTOCOL</span>
        <span>STATUS BIOMETRIK: NON-TERINFEKSI &bull; LEVEL 4 ACCESS</span>
      </footer>
    </div>
  );
};
