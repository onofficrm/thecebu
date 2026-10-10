import React, { useState } from 'react';
import { Home, Plane, ArrowRight } from 'lucide-react';
import { AppMode } from '../types';
import { BrandLogo } from '../components/common/BrandLogo';

interface OnboardingModeSelectScreenProps {
  onSelectMode: (mode: AppMode) => void;
}

export const OnboardingModeSelectScreen: React.FC<OnboardingModeSelectScreenProps> = ({
  onSelectMode,
}) => {
  const [isSubmitting, setIsSubmitting] = useState<AppMode | null>(null);

  const handleCardClick = (mode: AppMode) => {
    setIsSubmitting(mode);
    setTimeout(() => {
      onSelectMode(mode);
    }, 150);
  };

  return (
    <div className="app-safe-top min-h-full flex flex-col justify-between px-6 pb-6 sm:p-8 bg-gradient-to-b from-[#EAF8FF] via-[#F7FBFE] to-white select-none">
      {/* 1. 상단: 세부어때 공식 로고 & 환영 카피 */}
      <div className="pt-6 sm:pt-4 text-center flex flex-col items-center">
        <BrandLogo size="lg" variant="full" className="mb-3.5" />

        <p className="text-xs font-semibold text-[#079BE8] tracking-wide">
          세부에 살 때도, 여행할 때도.
        </p>
        <h1 className="text-xl sm:text-2xl font-black text-[#183247] tracking-tight mt-1 leading-snug">
          어떤 세부 정보를 찾고 계신가요?
        </h1>
      </div>

      {/* 2. 중앙: 커다란 선택 카드 2개 */}
      <div className="my-6 space-y-4">
        {/* 카드 A: 교민 모드 (brand-safe tropical cyan) */}
        <button
          type="button"
          onClick={() => handleCardClick('resident')}
          disabled={isSubmitting !== null}
          className={`w-full min-h-[140px] p-5 rounded-3xl border-2 transition-all duration-200 text-left relative overflow-hidden group shadow-xs active:scale-[0.98] ${
            isSubmitting === 'resident'
              ? 'border-[#00A8CC] bg-[#E9FBFE] ring-2 ring-[#00C5E8]/25 scale-[0.99]'
              : 'border-[#E1ECF3] bg-white hover:border-[#00A8CC] hover:shadow-md hover:bg-[#F1F9FE]'
          }`}
        >
          <div className="absolute -right-6 -bottom-6 w-28 h-28 bg-[#00C5E8]/10 rounded-full pointer-events-none group-hover:scale-125 transition-transform duration-300" />

          <div className="flex items-start gap-4">
            <div className="w-14 h-14 rounded-2xl bg-[#E9FBFE] text-[#008AAA] flex items-center justify-center shrink-0 border border-[#B9E5FC] group-hover:bg-[#00A8CC] group-hover:text-white transition-colors duration-200 shadow-xs">
              <Home size={28} strokeWidth={2.2} />
            </div>

            <div className="flex-1 min-w-0 pt-0.5">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-extrabold text-[#008AAA] uppercase tracking-wider">
                  RESIDENT MODE
                </span>
                <span className="text-xs font-bold text-[#008AAA] flex items-center gap-0.5 opacity-90 group-hover:translate-x-1 transition-transform">
                  선택 <ArrowRight size={13} />
                </span>
              </div>

              <h2 className="text-lg font-black text-[#183247] tracking-tight mt-0.5 group-hover:text-[#008AAA] transition-colors">
                세부에 살고 있어요
              </h2>

              <p className="text-xs text-[#617789] mt-1.5 leading-relaxed">
                필리핀 뉴스부터 생활정보, 중고거래, 교민 단톡방까지
              </p>
            </div>
          </div>

          {/* Quick tags */}
          <div className="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-[#ECF2F6]">
            {['필리핀뉴스', '생활정보', '중고거래', '구인구직', '단톡방'].map((tag) => (
              <span
                key={tag}
                className="text-[10px] font-semibold text-[#008AAA] bg-[#E9FBFE] px-2 py-0.5 rounded-md border border-[#B9E5FC]"
              >
                {tag}
              </span>
            ))}
          </div>
        </button>

        {/* 카드 B: 관광객 모드 (Ocean Blue) */}
        <button
          type="button"
          onClick={() => handleCardClick('tourist')}
          disabled={isSubmitting !== null}
          className={`w-full min-h-[140px] p-5 rounded-3xl border-2 transition-all duration-200 text-left relative overflow-hidden group shadow-xs active:scale-[0.98] ${
            isSubmitting === 'tourist'
              ? 'border-[#079BE8] bg-[#F1F9FE] ring-2 ring-[#079BE8]/30 scale-[0.99]'
              : 'border-[#E1ECF3] bg-white hover:border-[#079BE8] hover:shadow-md hover:bg-[#F1F9FE]'
          }`}
        >
          <div className="absolute -right-6 -bottom-6 w-28 h-28 bg-[#079BE8]/10 rounded-full pointer-events-none group-hover:scale-125 transition-transform duration-300" />

          <div className="flex items-start gap-4">
            <div className="w-14 h-14 rounded-2xl bg-[#EAF8FF] text-[#079BE8] flex items-center justify-center shrink-0 border border-[#B9E5FC] group-hover:bg-[#079BE8] group-hover:text-white transition-colors duration-200 shadow-xs">
              <Plane size={28} strokeWidth={2.2} />
            </div>

            <div className="flex-1 min-w-0 pt-0.5">
              <div className="flex items-center justify-between">
                <span className="text-[11px] font-extrabold text-[#079BE8] uppercase tracking-wider">
                  TOURIST MODE
                </span>
                <span className="text-xs font-bold text-[#079BE8] flex items-center gap-0.5 opacity-90 group-hover:translate-x-1 transition-transform">
                  선택 <ArrowRight size={13} />
                </span>
              </div>

              <h2 className="text-lg font-black text-[#183247] tracking-tight mt-0.5 group-hover:text-[#079BE8] transition-colors">
                세부로 여행 왔어요
              </h2>

              <p className="text-xs text-[#617789] mt-1.5 leading-relaxed">
                맛집, 현지 업체, 이벤트와 할인쿠폰까지
              </p>
            </div>
          </div>

          {/* Quick tags */}
          <div className="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-[#ECF2F6]">
            {['맛집찾기', '스파·투어업체', '이벤트', '할인쿠폰'].map((tag) => (
              <span
                key={tag}
                className="text-[10px] font-semibold text-[#0879E7] bg-[#F1F9FE] px-2 py-0.5 rounded-md border border-[#C7E9FB]"
              >
                {tag}
              </span>
            ))}
          </div>
        </button>
      </div>

      {/* 3. 하단 안내 문구 */}
      <div className="pt-1 pb-safe text-center space-y-1">
        <p className="text-xs font-semibold text-[#183247]">
          선택은 언제든 변경할 수 있어요.
        </p>
        <p className="text-[11px] text-[#8799A8]">
          로그인 없이도 세부어때의 모든 서비스를 둘러볼 수 있습니다.
        </p>
      </div>
    </div>
  );
};
