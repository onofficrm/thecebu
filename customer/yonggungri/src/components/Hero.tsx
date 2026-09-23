import React from 'react';
import { UtensilsCrossed, Phone, MapPin, Clock, Users, Flame, HeartHandshake } from 'lucide-react';
import { RESTAURANT_INFO } from '../data/restaurantData';

interface HeroProps {
  onOpenReservation: () => void;
  onScrollToMenu: () => void;
  onScrollToLocation: () => void;
}

export const Hero: React.FC<HeroProps> = ({
  onOpenReservation,
  onScrollToMenu,
  onScrollToLocation,
}) => {
  return (
    <section id="home" className="relative pt-16 sm:pt-20">
      {/* Hero Visual Container: Optimized Desktop & Mobile Proportions */}
      <div className="relative min-h-[460px] sm:min-h-[560px] lg:min-h-[620px] flex items-center justify-center overflow-hidden bg-[#1E140F]">
        {/* Real Food Photography Background */}
        <picture>
          <img
            src="/assets/images/hero_korean_dining_1790144550799.jpg"
            alt="세부 한식당 용궁리 김치마을 Korean restaurant 대표 상차림"
            className="absolute inset-0 w-full h-full object-cover object-center scale-105"
            loading="eager"
            referrerPolicy="no-referrer"
          />
        </picture>

        {/* High-legibility scrim for WCAG AA compliance */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/55 to-black/40" />

        {/* Content Box */}
        <div className="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 text-center text-white flex flex-col items-center">
          {/* Operating Tag: 24시간 연중무휴 (세부시티) */}
          <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/25 text-amber-200 text-xs sm:text-sm font-semibold tracking-wider mb-4 sm:mb-6">
            <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
            <span>OPEN 24 HOURS</span>
            <span className="text-white/70">·</span>
            <span className="text-white/90">세부시티 24시간 정상영업</span>
          </div>

          {/* 간판 그대로 100% 반영: [용궁리 김치마을] + [Korean restaurant] */}
          <div className="mb-4 sm:mb-6">
            {/* 1. 간판의 메인 한글 볼드 텍스트: "용궁리 김치마을" */}
            <h1 className="text-3xl sm:text-5xl lg:text-7xl font-black tracking-tight leading-tight sm:leading-none text-white drop-shadow-md">
              용궁리 <span className="text-amber-200">김치마을</span>
            </h1>

            {/* 2. 간판의 빨간색 영문 타이포그래피 그대로 반영: "Korean restaurant" */}
            <p className="text-xl sm:text-3xl lg:text-4xl font-extrabold text-[#FF4D4D] tracking-wide mt-2 sm:mt-3 drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] font-sans">
              Korean restaurant
            </p>
          </div>

          {/* 세부 방문객을 위한 간결하고 확실한 서브 안내 문구 */}
          <div className="mb-6 sm:mb-8 max-w-2xl">
            <p className="text-sm sm:text-lg text-stone-200 font-medium leading-relaxed">
              세부에서 만나는 24시간 제대로 된 한국의 맛
            </p>
            <p className="text-xs sm:text-sm text-stone-400 font-normal mt-0.5">
              정갈한 찌개·국밥·탕 요리부터 바삭한 치킨까지, 언제든 든든하게 모십니다
            </p>
          </div>

          {/* PC / Desktop: 가로 3개 CTA | Mobile: 2개 핵심 버튼 [대표 메뉴 보기] + [전화 예약하기] */}
          {/* Desktop (md:flex) */}
          <div className="hidden md:flex items-center justify-center gap-3.5 max-w-2xl w-full mb-3">
            <button
              onClick={onScrollToMenu}
              className="flex-1 min-h-[50px] px-6 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-[#1E140F] font-bold text-base flex items-center justify-center gap-2 transition-transform active:scale-[0.98] shadow-md cursor-pointer"
            >
              <UtensilsCrossed className="w-4 h-4 text-[#1E140F]" />
              <span>대표 메뉴 보기</span>
            </button>

            <button
              onClick={onOpenReservation}
              className="flex-1 min-h-[50px] px-6 py-3.5 rounded-xl bg-white hover:bg-stone-100 text-[#1E140F] font-bold text-base flex items-center justify-center gap-2 transition-transform active:scale-[0.98] shadow-md cursor-pointer"
            >
              <Phone className="w-4 h-4 text-[#8B2E1E]" />
              <span>전화 예약하기</span>
            </button>

            <button
              onClick={onScrollToLocation}
              className="flex-1 min-h-[50px] px-6 py-3.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-medium text-base backdrop-blur-xs border border-white/30 flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer"
            >
              <MapPin className="w-4 h-4 text-amber-300" />
              <span>Google Maps 길찾기</span>
            </button>
          </div>

          {/* Mobile (flex md:hidden): Fast, thumb-friendly 2 Buttons */}
          <div className="flex md:hidden flex-col w-full max-w-xs gap-2.5 mb-2">
            <button
              onClick={onScrollToMenu}
              className="w-full min-h-[48px] px-5 py-3 rounded-xl bg-amber-500 active:bg-amber-400 text-[#1E140F] font-bold text-sm flex items-center justify-center gap-2 shadow-md cursor-pointer"
            >
              <UtensilsCrossed className="w-4 h-4 text-[#1E140F]" />
              <span>대표 메뉴 보기</span>
            </button>

            <a
              href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
              className="w-full min-h-[48px] px-5 py-3 rounded-xl bg-white active:bg-stone-100 text-[#1E140F] font-bold text-sm flex items-center justify-center gap-2 shadow-md cursor-pointer"
            >
              <Phone className="w-4 h-4 text-[#8B2E1E]" />
              <span>전화 예약하기</span>
            </a>
          </div>

          {/* 세부시티 바닐라드 마리아 루이사 로드 초입으로 정확히 교체 */}
          <p className="text-[11px] sm:text-xs text-stone-300/85">
            세부시티 바닐라드 마리아 루이사 로드 초입 (스트리트스케이프 몰·BTC 인근)
          </p>
        </div>
      </div>

      {/* Hero Below Key Highlights Bar */}
      <div className="bg-[#FAF7F2] border-b border-[#EAE3D9] py-4 sm:py-5 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-6">
            <div className="bg-white p-3 sm:p-4 rounded-xl border border-[#EAE3D9]/80 flex items-center gap-2.5 sm:gap-3">
              <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-[#FAF4ED] text-[#8B2E1E] flex items-center justify-center shrink-0">
                <Clock className="w-4 h-4 sm:w-5 sm:h-5" />
              </div>
              <div>
                <p className="text-xs sm:text-sm font-bold text-[#2C1E18]">24시간 영업</p>
                <p className="text-[11px] sm:text-xs text-[#7A695F]">새벽 도착도 안심 식사</p>
              </div>
            </div>

            <div className="bg-white p-3 sm:p-4 rounded-xl border border-[#EAE3D9]/80 flex items-center gap-2.5 sm:gap-3">
              <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-[#FAF4ED] text-[#8B2E1E] flex items-center justify-center shrink-0">
                <Flame className="w-4 h-4 sm:w-5 sm:h-5" />
              </div>
              <div>
                <p className="text-xs sm:text-sm font-bold text-[#2C1E18]">한식 전문</p>
                <p className="text-[11px] sm:text-xs text-[#7A695F]">정통 묵은지와 진한 육수</p>
              </div>
            </div>

            <div className="bg-white p-3 sm:p-4 rounded-xl border border-[#EAE3D9]/80 flex items-center gap-2.5 sm:gap-3">
              <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-[#FAF4ED] text-[#8B2E1E] flex items-center justify-center shrink-0">
                <HeartHandshake className="w-4 h-4 sm:w-5 sm:h-5" />
              </div>
              <div>
                <p className="text-xs sm:text-sm font-bold text-[#2C1E18]">가족 식사</p>
                <p className="text-[11px] sm:text-xs text-[#7A695F]">아이 동반 안심 룸 완비</p>
              </div>
            </div>

            <div className="bg-white p-3 sm:p-4 rounded-xl border border-[#EAE3D9]/80 flex items-center gap-2.5 sm:gap-3">
              <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-[#FAF4ED] text-[#8B2E1E] flex items-center justify-center shrink-0">
                <Users className="w-4 h-4 sm:w-5 sm:h-5" />
              </div>
              <div>
                <p className="text-xs sm:text-sm font-bold text-[#2C1E18]">단체 식사</p>
                <p className="text-[11px] sm:text-xs text-[#7A695F]">골프·모임 프라이빗 룸</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
