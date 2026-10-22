import React from 'react';
import { Loader2, AlertCircle } from 'lucide-react';
import { SecondaryButton } from './Buttons';

export const LoadingState: React.FC<{ message?: string }> = ({
  message = 'Memuat data sistem...',
}) => {
  return (
    <div className="empty-state">
      <Loader2
        size={32}
        className="animate-spin"
        style={{ color: 'var(--color-accent)' }}
      />
      <div className="empty-title" style={{ marginTop: '8px' }}>
        {message}
      </div>
    </div>
  );
};

export const ErrorState: React.FC<{
  title?: string;
  message: string;
  onRetry?: () => void;
}> = ({ title = 'Terjadi Kesalahan', message, onRetry }) => {
  return (
    <div className="empty-state">
      <div className="empty-icon" style={{ color: 'var(--color-danger)' }}>
        <AlertCircle size={24} />
      </div>
      <div className="empty-title">{title}</div>
      <div className="empty-desc">{message}</div>
      {onRetry && (
        <SecondaryButton size="sm" onClick={onRetry} style={{ marginTop: '8px' }}>
          Coba Lagi
        </SecondaryButton>
      )}
    </div>
  );
};
