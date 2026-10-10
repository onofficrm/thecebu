import React from 'react';
import { Home, Newspaper, MessageCircle, Grid, User, Utensils, Ticket } from 'lucide-react';
import { AppMode, MainTab } from '../../types';

interface BottomNavigationProps {
  currentTab: MainTab;
  onTabChange: (tab: MainTab) => void;
  currentMode: AppMode;
  unreadCount?: number;
}

export const BottomNavigation: React.FC<BottomNavigationProps> = ({
  currentTab,
  onTabChange,
  currentMode,
}) => {
  // Tab configuration per mode
  // Resident: 홈 / 뉴스 / 단톡방 / 전체 / MY
  // Tourist: 홈 / 맛집·투어 / 쿠폰·혜택 / 전체 / MY
  const tabConfig =
    currentMode === 'resident'
      ? [
          {
            id: 'home' as MainTab,
            label: '홈',
            icon: Home,
          },
          {
            id: 'news' as MainTab,
            label: '뉴스',
            icon: Newspaper,
          },
          {
            id: 'chatrooms' as MainTab,
            label: '단톡방',
            icon: MessageCircle,
          },
          {
            id: 'services' as MainTab,
            label: '전체',
            icon: Grid,
          },
          {
            id: 'mypage' as MainTab,
            label: 'MY',
            icon: User,
          },
        ]
      : [
          {
            id: 'home' as MainTab,
            label: '홈',
            icon: Home,
          },
          {
            id: 'search' as MainTab,
            label: '맛집',
            icon: Utensils,
          },
          {
            id: 'benefits' as MainTab,
            label: '혜택',
            icon: Ticket,
          },
          {
            id: 'services' as MainTab,
            label: '전체',
            icon: Grid,
          },
          {
            id: 'mypage' as MainTab,
            label: 'MY',
            icon: User,
          },
        ];

  return (
    <nav
      aria-label="하단 내비게이션"
      className="relative z-30 shrink-0 bg-white/95 backdrop-blur-md border-t border-[#E1ECF3] pb-safe"
    >
      <div className="max-w-md mx-auto grid grid-cols-5 h-16 items-center px-1">
        {tabConfig.map((item) => {
          const isActive = currentTab === item.id;
          const Icon = item.icon;

          return (
            <button
              key={item.id}
              type="button"
              onClick={() => onTabChange(item.id)}
              className={`flex flex-col items-center justify-center min-h-[48px] py-1 transition-all relative ${
                isActive
                  ? 'text-[#079BE8]'
                  : 'text-[#617789] hover:text-[#183247]'
              }`}
            >
              <div
                className={`p-1 rounded-xl transition-all ${
                  isActive ? 'scale-105 font-bold text-[#079BE8]' : ''
                }`}
              >
                <Icon
                  size={20}
                  strokeWidth={isActive ? 2.5 : 1.75}
                />
              </div>
              <span
                className={`text-[11px] tracking-tight leading-tight transition-all ${
                  isActive ? 'font-bold text-[#075A9D]' : 'font-medium text-[#617789]'
                }`}
              >
                {item.label}
              </span>
              {isActive && (
                <span
                  className="absolute bottom-1 w-1.5 h-1.5 rounded-full bg-[#079BE8]"
                />
              )}
            </button>
          );
        })}
      </div>
    </nav>
  );
};
