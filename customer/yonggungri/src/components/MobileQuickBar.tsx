import React from 'react';
import { Phone, MapPin, UtensilsCrossed } from 'lucide-react';
import { RESTAURANT_INFO } from '../data/restaurantData';

interface MobileQuickBarProps {
  onOpenReservation: () => void;
  onScrollToMenu: () => void;
  onScrollToLocation: () => void;
}

export const MobileQuickBar: React.FC<MobileQuickBarProps> = ({
  onScrollToMenu,
  onScrollToLocation,
}) => {
  return (
    <aside
      aria-label="모바일 하단 빠른 실행 메뉴"
      className="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-[#FCFAF7]/95 backdrop-blur-md border-t border-[#EAE3D9] px-4 py-2.5 shadow-[0_-4px_16px_rgba(0,0,0,0.08)]"
    >
      {/* 3개의 핵심 버튼: ☎ 전화하기, 📍 길찾기, 🍽 메뉴 (PC에서는 숨김 처리) */}
      <div className="grid grid-cols-3 gap-2.5 items-center max-w-md mx-auto">
        {/* 1. ☎ 전화하기 (직접 기기 전화 앱 연결) */}
        <a
          href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
          className="flex items-center justify-center gap-1.5 py-3 px-2 rounded-xl bg-[#8B2E1E] text-white font-bold text-xs shadow-xs active:scale-[0.97] transition-transform cursor-pointer"
        >
          <Phone className="w-4 h-4 fill-white" />
          <span>전화하기</span>
        </a>

        {/* 2. 📍 길찾기 (Google Maps 및 위치 섹션 연동) */}
        <a
          href={RESTAURANT_INFO.googleMapsUrl}
          target="_blank"
          rel="noreferrer"
          className="flex items-center justify-center gap-1.5 py-3 px-2 rounded-xl bg-white border border-[#D9CFBF] text-[#2C1E18] font-bold text-xs active:bg-[#FAF4ED] active:scale-[0.97] transition-all cursor-pointer shadow-2xs"
        >
          <MapPin className="w-4 h-4 text-[#8B2E1E]" />
          <span>길찾기</span>
        </a>

        {/* 3. 🍽 메뉴 (대표 메뉴 & 전체 메뉴 부드러운 스크롤) */}
        <button
          onClick={onScrollToMenu}
          className="flex items-center justify-center gap-1.5 py-3 px-2 rounded-xl bg-[#2C1E18] text-white font-bold text-xs active:bg-[#1E140F] active:scale-[0.97] transition-transform cursor-pointer shadow-xs"
        >
          <UtensilsCrossed className="w-4 h-4 text-amber-300" />
          <span>메뉴</span>
        </button>
      </div>
    </aside>
  );
};
