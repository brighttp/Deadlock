import React from 'react';

interface StatCardProps {
  label: string;
  value: number | string;
  subtext?: string;
  icon?: React.ReactNode;
  accentColor?: string;
  isAmber?: boolean;
  progress?: number;
  progressColor?: 'amber' | 'green';
  children?: React.ReactNode;
}

export const StatCard: React.FC<StatCardProps> = ({
  label,
  value,
  subtext,
  icon,
  accentColor,
  isAmber = false,
  progress,
  progressColor = 'amber',
  children,
}) => {
  const isAmberEffective = isAmber || accentColor === '#F0B247' || accentColor === 'var(--color-warning)';

  return (
    <div className={`stat-card ${isAmberEffective ? 'accent-amber' : ''}`}>
      <div className="stat-header">
        <span className="stat-label">{label}</span>
        {icon && (
          <div style={{ color: isAmberEffective ? '#F0B247' : (accentColor || 'var(--text-muted)') }}>
            {icon}
          </div>
        )}
      </div>
      <div
        className="stat-value"
        style={{
          color: accentColor && accentColor.includes('danger') && Number(value) > 0 ? 'var(--color-danger)' : '#4ADE80',
          textShadow: accentColor && accentColor.includes('danger') && Number(value) > 0 ? '0 0 20px rgba(239, 68, 68, 0.45)' : '0 0 22px rgba(74, 222, 128, 0.45)',
        }}
      >
        {value}
      </div>

      {progress !== undefined && (
        <div className="stat-meter-track">
          <div
            className={`stat-meter-fill ${progressColor === 'green' ? 'fill-green' : ''}`}
            style={{ width: `${Math.min(100, Math.max(0, progress))}%` }}
          />
        </div>
      )}

      {subtext && <div className="stat-sub">{subtext}</div>}
      {children}
    </div>
  );
};
