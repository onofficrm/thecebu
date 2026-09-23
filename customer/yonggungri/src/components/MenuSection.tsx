import React, { useState, useMemo } from 'react';
import { Search, Phone, CalendarCheck, X } from 'lucide-react';
import { OUR_PHOTO_MENU_ITEMS, MENU_ITEMS, MENU_CATEGORIES, RESTAURANT_INFO } from '../data/restaurantData';
import { MenuItem, MenuCategory } from '../types';

interface MenuSectionProps {
  onOpenReservation: () => void;
  onSelectMenuItem: (item: MenuItem) => void;
}

export const MenuSection: React.FC<MenuSectionProps> = ({
  onOpenReservation,
  onSelectMenuItem,
}) => {
  const [activeCategory, setActiveCategory] = useState<MenuCategory>('all');
  const [searchQuery, setSearchQuery] = useState('');

  // Filtered menu items based on category and search query
  const filteredItems = useMemo(() => {
    let items = MENU_ITEMS;

    // Filter by category
    if (activeCategory === 'signature') {
      items = items.filter((item) => item.isSignature);
    } else if (activeCategory !== 'all') {
      items = items.filter((item) => item.category === activeCategory);
    }

    // Filter by search query
    if (searchQuery.trim()) {
      const q = searchQuery.trim().toLowerCase();
      items = items.filter(
        (item) =>
          item.koreanName.toLowerCase().includes(q) ||
          item.name.toLowerCase().includes(q) ||
          item.tags.some((tag) => tag.toLowerCase().includes(q)) ||
          item.description.toLowerCase().includes(q)
      );
    }

    return items;
  }, [activeCategory, searchQuery]);

  return (
    <section id="all-menu" className="py-16 sm:py-24 bg-[#FCFAF7] border-t border-b border-[#EAE3D9]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* SECTION TITLE & SUBTITLE: 메뉴 안내 / Our Menu */}
        <div className="text-center max-w-3xl mx-auto mb-8 sm:mb-12">
          <h2 className="text-3xl sm:text-4xl font-extrabold text-[#2C1E18] tracking-tight">
            메뉴 안내
          </h2>
          <p className="mt-1.5 text-base sm:text-lg text-[#70584B] font-medium tracking-wide">
            Our Menu
          </p>
        </div>

        {/* SEARCH BAR */}
        <div className="max-w-md mx-auto mb-8">
          <div className="relative">
            <Search className="w-4 h-4 text-[#8C7A6F] absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="메뉴명 검색 (예: 김치찌개, 국밥, 닭볶음탕, 통닭...)"
              className="w-full pl-10 pr-9 py-2.5 rounded-xl bg-white border border-[#D9CFBF] text-sm text-[#2C1E18] placeholder-[#9C8A7F] focus:outline-hidden focus:border-[#8B2E1E] focus:ring-1 focus:ring-[#8B2E1E] shadow-2xs transition-all"
            />
            {searchQuery && (
              <button
                onClick={() => setSearchQuery('')}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-[#8C7A6F] hover:text-[#2C1E18] p-0.5 cursor-pointer"
                aria-label="검색어 지우기"
              >
                <X className="w-4 h-4" />
              </button>
            )}
          </div>
        </div>

        {/* CATEGORY FILTER (Desktop: Centered / Mobile: Horizontal Swipe) */}
        <div className="mb-10 sm:mb-12 overflow-x-auto scrollbar-none -mx-4 px-4 sm:mx-0 sm:px-0">
          <div className="flex items-center sm:justify-center gap-1.5 sm:gap-2 min-w-max pb-2">
            {MENU_CATEGORIES.map((cat) => (
              <button
                key={cat.id}
                onClick={() => {
                  setActiveCategory(cat.id);
                  setSearchQuery('');
                }}
                className={`px-3.5 py-2 rounded-xl text-xs sm:text-sm font-bold transition-all cursor-pointer whitespace-nowrap ${
                  activeCategory === cat.id
                    ? 'bg-[#2C1E18] text-white shadow-xs'
                    : 'bg-white text-[#5E4D43] border border-[#EAE3D9] hover:bg-[#FAF5ED]'
                }`}
              >
                {cat.nameKo}
              </button>
            ))}
          </div>
        </div>

        {/* MENU CARD GRID: 정확히 일치하는 사진-메뉴명-영문-페소 붉은색 강조 레이아웃 */}
        {filteredItems.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-2xl border border-[#EAE3D9] p-8 max-w-md mx-auto">
            <p className="text-sm font-bold text-[#2C1E18]">일치하는 메뉴가 없습니다.</p>
            <p className="text-xs text-[#7A695F] mt-1">다른 검색어를 입력하시거나 카테고리를 변경해 보세요.</p>
            <button
              onClick={() => {
                setActiveCategory('all');
                setSearchQuery('');
              }}
              className="mt-4 px-4 py-2 rounded-lg bg-[#2C1E18] text-white text-xs font-bold cursor-pointer"
            >
              전체 메뉴 보기
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5 sm:gap-4 lg:gap-5">
            {filteredItems.map((item) => (
              <div
                key={item.id}
                onClick={() => onSelectMenuItem(item)}
                className="group bg-white rounded-xl border border-[#EAE3D9] overflow-hidden hover:border-[#D0C2B0] hover:shadow-md transition-all duration-200 flex flex-col cursor-pointer"
              >
                {/* 1. 음식 사진 */}
                <div className="relative aspect-4/3 w-full bg-[#FAF5ED] overflow-hidden flex items-center justify-center p-1 sm:p-2">
                  {item.image ? (
                    <img
                      src={item.image}
                      alt={item.koreanName}
                      className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300 ease-out"
                      loading="lazy"
                      referrerPolicy="no-referrer"
                    />
                  ) : (
                    <div className="w-full h-full bg-[#FAF5ED] flex flex-col items-center justify-center p-3 text-center">
                      <span className="text-[10px] text-[#8B2E1E] font-bold tracking-wider uppercase mb-0.5">
                        YONGGUNGRI
                      </span>
                      <span className="text-xs font-bold text-[#4A3B32] line-clamp-1">
                        {item.koreanName}
                      </span>
                    </div>
                  )}
                </div>

                {/* 2. 메뉴명 + 영문명 + 3. 붉은색 페소 가격 (제공된 시안 그대로 구성) */}
                <div className="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                  <div>
                    {/* 한글 메뉴명 */}
                    <h3 className="text-sm sm:text-base font-bold text-[#2C1E18] group-hover:text-[#8B2E1E] transition-colors leading-snug line-clamp-1">
                      {item.koreanName}
                    </h3>
                    {/* 영문 메뉴명 */}
                    <p className="text-[11px] sm:text-xs text-[#8C7A6F] font-normal line-clamp-1 mt-0.5">
                      {item.name}
                    </p>
                  </div>

                  {/* 가격: 굵은 붉은색 페소 ₱ (시안 이미지와 100% 동일한 비주얼) */}
                  <div className="mt-3 pt-2 border-t border-[#F5EFE6]">
                    <span className="text-base sm:text-lg font-black text-[#E02828] tabular-nums tracking-tight">
                      ₱{item.pricePhp.toLocaleString()}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* BOTTOM ACTION CTA */}
        <div className="mt-14 sm:mt-16 pt-8 border-t border-[#EAE3D9] flex flex-col sm:flex-row items-center justify-center gap-3.5 max-w-xl mx-auto">
          <a
            href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
            className="w-full sm:w-1/2 min-h-[50px] px-6 py-3.5 rounded-xl bg-[#8B2E1E] hover:bg-[#722518] text-white text-sm font-bold flex items-center justify-center gap-2 shadow-xs transition-transform active:scale-[0.98] cursor-pointer"
          >
            <Phone className="w-4 h-4 text-white" />
            <span>전화 예약하기</span>
          </a>

          <button
            onClick={onOpenReservation}
            className="w-full sm:w-1/2 min-h-[50px] px-6 py-3.5 rounded-xl bg-[#2C1E18] hover:bg-[#1E140F] text-white text-sm font-bold flex items-center justify-center gap-2 shadow-md hover:shadow-lg transition-transform active:scale-[0.98] cursor-pointer"
          >
            <CalendarCheck className="w-4 h-4 text-amber-300" />
            <span>온라인 예약 신청</span>
          </button>
        </div>
      </div>
    </section>
  );
};
