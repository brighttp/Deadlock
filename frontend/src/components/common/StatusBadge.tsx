import React from 'react';

export type StatusVariant = 
  | 'Tersedia' 
  | 'Dipinjam' 
  | 'Aktif' 
  | 'Terlambat' 
  | 'Selesai' 
  | 'Admin' 
  | 'Survivor'
  | string;

interface StatusBadgeProps {
  status: StatusVariant;
  className?: string;
}

export const StatusBadge: React.FC<StatusBadgeProps> = ({ status, className = '' }) => {
  let badgeClass = 'badge-neutral';

  switch (status) {
    case 'Tersedia':
      badgeClass = 'badge-success';
      break;
    case 'Dipinjam':
      badgeClass = 'badge-warning';
      break;
    case 'Aktif':
      badgeClass = 'badge-accent';
      break;
    case 'Terlambat':
      badgeClass = 'badge-danger';
      break;
    case 'Selesai':
      badgeClass = 'badge-neutral';
      break;
    case 'Admin':
      badgeClass = 'badge-accent';
      break;
    case 'Survivor':
      badgeClass = 'badge-survivor';
      break;
    default:
      badgeClass = 'badge-neutral';
  }

  return (
    <span className={`badge ${badgeClass} ${className}`}>
      <span className="badge-dot" />
      <span>{status}</span>
    </span>
  );
};
