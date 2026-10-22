import React from 'react';
import { Modal } from './Modal';
import { DangerButton, PrimaryButton, SecondaryButton } from './Buttons';
import { AlertTriangle, Info } from 'lucide-react';

interface ConfirmDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  variant?: 'danger' | 'primary';
  icon?: React.ReactNode;
}

export const ConfirmDialog: React.FC<ConfirmDialogProps> = ({
  isOpen,
  onClose,
  onConfirm,
  title,
  message,
  confirmLabel = 'Konfirmasi',
  cancelLabel = 'Batal',
  variant = 'primary',
  icon,
}) => {
  return (
    <Modal
      isOpen={isOpen}
      onClose={onClose}
      title={title}
      maxWidth="440px"
      footer={
        <>
          <SecondaryButton size="sm" onClick={onClose}>
            {cancelLabel}
          </SecondaryButton>
          {variant === 'danger' ? (
            <DangerButton
              size="sm"
              onClick={() => {
                onConfirm();
                onClose();
              }}
            >
              {confirmLabel}
            </DangerButton>
          ) : (
            <PrimaryButton
              size="sm"
              onClick={() => {
                onConfirm();
                onClose();
              }}
            >
              {confirmLabel}
            </PrimaryButton>
          )}
        </>
      }
    >
      <div style={{ display: 'flex', gap: '14px', alignItems: 'flex-start' }}>
        <div
          style={{
            color: variant === 'danger' ? 'var(--color-danger)' : 'var(--color-accent)',
            flexShrink: 0,
            marginTop: '2px',
          }}
        >
          {icon || (variant === 'danger' ? <AlertTriangle size={24} /> : <Info size={24} />)}
        </div>
        <div style={{ fontSize: '13.5px', color: 'var(--text-secondary)', lineHeight: '1.5' }}>
          {message}
        </div>
      </div>
    </Modal>
  );
};
