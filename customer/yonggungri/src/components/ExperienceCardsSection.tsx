import React from 'react';
import { Heart, Users2, Sparkles, Moon } from 'lucide-react';

export const ExperienceCardsSection: React.FC = () => {
  const experiences = [
    {
      eng: 'FAMILY',
      title: '가족과 함께하는 식사',
      desc: '아이들이 좋아하는 맵지 않은 갈비탕, 계란찜, 불고기전골과 편안한 프라이빗 룸이 준비되어 있습니다.',
      icon: Heart,
    },
    {
      eng: 'FRIENDS',
      title: '친구들과 즐기는 식사',
      desc: '얼큰한 감자탕과 매콤한 닭볶음탕, 시원한 산미구엘 맥주와 소주 한잔을 곁들이며 이야기 나누기 좋습니다.',
      icon: Sparkles,
    },
    {
      eng: 'GROUP',
      title: '여러 명이 함께하는 식사',
      desc: '호핑투어 동호회, 골프 모임 등 다인원도 여유롭게 수용 가능한 넓은 테이블과 단체석을 갖추었습니다.',
      icon: Users2,
    },
    {
      eng: 'LATE NIGHT',
      title: '늦은 시간에도 편하게',
      desc: '24시간 연중무휴로 운영되어 새벽 비행기 도착 후나 늦은 저녁 일정 후에도 언제든 정갈하게 식사하실 수 있습니다.',
      icon: Moon,
    },
  ];

  return (
    <section className="py-14 sm:py-20 bg-[#F8F5EE] border-b border-[#EAE3D9]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-2xl mx-auto mb-10 sm:mb-12">
          <p className="text-xs sm:text-sm font-bold tracking-widest text-[#8B2E1E] uppercase mb-1.5">
            EXPERIENCE
          </p>
          <h2 className="text-2xl sm:text-3xl font-extrabold text-[#2C1E18] tracking-tight">
            어떤 순간에도 편안한 식사
          </h2>
        </div>

        {/* 4 Usage Situation Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
          {experiences.map((exp) => {
            const Icon = exp.icon;
            return (
              <div
                key={exp.eng}
                className="bg-white p-6 rounded-2xl border border-[#EAE3D9] hover:border-[#D0C2B0] hover:shadow-md transition-all flex flex-col justify-between"
              >
                <div>
                  <div className="w-11 h-11 rounded-xl bg-[#FAF4ED] text-[#8B2E1E] flex items-center justify-center mb-4">
                    <Icon className="w-5 h-5" />
                  </div>
                  <span className="text-[11px] font-bold text-[#8B2E1E] tracking-wider uppercase block mb-1">
                    {exp.eng}
                  </span>
                  <h3 className="text-base sm:text-lg font-bold text-[#2C1E18] mb-2 leading-snug">
                    {exp.title}
                  </h3>
                  <p className="text-xs sm:text-[13px] text-[#5E4D43] leading-relaxed">
                    {exp.desc}
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
