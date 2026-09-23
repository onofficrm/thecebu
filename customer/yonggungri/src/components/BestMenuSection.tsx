import React from 'react';
import { BEST_MENU_ITEMS } from '../data/restaurantData';
import { MenuItem } from '../types';
import { ChevronDown, Sparkles } from 'lucide-react';

interface BestMenuSectionProps {
  onSelectMenuItem: (item: MenuItem) => void;
  onScrollToAllMenu: () => void;
}

export const BestMenuSection: React.FC<BestMenuSectionProps> = ({
  onSelectMenuItem,
  onScrollToAllMenu,
}) => {
  return (
    <section id="best-menu" className="py-14 sm:py-20 bg-[#F8F5EE] border-t border-[#EAE3D9]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="text-center max-w-3xl mx-auto mb-10 sm:mb-14">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#FAF4ED] border border-[#EAE3D9] text-[#8B2E1E] text-xs font-bold tracking-widest uppercase mb-2.5">
            <Sparkles className="w-3.5 h-3.5" />
            <span>BEST MENU</span>
          </div>
          <h2 className="text-2xl sm:text-4xl font-extrabold text-[#2C1E18] tracking-tight">
            용궁리에서 많이 찾는 대표 메뉴
          </h2>
          <p className="mt-2 text-sm sm:text-base text-[#5E4D43]">
            세부 여행객과 현지 교민들이 가장 즐겨 찾으시는 인기 메뉴 4선
          </p>
        </div>

        {/* 4 Cards: Desktop 4-Grid / Mobile 2-Grid or 1-Col */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
          {BEST_MENU_ITEMS.map((item) => (
            <div
              key={item.id}
              onClick={() => onSelectMenuItem(item)}
              className="group bg-white rounded-2xl border border-[#EAE3D9] overflow-hidden hover:border-[#D0C2B0] hover:shadow-lg transition-all duration-300 flex flex-col cursor-pointer"
            >
              {/* Food Photography */}
              <div className="relative aspect-4/3 w-full bg-[#FAF5ED] overflow-hidden flex items-center justify-center p-2">
                {item.image && (
                  <img
                    src={item.image}
                    alt={`${item.koreanName} - 용궁리 김치마을(세부)`}
                    className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500 ease-out"
                    loading="lazy"
                    referrerPolicy="no-referrer"
                  />
                )}
              </div>

              {/* Card Text Content */}
              <div className="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                <div>
                  <h3 className="text-base sm:text-lg font-bold text-[#2C1E18] group-hover:text-[#8B2E1E] transition-colors leading-snug">
                    {item.koreanName}
                  </h3>
                  <p className="text-xs text-[#8C7A6F] mt-0.5 font-medium">
                    {item.name}
                  </p>
                  <p className="text-xs text-[#5E4D43] mt-2 line-clamp-2 leading-relaxed">
                    {item.description}
                  </p>
                </div>

                {/* Price Display */}
                <div className="mt-4 pt-3 border-t border-[#F0EBE3] flex items-baseline justify-between">
                  <span className="text-xs text-[#8C7A6F]">가격</span>
                  <div className="text-right">
                    <span className="text-lg sm:text-xl font-black text-[#E02828] tabular-nums">
                      ₱{item.pricePhp.toLocaleString()}
                    </span>
                    <span className="text-[11px] text-[#8C7A6F] block tabular-nums">
                      (약 {item.approxKrw.toLocaleString()}원)
                    </span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* View All Menu CTA */}
        <div className="mt-10 sm:mt-12 text-center">
          <button
            onClick={onScrollToAllMenu}
            className="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-[#2C1E18] hover:bg-[#1E140F] text-white font-bold text-sm sm:text-base transition-transform active:scale-[0.98] shadow-md cursor-pointer group"
          >
            <span>전체 메뉴 안내 보기</span>
            <ChevronDown className="w-4 h-4 text-amber-300 group-hover:translate-y-0.5 transition-transform" />
          </button>
        </div>
      </div>
    </section>
  );
};
