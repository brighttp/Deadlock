import React, { useState } from 'react';
import type { Unit, Category } from '../../types';
import { StatusBadge } from './StatusBadge';
import { Shield, ImageOff } from 'lucide-react';

interface UnitCardProps {
  unit: Unit;
  categories: Category[];
  action?: React.ReactNode;
  onSelect?: () => void;
}

export const UnitCard: React.FC<UnitCardProps> = ({
  unit,
  categories,
  action,
  onSelect,
}) => {
  const [imgError, setImgError] = useState(false);

  const getCategoryName = (id: string) => {
    return categories.find((c) => c.id === id)?.name || id;
  };

  return (
    <div className="unit-card" onClick={onSelect}>
      {/* Image Section */}
      <div className="unit-card-media">
        {unit.imageUrl && !imgError ? (
          <img
            src={unit.imageUrl}
            alt={unit.name}
            className="unit-card-img"
            loading="lazy"
            onError={() => setImgError(true)}
          />
        ) : (
          <div className="unit-card-img-fallback">
            {imgError ? <ImageOff size={28} /> : <Shield size={28} />}
            <span style={{ fontSize: '11px', fontFamily: 'var(--font-mono)', letterSpacing: '0.05em' }}>
              {unit.code}
            </span>
          </div>
        )}

        <div className="unit-card-overlay" />

        {/* Code Badge Overlay */}
        <span className="unit-card-code-badge">{unit.code}</span>

        {/* Status Badge Overlay */}
        <div className="unit-card-status-badge">
          <StatusBadge status={unit.status} />
        </div>
      </div>

      {/* Body Section */}
      <div className="unit-card-body">
        {/* Categories */}
        <div className="unit-card-categories">
          {unit.categoryIds.map((catId) => (
            <span key={catId} className="unit-cat-pill">
              {getCategoryName(catId)}
            </span>
          ))}
        </div>

        {/* Title */}
        <h3 className="unit-card-title">{unit.name}</h3>

        {/* Description */}
        <p className="unit-card-desc">
          {unit.description || 'Tidak ada deskripsi spesifikasi tambahan.'}
        </p>
      </div>

      {/* Footer Section */}
      {action && <div className="unit-card-footer">{action}</div>}
    </div>
  );
};
