import React from 'react';
import { AppMode } from '../../types';
import { Home, Compass } from 'lucide-react';

interface ModeSwitcherProps {
  currentMode: AppMode;
  onModeChange: (mode: AppMode) => void;
  compact?: boolean;
}

export const ModeSwitcher: React.FC<ModeSwitcherProps> = ({
  currentMode,
  onModeChange,
  compact = false,
}) => {
  return (
    <div
      role="group"
      aria-label="사용자 모드 선택"
      className={`relative inline-flex items-center p-1 bg-[#EAF8FF] border border-[#E1ECF3] rounded-xl transition-all ${
        compact ? 'h-9 text-xs' : 'h-11 w-full text-sm'
      }`}
    >
      <button
        type="button"
        onClick={() => onModeChange('resident')}
        className={`flex-1 flex items-center justify-center gap-1.5 min-h-[36px] px-3 font-semibold rounded-lg transition-all duration-200 select-none ${
          currentMode === 'resident'
            ? 'bg-[#00A8CC] text-white shadow-xs'
            : 'text-[#617789] hover:text-[#183247]'
        }`}
      >
        <Home size={compact ? 14 : 16} />
        <span>교민 모드</span>
      </button>

      <button
        type="button"
        onClick={() => onModeChange('tourist')}
        className={`flex-1 flex items-center justify-center gap-1.5 min-h-[36px] px-3 font-semibold rounded-lg transition-all duration-200 select-none ${
          currentMode === 'tourist'
            ? 'bg-[#079BE8] text-white shadow-xs'
            : 'text-[#617789] hover:text-[#183247]'
        }`}
      >
        <Compass size={compact ? 14 : 16} />
        <span>관광객 모드</span>
      </button>
    </div>
  );
};
