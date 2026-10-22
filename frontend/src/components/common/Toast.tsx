import React from 'react';
import { useDeadlock } from '../../context/DeadlockContext';
import { CheckCircle2, AlertCircle, AlertTriangle, Info, X } from 'lucide-react';

export const ToastContainer: React.FC = () => {
  const { toasts, removeToast } = useDeadlock();

  if (toasts.length === 0) return null;

  return (
    <div className="toast-container" aria-live="polite">
      {toasts.map((toast) => {
        let Icon = CheckCircle2;
        let iconColor = 'var(--color-success)';

        if (toast.type === 'error') {
          Icon = AlertCircle;
          iconColor = 'var(--color-danger)';
        } else if (toast.type === 'warning') {
          Icon = AlertTriangle;
          iconColor = 'var(--color-warning)';
        } else if (toast.type === 'info') {
          Icon = Info;
          iconColor = 'var(--color-accent)';
        }

        return (
          <div key={toast.id} className={`toast-item ${toast.type}`}>
            <Icon size={18} style={{ color: iconColor, flexShrink: 0 }} />
            <div style={{ flex: 1, fontSize: '13px', color: 'var(--text-main)' }}>
              {toast.message}
            </div>
            <button
              type="button"
              onClick={() => removeToast(toast.id)}
              style={{
                background: 'none',
                border: 'none',
                color: 'var(--text-muted)',
                cursor: 'pointer',
                padding: '2px',
              }}
              title="Tutup Notifikasi"
            >
              <X size={14} />
            </button>
          </div>
        );
      })}
    </div>
  );
};
