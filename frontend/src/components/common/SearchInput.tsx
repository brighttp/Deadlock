import React from 'react';
import { Search, X } from 'lucide-react';

interface SearchInputProps {
  value: string;
  onChange: (val: string) => void;
  placeholder?: string;
  className?: string;
}

export const SearchInput: React.FC<SearchInputProps> = ({
  value,
  onChange,
  placeholder = 'Cari data...',
  className = '',
}) => {
  return (
    <div className={`search-container ${className}`}>
      <Search size={16} className="search-icon" />
      <input
        type="text"
        className="input-control search-input"
        value={value}
        onChange={(e) => onChange(e.target.value)}
        placeholder={placeholder}
      />
      {value && (
        <button
          type="button"
          className="search-clear"
          onClick={() => onChange('')}
          title="Hapus pencarian"
        >
          <X size={14} />
        </button>
      )}
    </div>
  );
};
