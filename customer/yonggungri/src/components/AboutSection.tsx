import React from 'react';
import { Clock, UtensilsCrossed, Heart, Users } from 'lucide-react';
import { RESTAURANT_INFO } from '../data/restaurantData';

export const AboutSection: React.FC = () => {
  const coreFeatures = [
    {
      engLabel: '24 HOURS',
      koTitle: '24시간 운영',
      icon: Clock,
      desc: '새벽 비행기 도착 시각이나 늦은 밤에도 언제든 따뜻하게 드실 수 있습니다.',
    },
    {
      engLabel: 'KOREAN FOOD',
      koTitle: '다양한 한식 메뉴',
      icon: UtensilsCrossed,
      desc: '얼큰한 찌개부터 든든한 전골, 볶음, 고기구이까지 다채로운 메뉴를 제공합니다.',
    },
    {
      engLabel: 'FAMILY',
      koTitle: '가족 식사',
      icon: Heart,
      desc: '아이 동반 가족도 자극적이지 않고 편안하게 식사할 수 있는 환경을 갖추었습니다.',
    },
    {
      engLabel: 'GROUP',
      koTitle: '단체 식사',
      icon: Users,
      desc: '골프 투어 및 단체 여행객이 편안하게 함께 모여 식사할 수 있습니다.',
    },
  ];

  return (
    <section id="about" className="py-16 sm:py-20 bg-[#FCFAF7]">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        {/* Section Header */}
        <p className="text-xs sm:text-sm font-semibold tracking-wider text-[#8B2E1E] uppercase mb-2">
          ABOUT YONGGUNGRI
        </p>

        <h2 className="text-2xl sm:text-4xl font-extrabold text-[#2C1E18] tracking-tight leading-snug mb-4">
          세부에서 한국 음식이 생각날 때
        </h2>

        {/* Short, concise body */}
        <p className="text-base sm:text-lg text-[#5E4D43] leading-relaxed max-w-2xl mx-auto text-balance">
          <strong className="font-semibold text-[#2C1E18]">{RESTAURANT_INFO.fullName}</strong>는
          <br className="hidden sm:inline" />
          {' '}세부에서 편안하게 즐길 수 있는 다양한 한식 메뉴를 선보이는 한식당입니다.
        </p>

        {/* 4 Core Features Grid */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mt-12 text-left">
          {coreFeatures.map((feat) => {
            const Icon = feat.icon;
            return (
              <div
                key={feat.engLabel}
                className="bg-white p-5 sm:p-6 rounded-2xl border border-[#EAE3D9] hover:border-[#D9CFBF] transition-all flex flex-col justify-between shadow-2xs group"
              >
                <div>
                  <div className="w-11 h-11 rounded-xl bg-[#FAF4ED] text-[#8B2E1E] flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                    <Icon className="w-5 h-5" />
                  </div>
                  <span className="text-[11px] font-bold text-[#8B2E1E] tracking-wider uppercase block mb-1">
                    {feat.engLabel}
                  </span>
                  <h3 className="text-base sm:text-lg font-bold text-[#2C1E18] mb-1.5">
                    {feat.koTitle}
                  </h3>
                  <p className="text-xs text-[#7A695F] leading-relaxed">
                    {feat.desc}
                  </p>
                </div>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
};
