import React from 'react';
import { ChevronRight, Ticket } from 'lucide-react';
import { CouponItem } from '../../types';
import { formatNumericText } from '../../utils/format';

interface CouponCardProps {
  coupon: CouponItem;
  onClick: () => void;
}

export const CouponCard: React.FC<CouponCardProps> = ({ coupon, onClick }) => {
  const showDiscount =
    coupon.discount &&
    coupon.discount.trim().toLocaleLowerCase() !== coupon.title.trim().toLocaleLowerCase();

  return (
    <button
      type="button"
      onClick={onClick}
      className="group relative w-full overflow-hidden rounded-2xl border border-[#D8E8F2] bg-white p-4 pl-5 text-left shadow-2xs transition-all hover:border-[#079BE8] active:scale-[0.99]"
    >
      <span className="absolute inset-y-0 left-0 w-1.5 bg-[#079BE8]" aria-hidden="true" />

      <div className="flex min-w-0 items-center justify-between gap-3">
        <div className="flex min-w-0 items-center gap-1.5">
          <Ticket size={14} className="shrink-0 text-[#079BE8]" />
          <span className="truncate text-[11px] font-extrabold text-[#079BE8]">
            {coupon.storeName}
          </span>
        </div>
        <span className="shrink-0 rounded-full bg-[#F1F9FE] px-2 py-1 text-[10px] font-semibold text-[#617789]">
          {coupon.validUntil === '상시' ? '상시 사용' : `~ ${coupon.validUntil}`}
        </span>
      </div>

      <h3 className="mt-2 text-sm font-black leading-5 text-[#183247]">
        {formatNumericText(coupon.title)}
      </h3>
      {coupon.conditions && (
        <p className="mt-1 line-clamp-2 text-[11px] leading-[1.55] text-[#617789]">
          {formatNumericText(coupon.conditions)}
        </p>
      )}

      <div className="mt-3 flex min-h-8 items-center justify-between gap-2 border-t border-[#ECF2F6] pt-2.5">
        <span className="min-w-0 truncate text-[11px] font-extrabold text-[#0879E7]">
          {showDiscount ? formatNumericText(coupon.discount) : '쿠폰 상세 조건 확인'}
        </span>
        <span className="flex shrink-0 items-center gap-0.5 text-[11px] font-bold text-[#075A9D]">
          자세히 보기
          <ChevronRight size={14} className="transition-transform group-hover:translate-x-0.5" />
        </span>
      </div>
    </button>
  );
};
