import React from 'react';
import { X, Phone, CalendarCheck } from 'lucide-react';
import { MenuItem } from '../types';
import { RESTAURANT_INFO } from '../data/restaurantData';

interface MenuDetailModalProps {
  item: MenuItem | null;
  onClose: () => void;
  onReserve: () => void;
}

export const MenuDetailModal: React.FC<MenuDetailModalProps> = ({ item, onClose, onReserve }) => {
  if (!item) return null;

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-200"
      onClick={onClose}
    >
      <div
        className="relative w-full max-w-md bg-[#FCFAF7] rounded-2xl shadow-2xl border border-[#EAE3D9] overflow-hidden max-h-[90vh] flex flex-col"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Large Food Photography */}
        <div className="relative aspect-4/3 w-full bg-stone-900 overflow-hidden">
          {item.image ? (
            <img
              src={item.image}
              alt={item.koreanName}
              className="w-full h-full object-cover"
              referrerPolicy="no-referrer"
            />
          ) : (
            <div className="w-full h-full bg-[#FAF4ED] flex flex-col items-center justify-center text-center p-6">
              <span className="text-xs font-semibold text-[#8B2E1E] uppercase tracking-wider mb-1">
                YONGGUNGRI
              </span>
              <p className="text-xl font-bold text-[#2C1E18]">{item.koreanName}</p>
            </div>
          )}

          {/* Close Button */}
          <button
            onClick={onClose}
            className="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/55 hover:bg-black/80 text-white flex items-center justify-center transition-colors cursor-pointer z-10"
            aria-label="닫기"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content Body: Focused on Food name, English name, and Price without long text */}
        <div className="p-6 overflow-y-auto space-y-4">
          <div>
            <div className="flex items-center gap-2 mb-1">
              <span className="text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#FAF4ED] text-[#8B2E1E] border border-[#EAE3D9]">
                {item.portion.split(' ')[0] || '1인분'}
              </span>
              <span className="text-xs text-[#8C7A6F] font-medium tracking-tight">
                {item.name}
              </span>
            </div>
            <h3 className="text-2xl font-extrabold text-[#2C1E18] tracking-tight">
              {item.koreanName}
            </h3>
          </div>

          {/* Highlighted Price Container */}
          <div className="p-4 rounded-xl bg-[#FAF5ED] border border-[#EAE3D9] flex items-baseline justify-between">
            <span className="text-xs font-semibold text-[#7A695F]">가격</span>
            <div className="text-right">
              <span className="text-2xl font-extrabold text-[#8B2E1E] tabular-nums">
                ₱{item.pricePhp.toLocaleString()}
              </span>
              <span className="text-xs text-[#8C7A6F] ml-1.5 tabular-nums">
                (약 {item.approxKrw.toLocaleString()}원)
              </span>
            </div>
          </div>

          {/* Short description if available */}
          {item.description && (
            <p className="text-xs sm:text-sm text-[#5E4D43] leading-relaxed">
              {item.description}
            </p>
          )}

          {/* Quick CTAs inside modal */}
          <div className="pt-2 grid grid-cols-2 gap-2.5">
            <a
              href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
              className="py-3 px-3 rounded-xl bg-white border border-[#EAE3D9] hover:bg-[#FAF5ED] text-[#2C1E18] text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
            >
              <Phone className="w-3.5 h-3.5 text-[#8B2E1E]" />
              <span>전화 문의</span>
            </a>
            <button
              onClick={onReserve}
              className="py-3 px-3 rounded-xl bg-[#2C1E18] hover:bg-[#1E140F] text-white text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer"
            >
              <CalendarCheck className="w-3.5 h-3.5 text-amber-300" />
              <span>좌석 예약하기</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
