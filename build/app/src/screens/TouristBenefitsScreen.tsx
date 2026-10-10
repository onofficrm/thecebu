import React, { useState } from 'react';
import { CalendarDays, Ticket } from 'lucide-react';
import { CouponItem, EventItem, ServiceId } from '../types';
import { MOCK_COUPONS_LIST, MOCK_EVENTS_LIST } from '../data/mockData';
import { ContentCard } from '../components/common/ContentCard';
import { CouponCard } from '../components/cards/CouponCard';
import { EmptyState } from '../components/common/EmptyState';

interface TouristBenefitsScreenProps {
  onNavigateService: (serviceId: ServiceId) => void;
  onSelectCoupon: (item: CouponItem) => void;
  onSelectEvent: (item: EventItem) => void;
}

type BenefitTab = 'coupons' | 'events';

export const TouristBenefitsScreen: React.FC<TouristBenefitsScreenProps> = ({
  onNavigateService,
  onSelectCoupon,
  onSelectEvent,
}) => {
  const [activeTab, setActiveTab] = useState<BenefitTab>('coupons');

  return (
    <div className="space-y-4 pb-24">
      <section className="rounded-2xl bg-white p-4 ring-1 ring-[#E1ECF3]">
        <p className="text-[11px] font-bold text-[#079BE8]">TRAVEL BENEFITS</p>
        <h1 className="mt-1 text-lg font-black text-[#183247]">이벤트와 쿠폰을 한곳에서</h1>
        <p className="mt-1 text-xs leading-relaxed text-[#617789]">
          운영 데이터에 등록된 이벤트와 쿠폰만 표시됩니다.
        </p>
      </section>

      <div className="grid grid-cols-2 gap-2 rounded-2xl bg-[#EAF8FF] p-1">
        <button
          type="button"
          onClick={() => setActiveTab('coupons')}
          className={`min-h-[48px] rounded-xl flex items-center justify-center gap-2 text-sm font-bold transition-colors ${
            activeTab === 'coupons'
              ? 'bg-white text-[#075A9D] shadow-xs'
              : 'text-[#617789]'
          }`}
        >
          <Ticket size={18} />
          쿠폰
        </button>
        <button
          type="button"
          onClick={() => setActiveTab('events')}
          className={`min-h-[48px] rounded-xl flex items-center justify-center gap-2 text-sm font-bold transition-colors ${
            activeTab === 'events'
              ? 'bg-white text-[#075A9D] shadow-xs'
              : 'text-[#617789]'
          }`}
        >
          <CalendarDays size={18} />
          이벤트
        </button>
      </div>

      <div className="flex items-center justify-between px-1">
        <div>
          <h2 className="text-base font-bold text-[#183247]">
            {activeTab === 'coupons' ? '쿠폰 혜택' : '이벤트'}
          </h2>
          <p className="mt-0.5 text-xs text-[#617789]">
            {activeTab === 'coupons' ? '조건과 유효기간을 확인하세요' : '장소와 일정을 확인하세요'}
          </p>
        </div>
        <button
          type="button"
          onClick={() => onNavigateService(activeTab)}
          className="min-h-[36px] text-xs font-bold text-[#079BE8]"
        >
          전체보기
        </button>
      </div>

      <div className="space-y-2.5">
        {(activeTab === 'coupons' ? MOCK_COUPONS_LIST : MOCK_EVENTS_LIST).length === 0 ? (
          <EmptyState
            title={activeTab === 'coupons' ? '등록된 쿠폰이 없습니다' : '등록된 이벤트가 없습니다'}
            description="운영 데이터가 등록되면 여기에 표시됩니다."
          />
        ) : activeTab === 'coupons'
          ? MOCK_COUPONS_LIST.map((coupon) => (
              <CouponCard
                key={coupon.id}
                coupon={coupon}
                onClick={() => onSelectCoupon(coupon)}
              />
            ))
          : MOCK_EVENTS_LIST.map((event) => (
              <ContentCard
                key={event.id}
                title={event.title}
                subtitle={event.summary}
                category={event.badge}
                metadata={[{ value: event.location }, { value: event.period }]}
                actionText="상세보기"
                onClick={() => onSelectEvent(event)}
              />
            ))}
      </div>
    </div>
  );
};
