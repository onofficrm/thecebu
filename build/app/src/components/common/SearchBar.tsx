import React, { useRef } from 'react';
import { Search, X } from 'lucide-react';

interface SearchBarProps {
  value: string;
  onChange: (value: string) => void;
  onSearch?: (term: string) => void;
  placeholder?: string;
  autoFocus?: boolean;
}

export const SearchBar: React.FC<SearchBarProps> = ({
  value,
  onChange,
  onSearch,
  placeholder = '세부 맛집, 중고물품, 뉴스, 구인 검색...',
  autoFocus = false,
}) => {
  const inputRef = useRef<HTMLInputElement>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (onSearch) {
      onSearch(value.trim());
    }
  };

  const handleClear = () => {
    onChange('');
    inputRef.current?.focus();
  };

  return (
    <form onSubmit={handleSubmit} className="relative w-full">
      <div className="relative flex items-center">
        <div className="absolute left-3.5 text-[#617789] pointer-events-none flex items-center justify-center">
          <Search size={18} />
        </div>
        <input
          ref={inputRef}
          type="search"
          value={value}
          onChange={(e) => onChange(e.target.value)}
          placeholder={placeholder}
          autoFocus={autoFocus}
          className="w-full h-11 pl-10 pr-10 bg-white border border-[#E1ECF3] rounded-xl text-sm text-[#183247] placeholder-[#8799A8] focus:outline-none focus:ring-2 focus:ring-[#079BE8]/25 focus:border-[#079BE8] transition-all shadow-xs"
        />
        {value.length > 0 && (
          <button
            type="button"
            onClick={handleClear}
            aria-label="입력 내용 지우기"
            className="absolute right-2.5 min-h-[36px] min-w-[36px] flex items-center justify-center text-[#8799A8] hover:text-[#183247] rounded-full"
          >
            <X size={16} />
          </button>
        )}
      </div>
    </form>
  );
};
