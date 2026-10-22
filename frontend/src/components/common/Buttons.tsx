import React from 'react';

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  children?: React.ReactNode;
  icon?: React.ReactNode;
  variant?: 'primary' | 'secondary' | 'danger';
  size?: 'sm' | 'md';
}

export const PrimaryButton: React.FC<ButtonProps> = ({
  children,
  icon,
  size = 'md',
  className = '',
  ...props
}) => {
  return (
    <button
      className={`btn-primary ${size === 'sm' ? 'btn-sm' : ''} ${className}`}
      {...props}
    >
      {icon && <span className="btn-icon-wrapper">{icon}</span>}
      {children}
    </button>
  );
};

export const SecondaryButton: React.FC<ButtonProps> = ({
  children,
  icon,
  size = 'md',
  className = '',
  ...props
}) => {
  return (
    <button
      className={`btn-secondary ${size === 'sm' ? 'btn-sm' : ''} ${className}`}
      {...props}
    >
      {icon && <span className="btn-icon-wrapper">{icon}</span>}
      {children}
    </button>
  );
};

export const DangerButton: React.FC<ButtonProps> = ({
  children,
  icon,
  size = 'md',
  className = '',
  ...props
}) => {
  return (
    <button
      className={`btn-danger ${size === 'sm' ? 'btn-sm' : ''} ${className}`}
      {...props}
    >
      {icon && <span className="btn-icon-wrapper">{icon}</span>}
      {children}
    </button>
  );
};

interface IconButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  icon: React.ReactNode;
  title?: string;
}

export const IconButton: React.FC<IconButtonProps> = ({
  icon,
  title,
  className = '',
  ...props
}) => {
  return (
    <button
      type="button"
      className={`btn-icon ${className}`}
      title={title}
      aria-label={title}
      {...props}
    >
      {icon}
    </button>
  );
};
