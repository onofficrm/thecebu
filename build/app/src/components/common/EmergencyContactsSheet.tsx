import React from 'react';
import { BottomSheet } from './BottomSheet';
import { PhoneCall } from 'lucide-react';

interface EmergencyContactsSheetProps {
  isOpen: boolean;
  onClose: () => void;
}

export const EmergencyContactsSheet: React.FC<EmergencyContactsSheetProps> = ({
  isOpen,
  onClose,
}) => {
  const contacts = [
    {
      category: '필리핀 통합 긴급 신고',
      desc: '경찰·소방·구급 긴급 상황',
      tel: '911',
      dialTel: '911',
      icon: PhoneCall,
      color: '#075A9D',
    },
  ];

  return (
    <BottomSheet isOpen={isOpen} onClose={onClose} title="세부 주요 긴급 비상연락망">
      <div className="space-y-3 pb-2">
        <p className="text-xs text-[#617789] leading-relaxed">
          현재 확인된 공식 통합 긴급번호만 표시합니다. 기관별 연락처는 운영 데이터 등록 후 제공됩니다.
        </p>

        <div className="space-y-2.5">
          {contacts.map((item, idx) => {
            const Icon = item.icon;
            return (
              <div
                key={idx}
                className="bg-[#F7FBFE] border border-[#E1ECF3] rounded-2xl p-3.5 flex items-start justify-between gap-3"
              >
                <div className="flex items-start gap-3 min-w-0">
                  <div
                    className="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                    style={{ backgroundColor: `${item.color}15`, color: item.color }}
                  >
                    <Icon size={18} />
                  </div>
                  <div className="min-w-0">
                    <h4 className="text-xs font-bold text-[#183247]">
                      {item.category}
                    </h4>
                    <p className="text-[11px] text-[#617789] mt-0.5 line-clamp-2">
                      {item.desc}
                    </p>
                    <div className="mt-1 font-mono text-xs font-extrabold text-[#075A9D]">
                      {item.tel}
                    </div>
                  </div>
                </div>

                <a
                  href={`tel:${item.dialTel.replace(/[^0-9+]/g, '')}`}
                  aria-label={`${item.category}에 전화하기`}
                  className="min-h-[40px] px-3 py-1.5 bg-[#075A9D] text-white text-xs font-bold rounded-xl flex items-center gap-1 hover:bg-[#0879E7] transition-colors shrink-0"
                >
                  <PhoneCall size={13} />
                  <span>통화</span>
                </a>
              </div>
            );
          })}
        </div>
      </div>
    </BottomSheet>
  );
};
