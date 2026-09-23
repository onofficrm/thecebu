import React from 'react';
import { Phone, MapPin, Clock } from 'lucide-react';
import { RESTAURANT_INFO } from '../data/restaurantData';

interface FooterProps {
  onOpenReservation: () => void;
  onScrollToMenu: () => void;
  onScrollToLocation: () => void;
}

export const Footer: React.FC<FooterProps> = ({
  onScrollToMenu,
  onScrollToLocation,
}) => {
  return (
    <footer className="bg-[#18110D] text-[#D4C8C1] pt-12 pb-24 md:pb-12 border-t border-[#2C1E18]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-12 gap-8 pb-8 border-b border-[#2C1E18]">
          {/* Brand Info */}
          <div className="md:col-span-6 space-y-3">
            <div>
              <p className="text-sm font-semibold text-amber-300">
                {RESTAURANT_INFO.brandNameKo}
              </p>
              <h3 className="text-2xl font-extrabold text-white tracking-tight">
                {RESTAURANT_INFO.brandNameEn}
              </h3>
            </div>
            <p className="text-xs sm:text-sm text-[#A8988F] max-w-md leading-relaxed">
              세부시티 바닐라드 마리아 루이사 로드 초입에서 만나는 제대로 된 한국의 맛.
              24시간 정갈한 손맛과 따뜻한 정으로 세부 방문객과 교민 여러분을 정성껏 모십니다.
            </p>
            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#241712] border border-[#3A2820] text-xs text-emerald-400">
              <span className="w-2 h-2 rounded-full bg-emerald-400" />
              <span>마리아 루이사 초입 · 연중무휴 24시간 정상 영업</span>
            </div>
          </div>

          {/* Quick Nav */}
          <div className="md:col-span-2 space-y-2 text-xs">
            <h4 className="font-bold text-white uppercase tracking-wider mb-2">
              바로가기
            </h4>
            <ul className="space-y-2">
              <li>
                <a href="#home" className="hover:text-white transition-colors">
                  홈
                </a>
              </li>
              <li>
                <a href="#about" className="hover:text-white transition-colors">
                  소개
                </a>
              </li>
              <li>
                <button
                  onClick={onScrollToMenu}
                  className="hover:text-white transition-colors text-left cursor-pointer"
                >
                  대표 메뉴
                </button>
              </li>
              <li>
                <button
                  onClick={onScrollToLocation}
                  className="hover:text-white transition-colors text-left cursor-pointer"
                >
                  오시는 길
                </button>
              </li>
            </ul>
          </div>

          {/* Contact Details */}
          <div className="md:col-span-4 space-y-2.5 text-xs text-[#A8988F]">
            <h4 className="font-bold text-white uppercase tracking-wider mb-2">
              매장 안내
            </h4>

            <div className="flex items-start gap-2">
              <MapPin className="w-3.5 h-3.5 text-amber-400 mt-0.5 shrink-0" />
              <span>{RESTAURANT_INFO.address} ({RESTAURANT_INFO.addressDetail})</span>
            </div>

            <div className="flex items-start gap-2">
              <Phone className="w-3.5 h-3.5 text-amber-400 mt-0.5 shrink-0" />
              <a
                href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
                className="text-white font-bold hover:text-amber-300 transition-colors"
              >
                {RESTAURANT_INFO.phoneDisplay}
              </a>
            </div>

            <div className="flex items-start gap-2">
              <Clock className="w-3.5 h-3.5 text-emerald-400 mt-0.5 shrink-0" />
              <span className="text-white font-medium">{RESTAURANT_INFO.hours}</span>
            </div>
          </div>
        </div>

        {/* Copyright */}
        <div className="pt-6 text-center text-[11px] text-[#7E6E65]">
          © {new Date().getFullYear()} {RESTAURANT_INFO.fullName}. All rights reserved.
        </div>
      </div>
    </footer>
  );
};
