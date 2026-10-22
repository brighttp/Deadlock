import React, { useState, useEffect } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { FormField } from '../../components/common/FormField';
import { PrimaryButton } from '../../components/common/Buttons';
import { ToastContainer } from '../../components/common/Toast';
import { Tactical3DHologram } from '../../components/auth/Tactical3DHologram';
import {
  Shield,
  ArrowRight,
  KeyRound,
  Mail,
  Lock,
  Eye,
  EyeOff,
  Radio,
  Wifi,
  Activity,
  Cpu,
  UserPlus,
  LogIn,
} from 'lucide-react';

export const LoginPage: React.FC = () => {
  const { login, setCurrentPage } = useDeadlock();
  const [email, setEmail] = useState('admin@deadlock.haven');
  const [password, setPassword] = useState('password123');
  const [showPassword, setShowPassword] = useState(false);
  const [errors, setErrors] = useState<{ email?: string; password?: string }>({});

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
    const rotateX = -(y / (rect.height / 2)) * 5;
    const rotateY = (x / (rect.width / 2)) * 5;
    setTilt({ x: rotateX, y: rotateY });
  };

  const handleCardMouseLeave = () => {
    setTilt({ x: 0, y: 0 });
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const newErrors: { email?: string; password?: string } = {};

    if (!email.trim()) {
      newErrors.email = 'Email wajib diisi.';
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Format email tidak valid.';
    }

    if (!password) {
      newErrors.password = 'Password wajib diisi.';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    setErrors({});
    login(email, password);
  };

  const fillQuickAccount = (quickEmail: string) => {
    setEmail(quickEmail);
    setPassword('password123');
    setErrors({});
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
          <span>HAVEN SECTOR 04 &bull; ARSENAL &amp; QUARANTINE PROTOCOL</span>
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
              <span>3D TACTICAL VAULT CORE // BIOMETRIC FEED</span>
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
            <span className="holo-hud-badge top-left">[ CAM-04 // LIVE RADAR ]</span>
            <Tactical3DHologram />
            <span className="holo-hud-badge bottom-right">LAT -6.2088 &bull; LNG 106.8456</span>
          </div>

          {/* Telemetry Pills Strip */}
          <div className="holo-telemetry-strip">
            <div className="holo-pill">
              <span className="pill-title">Mainframe</span>
              <span className="pill-val">ONLINE 99.8%</span>
            </div>
            <div className="holo-pill">
              <span className="pill-title">Enkripsi</span>
              <span className="pill-val amber">AES-256 GCM</span>
            </div>
            <div className="holo-pill">
              <span className="pill-title">Karantina</span>
              <span className="pill-val">HARI KE-142</span>
            </div>
          </div>

          {/* Tactical Military Briefing Quote */}
          <div className="holo-briefing-box">
            <p style={{ margin: 0, fontWeight: 500 }}>
              &ldquo;Dalam kegelapan kiamat, disiplin inventaris logistik adalah satu-satunya garis batas antara
              bertahan hidup dan kepunahan massal.&rdquo;
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
              <span>DOKTRIN MILITER POSKO UTAMA HAVEN 4</span>
            </div>
          </div>
        </section>

        {/* RIGHT COLUMN: 3D GLASSMORPHI AUTH TERMINAL CARD */}
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
                Sistem Logistik Gudang Karantina // Haven Sector 4
              </p>
            </div>
          </div>

          {/* Mode Tabs (Login vs Register) */}
          <div className="auth-tab-bar">
            <button type="button" className="auth-tab-btn active">
              <LogIn size={13} />
              <span>MASUK TERMINAL</span>
            </button>
            <button
              type="button"
              className="auth-tab-btn"
              onClick={() => setCurrentPage('register')}
            >
              <UserPlus size={13} />
              <span>REGISTRASI BARU</span>
            </button>
          </div>

          {/* Form Credentials */}
          <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
            <FormField label="Alamat Email Kredensial" required error={errors.email}>
              <div className="tactical-input-wrap">
                <Mail size={16} className="tactical-input-icon" />
                <input
                  type="email"
                  className={`input-control ${errors.email ? 'input-error' : ''}`}
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="admin@deadlock.haven"
                  autoComplete="email"
                />
              </div>
            </FormField>

            <FormField label="Kunci Sandi Akses (Password)" required error={errors.password}>
              <div className="tactical-input-wrap" style={{ paddingRight: '8px' }}>
                <Lock size={16} className="tactical-input-icon" />
                <input
                  type={showPassword ? 'text' : 'password'}
                  className={`input-control ${errors.password ? 'input-error' : ''}`}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  autoComplete="current-password"
                />
                <button
                  type="button"
                  className="input-eye-btn"
                  onClick={() => setShowPassword((prev) => !prev)}
                  title={showPassword ? 'Sembunyikan password' : 'Tampilkan password'}
                >
                  {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                </button>
              </div>
            </FormField>

            <PrimaryButton
              type="submit"
              size="lg"
              style={{ width: '100%', marginTop: '6px' }}
              icon={<ArrowRight size={17} />}
            >
              Otorisasi &amp; Masuk Terminal
            </PrimaryButton>
          </form>

          {/* Quick Demo Access Bar */}
          <div className="auth-quick-preset-bar">
            <div className="auth-quick-header">
              <span style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                <KeyRound size={13} style={{ color: 'var(--color-accent)' }} />
                <span>AKSES CEPAT DEMO (1-KLIK)</span>
              </span>
              <span style={{ color: 'var(--text-muted)', fontSize: '9.5px' }}>SIAP PAKAI</span>
            </div>

            <div className="auth-quick-btns">
              <button
                type="button"
                className="auth-preset-card"
                onClick={() => fillQuickAccount('admin@deadlock.haven')}
              >
                <div className="auth-preset-avatar">KO</div>
                <div className="auth-preset-info">
                  <span className="auth-preset-name">Komandan Marcus</span>
                  <span className="auth-preset-role">ADMIN &bull; BUNKER A</span>
                </div>
              </button>

              <button
                type="button"
                className="auth-preset-card"
                onClick={() => fillQuickAccount('sarah@haven.org')}
              >
                <div className="auth-preset-avatar" style={{ color: '#F0B247', borderColor: 'rgba(240, 178, 71, 0.4)' }}>
                  SA
                </div>
                <div className="auth-preset-info">
                  <span className="auth-preset-name">Sarah Connor</span>
                  <span className="auth-preset-role">SURVIVOR &bull; BLOK 4B</span>
                </div>
              </button>
            </div>
          </div>
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
