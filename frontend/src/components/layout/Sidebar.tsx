import React from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import type { PageRoute } from '../../types';
import {
  LayoutDashboard,
  Tags,
  Boxes,
  Users,
  ArrowLeftRight,
  History,
  PackageSearch,
  ClipboardList,
  UserCheck,
  LogOut,
  ChevronRight,
  Radio,
  X,
} from 'lucide-react';

interface SidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ isOpen, onClose }) => {
  const { currentUser, currentPage, setCurrentPage, logout } = useDeadlock();

  if (!currentUser) return null;

  const isAdmin = currentUser.role === 'Admin';

  interface NavItemDef {
    id: PageRoute;
    label: string;
    icon: React.ReactNode;
    badge?: string;
  }

  const adminNavItems: NavItemDef[] = [
    { id: 'profile', label: 'Profil Komandan', icon: <UserCheck size={18} /> },
    { id: 'admin-dashboard', label: 'Dashboard Komando', icon: <LayoutDashboard size={18} /> },
    { id: 'admin-categories', label: 'Kategori Logistik', icon: <Tags size={18} /> },
    { id: 'admin-units', label: 'Unit Inventaris', icon: <Boxes size={18} /> },
    { id: 'admin-members', label: 'Personel Survivor', icon: <Users size={18} /> },
    { id: 'admin-loans', label: 'Peminjaman Unit', icon: <ArrowLeftRight size={18} /> },
    { id: 'admin-history', label: 'Riwayat & Rekap', icon: <History size={18} /> },
  ];

  const survivorNavItems: NavItemDef[] = [
    { id: 'profile', label: 'Profil Saya', icon: <UserCheck size={18} /> },
    { id: 'survivor-dashboard', label: 'Dashboard', icon: <LayoutDashboard size={18} /> },
    { id: 'survivor-catalog', label: 'Katalog Unit', icon: <PackageSearch size={18} /> },
    { id: 'survivor-loans', label: 'Pinjaman Saya', icon: <ClipboardList size={18} /> },
  ];

  const navItems = isAdmin ? adminNavItems : survivorNavItems;

  const handleNavClick = (page: PageRoute) => {
    setCurrentPage(page);
    onClose();
  };

  return (
    <aside className={`app-sidebar ${isOpen ? 'open' : ''}`}>
      {/* Profile Header (replacing Deadlock brand) */}
      <div
        className="sidebar-brand sidebar-profile-header"
        onClick={() => handleNavClick('profile')}
        title="Buka Profil"
        style={{ cursor: 'pointer' }}
      >
        <div className="user-avatar-wrapper">
          <div className="user-avatar">
            {currentUser.name.substring(0, 2).toUpperCase()}
          </div>
          <span className="user-online-pip" />
        </div>

        <div className="user-details">
          <span className="user-name" title={currentUser.name}>
            {currentUser.name}
          </span>
          <span className="user-role-text">
            {isAdmin ? 'KOMANDAN LOGISTIK' : 'SURVIVOR RECON'}
          </span>
        </div>

        <button
          className="mobile-menu-btn"
          onClick={(e) => {
            e.stopPropagation();
            onClose();
          }}
          style={{
            marginLeft: 'auto',
            background: 'none',
            border: 'none',
            color: 'var(--text-secondary)',
            cursor: 'pointer',
          }}
          title="Tutup Menu"
        >
          <X size={20} />
        </button>
      </div>

      {/* Navigation */}
      <nav className="sidebar-nav">
        <div className="nav-items-list">
          {navItems.map((item) => {
            const isActive = currentPage === item.id;
            return (
              <button
                key={item.id}
                type="button"
                className={`nav-item ${isActive ? 'active' : ''}`}
                onClick={() => handleNavClick(item.id)}
              >
                <span className="nav-item-icon">{item.icon}</span>
                <span className="nav-item-label">{item.label}</span>
                {isActive ? (
                  <span className="nav-item-active-chevron">
                    <ChevronRight size={14} />
                  </span>
                ) : null}
              </button>
            );
          })}
        </div>
      </nav>

      {/* Footer */}
      <div className="sidebar-footer">
        {/* Comms Radar status */}
        <div className="comms-status-bar">
          <Radio size={12} className="comms-radio-icon" />
          <span className="comms-label">RADIO FREQ: 142.85 MHz</span>
          <span className="comms-indicator" />
        </div>

        <button
          type="button"
          className="btn-sidebar-logout"
          onClick={() => {
            onClose();
            logout();
          }}
        >
          <LogOut size={15} />
          <span>Keluar Sesi Terminal</span>
        </button>
      </div>
    </aside>
  );
};
