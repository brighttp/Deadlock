import React, { useState } from 'react';
import { Headphones, Radio, PhoneCall, CheckCircle, ShieldAlert } from 'lucide-react';
import { Modal } from './Modal';
import { PrimaryButton, SecondaryButton } from './Buttons';
import { FormField } from './FormField';
import { useDeadlock } from '../../context/DeadlockContext';

export const PoskoHotlineBanner: React.FC = () => {
  const { showToast } = useDeadlock();
  const [isOpen, setIsOpen] = useState(false);
  const [subject, setSubject] = useState('');
  const [message, setMessage] = useState('');

  const handleSend = (e: React.FormEvent) => {
    e.preventDefault();
    showToast('Transmisi terhubung. Posko Logistik Sektor 4 menerima laporan Anda.', 'success');
    setSubject('');
    setMessage('');
    setIsOpen(false);
  };

  return (
    <>
      <div className="posko-hotline-strip">
        <div className="posko-hotline-left">
          <div className="posko-hotline-icon-pod">
            <Headphones size={18} />
          </div>
          <div className="posko-hotline-title">
            <span>HUBUNGI POSKO LOGISTIK SEKTOR 4</span>
            <span className="posko-hotline-freq-tag">RADIO CH-09 // 142.85 MHz</span>
          </div>
        </div>

        <div className="posko-hotline-right">
          <span className="posko-hotline-meta">STANDBY 24/7 &bull; RESPON CEPAT</span>
          <button
            type="button"
            className="btn-posko-action"
            onClick={() => setIsOpen(true)}
          >
            <PhoneCall size={13} />
            <span>PANGGIL OPERATOR</span>
          </button>
        </div>
      </div>

      <Modal
        isOpen={isOpen}
        onClose={() => setIsOpen(false)}
        title="Saluran Transmisi Darurat // Posko Logistik"
        maxWidth="500px"
        footer={
          <>
            <SecondaryButton size="sm" onClick={() => setIsOpen(false)}>
              Batal
            </SecondaryButton>
            <PrimaryButton size="sm" onClick={handleSend} icon={<Radio size={14} />}>
              Kirim Transmisi Radio
            </PrimaryButton>
          </>
        }
      >
        <form onSubmit={handleSend} style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
          <div
            style={{
              padding: '12px 14px',
              backgroundColor: 'rgba(240, 178, 71, 0.12)',
              border: '1px solid rgba(240, 178, 71, 0.35)',
              borderRadius: 'var(--radius-sm)',
              display: 'flex',
              alignItems: 'center',
              gap: '12px',
              fontSize: '13px',
              color: '#F8E7C5',
            }}
          >
            <ShieldAlert size={20} style={{ color: '#F0B247', flexShrink: 0 }} />
            <div>
              <strong>Posko Induk Haven Sector 4:</strong> Letnan Raymond Kelly (Petugas Jaga).
              Gunakan saluran ini untuk pertanyaan pengembalian unit, perpanjangan darurat, atau suplai medis khusus.
            </div>
          </div>

          <FormField label="Topik Laporan / Permintaan" required>
            <input
              type="text"
              className="input-control"
              value={subject}
              onChange={(e) => setSubject(e.target.value)}
              placeholder="Contoh: Permintaan Amunisi Tambahan / Serah Terima Unit"
              required
            />
          </FormField>

          <FormField label="Pesan Transmisi" required>
            <textarea
              className="input-control"
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              placeholder="Jelaskan kebutuhan operasional atau kendala unit lapangan Anda..."
              rows={3}
              required
            />
          </FormField>

          <div
            style={{
              fontSize: '11.5px',
              color: 'var(--text-muted)',
              fontFamily: 'var(--font-mono)',
              display: 'flex',
              alignItems: 'center',
              gap: '6px',
            }}
          >
            <CheckCircle size={13} style={{ color: 'var(--color-success)' }} />
            Sinyal Enkripsi: Terhubung Aman (AES-256 Military Haven)
          </div>
        </form>
      </Modal>
    </>
  );
};
