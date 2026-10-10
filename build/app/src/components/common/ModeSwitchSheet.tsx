import React from 'react';
import { AppMode } from '../../types';
import { BottomSheet } from './BottomSheet';
import { Home, Plane, Check, Sparkles } from 'lucide-react';

interface ModeSwitchSheetProps {
  isOpen: boolean;
  onClose: () => void;
  currentMode: AppMode;
  onSelectMode: (mode: AppMode) => void;
}

export const ModeSwitchSheet: React.FC<ModeSwitchSheetProps> = ({
  isOpen,
  onClose,
  currentMode,
  onSelectMode,
}) => {
  const handleSelect = (mode: AppMode) => {
    onSelectMode(mode);
    onClose();
  };

  return (
    <BottomSheet isOpen={isOpen} onClose={onClose} title="사용자 모드 변경">
      <div className="space-y-3 pb-2">
        <p className="text-xs text-[#617789] leading-relaxed">
          체류 목적에 따라 홈 화면의 주요 메뉴와 콘텐츠 배치가 최적화됩니다.
        </p>

        {/* 1. 교민 모드 선택지 */}
        <button
          type="button"
          onClick={() => handleSelect('resident')}
          className={`w-full p-4 rounded-2xl border-2 text-left transition-all flex items-start justify-between gap-3 min-h-[88px] active:scale-[0.99] ${
            currentMode === 'resident'
              ? 'border-[#00A8CC] bg-[#E9FBFE] shadow-xs'
              : 'border-[#E1ECF3] bg-white hover:border-[#B9E5FC]'
          }`}
        >
          <div className="flex items-start gap-3.5 min-w-0">
            <div
              className={`w-11 h-11 rounded-xl flex items-center justify-center shrink-0 mt-0.5 ${
                currentMode === 'resident'
                  ? 'bg-[#00A8CC] text-white shadow-xs'
                  : 'bg-[#E9FBFE] text-[#008AAA]'
              }`}
            >
              <Home size={22} strokeWidth={2.2} />
            </div>

            <div className="min-w-0">
              <div className="flex items-center gap-2">
                <h3 className="text-sm font-bold text-[#183247]">
                  교민 모드
                </h3>
                {currentMode === 'resident' && (
                  <span className="text-[10px] font-bold text-[#008AAA] bg-white px-2 py-0.5 rounded-full border border-[#B9E5FC]">
                    현재 적용 중
                  </span>
                )}
              </div>
              <p className="text-xs text-[#617789] mt-1 leading-snug">
                필리핀 뉴스, 생활정보, 중고거래, 구인구직, 부동산, 단톡방 중심
              </p>
            </div>
          </div>

          <div className="shrink-0 mt-1">
            {currentMode === 'resident' ? (
              <div className="w-6 h-6 rounded-full bg-[#00A8CC] text-white flex items-center justify-center">
                <Check size={14} strokeWidth={2.5} />
              </div>
            ) : (
              <div className="w-6 h-6 rounded-full border border-[#E1ECF3]" />
            )}
          </div>
        </button>

        {/* 2. 관광객 모드 선택지 */}
        <button
          type="button"
          onClick={() => handleSelect('tourist')}
          className={`w-full p-4 rounded-2xl border-2 text-left transition-all flex items-start justify-between gap-3 min-h-[88px] active:scale-[0.99] ${
            currentMode === 'tourist'
              ? 'border-[#079BE8] bg-[#F1F9FE] shadow-xs'
              : 'border-[#E1ECF3] bg-white hover:border-[#B9E5FC]'
          }`}
        >
          <div className="flex items-start gap-3.5 min-w-0">
            <div
              className={`w-11 h-11 rounded-xl flex items-center justify-center shrink-0 mt-0.5 ${
                currentMode === 'tourist'
                  ? 'bg-[#079BE8] text-white shadow-xs'
                  : 'bg-[#EAF8FF] text-[#079BE8]'
              }`}
            >
              <Plane size={22} strokeWidth={2.2} />
            </div>

            <div className="min-w-0">
              <div className="flex items-center gap-2">
                <h3 className="text-sm font-bold text-[#183247]">
                  관광객 모드
                </h3>
                {currentMode === 'tourist' && (
                  <span className="text-[10px] font-bold text-[#079BE8] bg-white px-2 py-0.5 rounded-full border border-[#B9E5FC]">
                    현재 적용 중
                  </span>
                )}
              </div>
              <p className="text-xs text-[#617789] mt-1 leading-snug">
                맛집찾기, 현지 업체(스파/투어), 이벤트, 할인쿠폰 중심
              </p>
            </div>
          </div>

          <div className="shrink-0 mt-1">
            {currentMode === 'tourist' ? (
              <div className="w-6 h-6 rounded-full bg-[#079BE8] text-white flex items-center justify-center">
                <Check size={14} strokeWidth={2.5} />
              </div>
            ) : (
              <div className="w-6 h-6 rounded-full border border-[#E1ECF3]" />
            )}
          </div>
        </button>

        {/* 하단 안내 */}
        <div className="pt-2 px-1 flex items-center gap-2 text-[11px] text-[#617789]">
          <Sparkles size={14} className="text-[#079BE8] shrink-0" />
          <span>어느 모드를 선택하셔도 전체메뉴에서 10개 모든 서비스를 이용하실 수 있습니다.</span>
        </div>
      </div>
    </BottomSheet>
  );
};
