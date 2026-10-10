import React from 'react';

interface CategoryChipProps {
  label: string;
  active: boolean;
  onClick: () => void;
  count?: number;
}

export const CategoryChip: React.FC<CategoryChipProps> = ({
  label,
  active,
  onClick,
  count,
}) => {
  return (
    <button
      type="button"
      onClick={onClick}
      className={`min-h-[38px] px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap shrink-0 flex items-center gap-1.5 border ${
        active
          ? 'bg-[#079BE8] text-white border-[#079BE8] shadow-xs'
          : 'bg-white text-[#617789] border-[#E1ECF3] hover:bg-[#F1F9FE] hover:text-[#183247]'
      }`}
    >
      <span>{label}</span>
      {count !== undefined && (
        <span
          className={`text-[10px] px-1.5 py-0.2 rounded font-bold ${
            active ? 'bg-white/20 text-white' : 'bg-[#EAF8FF] text-[#075A9D]'
          }`}
        >
          {count}
        </span>
      )}
    </button>
  );
};
