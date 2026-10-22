import React from 'react';

interface FormFieldProps {
  label: string;
  required?: boolean;
  error?: string;
  hint?: string;
  htmlFor?: string;
  children: React.ReactNode;
  className?: string;
}

export const FormField: React.FC<FormFieldProps> = ({
  label,
  required = false,
  error,
  hint,
  htmlFor,
  children,
  className = '',
}) => {
  return (
    <div className={`form-group ${className}`}>
      <label htmlFor={htmlFor} className="form-label">
        <span>
          {label}
          {required && <span className="required-dot">*</span>}
        </span>
      </label>
      {children}
      {error && <span className="form-error">{error}</span>}
      {!error && hint && <span className="form-hint">{hint}</span>}
    </div>
  );
};
