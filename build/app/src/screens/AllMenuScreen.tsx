import React from 'react';
import { SERVICES_META } from '../data/mockData';
import { ServiceId, AppMode } from '../types';
import { ServiceIcon } from '../components/common/ServiceIcon';
import { ModeSwitcher } from '../components/common/ModeSwitcher';
import { ChevronRight, Smartphone, PhoneCall, FileText } from 'lucide-react';

interface AllMenuScreenProps {
  currentMode: AppMode;
  onModeChange: (mode: AppMode) => void;
  onSelectService: (serviceId: ServiceId) => void;
  onOpenEmergency: () => void;
}

export const AllMenuScreen: React.FC<AllMenuScreenProps> = ({
  currentMode,
  onModeChange,
  onSelectService,
  onOpenEmergency,
}) => {
  const sections = [
    {
      title: '생활 & 정착 (교민 중심)',
      badge: '교민',
      items: SERVICES_META.filter((s) => ['news', 'life_info', 'real_estate'].includes(s.id)),
    },
    {
      title: '커뮤니티 & 교류',
      badge: '공통',
      items: SERVICES_META.filter((s) => ['marketplace', 'jobs', 'chatrooms'].includes(s.id)),
    },
    {
      title: '여행 & 미식 (관광 중심)',
      badge: '관광객',
      items: SERVICES_META.filter((s) => ['restaurants', 'businesses'].includes(s.id)),
    },
    {
      title: '혜택 & 프로모션',
      badge: '할인',
      items: SERVICES_META.filter((s) => ['events', 'coupons'].includes(s.id)),
    },
  ];

  return (
    <div className="space-y-6 pb-24">
      {/* Top Mode Card */}
      <div className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs">
        <div className="flex items-center justify-between mb-3">
          <div>
            <span className="text-xs text-[#617789] font-medium">현재 홈 화면 모드</span>
            <h2 className="text-sm font-bold text-[#183247]">
              {currentMode === 'resident' ? '🌴 교민 모드 활성화 중' : '✈️ 관광객 모드 활성화 중'}
            </h2>
          </div>
          <span className="text-[11px] text-[#079BE8] font-bold">10개 서비스 상시 이용</span>
        </div>
        <ModeSwitcher currentMode={currentMode} onModeChange={onModeChange} />

        {/* Current Mode Recommended Fast Access */}
        <div className="mt-4 pt-3 border-t border-[#ECF2F6]">
          <span className="text-[11px] font-bold text-[#617789] block mb-2">
            ⭐ {currentMode === 'resident' ? '교민 모드 추천 빠른 접근' : '관광객 모드 추천 빠른 접근'}
          </span>
          <div className="grid grid-cols-4 gap-2">
            {(currentMode === 'resident'
              ? SERVICES_META.filter((s) => ['news', 'life_info', 'marketplace', 'chatrooms'].includes(s.id))
              : SERVICES_META.filter((s) => ['restaurants', 'businesses', 'events', 'coupons'].includes(s.id))
            ).map((s) => (
              <button
                key={s.id}
                type="button"
                onClick={() => onSelectService(s.id)}
                className="flex flex-col items-center justify-center p-2 rounded-xl bg-[#F7FBFE] hover:bg-[#EAF8FF] border border-[#E1ECF3] hover:border-[#079BE8] transition-all text-center"
              >
                <div
                  className="w-8 h-8 rounded-lg flex items-center justify-center mb-1"
                  style={{ backgroundColor: `${s.color}15`, color: s.color }}
                >
                  <ServiceIcon iconName={s.icon} size={16} color={s.color} />
                </div>
                <span className="text-[11px] font-bold text-[#183247] truncate max-w-full">
                  {s.name}
                </span>
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* 10 Services Categorized */}
      {sections.map((section, idx) => (
        <section key={idx} className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs">
          <div className="flex items-center justify-between mb-3 pb-2 border-b border-[#ECF2F6]">
            <h3 className="text-sm font-bold text-[#183247]">{section.title}</h3>
            <span className="text-[10px] font-semibold text-[#075A9D] bg-[#EAF8FF] px-2 py-0.5 rounded border border-[#B9E5FC]">
              {section.badge}
            </span>
          </div>

          <div className="divide-y divide-[#ECF2F6]">
            {section.items.map((service) => (
              <button
                key={service.id}
                type="button"
                onClick={() => onSelectService(service.id)}
                className="w-full flex items-center justify-between py-3 hover:bg-[#F7FBFE] px-1 rounded-xl transition-colors text-left min-h-[52px]"
              >
                <div className="flex items-center gap-3 min-w-0">
                  <div
                    className="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                    style={{ backgroundColor: `${service.color}15`, color: service.color }}
                  >
                    <ServiceIcon iconName={service.icon} size={18} color={service.color} />
                  </div>
                  <div className="min-w-0">
                    <div className="flex items-center gap-2">
                      <span className="text-xs font-bold text-[#183247]">
                        {service.name}
                      </span>
                      {service.badge && (
                        <span className="text-[9px] font-bold text-[#075A9D] bg-[#EAF8FF] px-1.5 py-0.2 rounded border border-[#B9E5FC]">
                          {service.badge}
                        </span>
                      )}
                    </div>
                    <p className="text-[11px] text-[#617789] truncate mt-0.5">
                      {service.description}
                    </p>
                  </div>
                </div>
                <ChevronRight size={16} className="text-[#8799A8] shrink-0 ml-2" />
              </button>
            ))}
          </div>
        </section>
      ))}

      {/* Useful Shortcuts & Customer Support */}
      <section className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs">
        <h3 className="text-sm font-bold text-[#183247] mb-3 pb-2 border-b border-[#ECF2F6]">
          고객 지원 & 비상 편의
        </h3>
        <div className="space-y-2">
          <button
            type="button"
            onClick={onOpenEmergency}
            className="w-full flex items-center justify-between p-2.5 rounded-xl hover:bg-[#F7FBFE] transition-colors text-left text-xs font-semibold text-[#183247] min-h-[44px]"
          >
            <div className="flex items-center gap-2.5">
              <PhoneCall size={16} className="text-[#075A9D]" />
              <span>세부 비상연락망 (영사관 / 응급병원 / 경찰)</span>
            </div>
            <ChevronRight size={14} className="text-[#8799A8]" />
          </button>

          <div
            className="w-full flex items-center justify-between p-2.5 rounded-xl hover:bg-[#F7FBFE] transition-colors text-left text-xs font-semibold text-[#183247] min-h-[44px]"
          >
            <div className="flex items-center gap-2.5">
              <Smartphone size={16} className="text-[#079BE8]" />
              <span>모든 서비스는 앱 안에서 열립니다</span>
            </div>
            <span className="text-[10px] font-bold text-[#079BE8]">APP</span>
          </div>

          <div className="flex items-center justify-between p-2.5 rounded-xl text-left text-xs font-medium text-[#8799A8]">
            <div className="flex items-center gap-2.5">
              <FileText size={16} className="text-[#8799A8]" />
              <span>이용약관 및 개인정보처리방침</span>
            </div>
            <span className="text-[10px]">v2.1.0</span>
          </div>
        </div>
      </section>
    </div>
  );
};
