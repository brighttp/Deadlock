import React from 'react';
import { PackageOpen } from 'lucide-react';

interface EmptyStateProps {
  title?: string;
  description?: string;
  icon?: React.ReactNode;
  action?: React.ReactNode;
}

export const EmptyState: React.FC<EmptyStateProps> = ({
  title = 'Tidak ada data ditemukan',
  description = 'Belum ada catatan atau data yang sesuai dengan kriteria saat ini.',
  icon,
  action,
}) => {
  return (
    <div className="empty-state">
      <div className="empty-icon">
        {icon || <PackageOpen size={24} />}
      </div>
      <div className="empty-title">{title}</div>
      <div className="empty-desc">{description}</div>
      {action && <div style={{ marginTop: '6px' }}>{action}</div>}
    </div>
  );
};
