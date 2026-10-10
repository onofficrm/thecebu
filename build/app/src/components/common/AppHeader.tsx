import React from 'react';
import { ArrowLeft, Bell, Search, ChevronDown } from 'lucide-react';
import { AppMode } from '../../types';
import { BrandLogo } from './BrandLogo';

interface AppHeaderProps {
  title?: string;
  showBack?: boolean;
  onBack?: () => void;
  currentMode: AppMode;
  onOpenModeSheet: () => void;
  onOpenNotifications: () => void;
  onOpenSearch: () => void;
  unreadCount?: number;
}

export const AppHeader: React.FC<AppHeaderProps> = ({
  title,
  showBack = false,
  onBack,
  currentMode,
  onOpenModeSheet,
  onOpenNotifications,
  onOpenSearch,
  unreadCount = 2,
}) => {
  return (
    <header className="app-safe-top sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-[#E1ECF3] transition-colors">
      <div className="flex items-center justify-between h-14 px-3 sm:px-4 gap-2">
        {/* Left: Brand or Back */}
        <div className="flex items-center gap-1.5 shrink-0">
          {showBack ? (
            <button
              type="button"
              onClick={onBack}
              aria-label="뒤로 가기"
              className="min-h-[44px] min-w-[44px] -ml-2 flex items-center justify-center text-[#183247] hover:text-[#079BE8] transition-colors rounded-full"
            >
              <ArrowLeft size={22} />
            </button>
          ) : (
            <BrandLogo size="sm" variant="full" />
          )}
        </div>

        {/* Center: Title or Mode Selector Dropdown Button [교민 모드 ▼] */}
        <div className="flex-1 flex items-center justify-center min-w-0 px-1">
          {title ? (
            <h1 className="text-sm font-bold text-[#183247] truncate text-center">
              {title}
            </h1>
          ) : (
            <button
              type="button"
              onClick={onOpenModeSheet}
              aria-label="사용자 모드 변경 열기"
              className={`min-h-[40px] px-3 py-1 flex items-center gap-1.5 text-xs font-bold rounded-full transition-all border shadow-2xs active:scale-[0.97] ${
                currentMode === 'resident'
                  ? 'bg-[#E9FBFE] text-[#008AAA] border-[#B9E5FC] hover:bg-[#D9F7FC]'
                  : 'bg-[#EAF8FF] text-[#0879E7] border-[#C7E9FB] hover:bg-[#D9F1FD]'
              }`}
            >
              <span
                className={`w-2 h-2 rounded-full shrink-0 ${
                  currentMode === 'resident' ? 'bg-[#00A8CC]' : 'bg-[#079BE8]'
                }`}
              />
              <span className="truncate">
                {currentMode === 'resident' ? '교민 모드' : '관광객 모드'}
              </span>
              <ChevronDown size={14} className="text-[#617789] shrink-0 ml-0.5" />
            </button>
          )}
        </div>

        {/* Right: Actions */}
        <div className="flex items-center gap-0.5 shrink-0">
          {/* Search Trigger */}
          <button
            type="button"
            onClick={onOpenSearch}
            aria-label="통합 검색 열기"
            className="min-h-[44px] min-w-[44px] flex items-center justify-center text-[#617789] hover:text-[#079BE8] transition-colors rounded-full"
          >
            <Search size={20} />
          </button>

          {/* Notifications Trigger */}
          <button
            type="button"
            onClick={onOpenNotifications}
            aria-label="알림 열기"
            className="relative min-h-[44px] min-w-[44px] flex items-center justify-center text-[#617789] hover:text-[#079BE8] transition-colors rounded-full"
          >
            <Bell size={20} />
            {unreadCount > 0 && (
              <span className="absolute top-2.5 right-2.5 w-4 h-4 bg-[#0879E7] text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                {unreadCount}
              </span>
            )}
          </button>

        </div>
      </div>
    </header>
  );
};
