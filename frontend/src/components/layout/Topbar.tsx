import React, { useState, useEffect } from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { Menu, Clock } from 'lucide-react';
import { StatusBadge } from '../common/StatusBadge';
import { IconButton } from '../common/Buttons';

interface TopbarProps {
  onToggleSidebar: () => void;
}

export const Topbar: React.FC<TopbarProps> = ({ onToggleSidebar }) => {
  const { currentUser, currentPage } = useDeadlock();
  const [currentTime, setCurrentTime] = useState<string>('');

  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      setCurrentTime(
        now.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
        }) + ' WIB'
      );
    };
    updateTime();
    const timer = setInterval(updateTime, 1000);
    return () => clearInterval(timer);
  }, []);

  const getPageTitle = () => {
    switch (currentPage) {
      case 'admin-dashboard':
        return 'Dashboard Komando Logistik';
      case 'admin-categories':
        return 'Manajemen Kategori Logistik';
      case 'admin-units':
        return 'Inventaris Unit & Persenjataan';
      case 'admin-members':
        return 'Direktori Personel Survivor';
      case 'admin-loans':
        return 'Pusat Peminjaman & Pengembalian';
      case 'admin-history':
        return 'Rekam Riwayat Peminjaman Unit';
      case 'survivor-dashboard':
        return 'Dashboard Operasi Survivor';
      case 'survivor-catalog':
        return 'Katalog Unit & Perbekalan';
      case 'survivor-loans':
        return 'Daftar Pinjaman Logistik Saya';
      case 'profile':
        return 'Terminal Profil Personel';
      default:
        return 'Sistem Gudang';
    }
  };

  return (
    <header className="app-topbar">
      <div className="topbar-left">
        <IconButton
          icon={<Menu size={20} />}
          onClick={onToggleSidebar}
          className="mobile-menu-btn"
          title="Buka Menu"
        />

        <div className="topbar-page-identity">
          <div className="topbar-page-title-row">
            <span className="topbar-page-title">{getPageTitle()}</span>
          </div>
        </div>
      </div>

      <div className="topbar-right">
        {/* Live Military Digital Clock */}
        <div className="topbar-clock-widget">
          <Clock size={13} className="clock-icon" />
          <span className="clock-text">{currentTime || '12:00:00 WIB'}</span>
        </div>

        {/* Current Role Badge */}
        {currentUser && (
          <StatusBadge status={currentUser.role} className="role-badge-pill" />
        )}
      </div>
    </header>
  );
};
