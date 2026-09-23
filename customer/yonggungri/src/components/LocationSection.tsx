import React, { useState } from 'react';
import {
  MapPin,
  Phone,
  Clock,
  Car,
  Package,
  Bike,
  Users2,
  Navigation,
  Copy,
  Check,
  CalendarCheck,
  Building,
} from 'lucide-react';
import { RESTAURANT_INFO, CEBU_CITY_DISTANCES } from '../data/restaurantData';

interface LocationSectionProps {
  onOpenReservation: () => void;
  onOpenGroupReservation?: () => void;
}

export const LocationSection: React.FC<LocationSectionProps> = ({
  onOpenReservation,
  onOpenGroupReservation,
}) => {
  const [copiedGrab, setCopiedGrab] = useState(false);
  const [copiedAddress, setCopiedAddress] = useState(false);

  const handleCopyGrab = () => {
    navigator.clipboard.writeText(RESTAURANT_INFO.grabSearchKeyword);
    setCopiedGrab(true);
    setTimeout(() => setCopiedGrab(false), 2000);
  };

  const handleCopyAddress = () => {
    navigator.clipboard.writeText(`${RESTAURANT_INFO.address} (${RESTAURANT_INFO.addressDetail})`);
    setCopiedAddress(true);
    setTimeout(() => setCopiedAddress(false), 2000);
  };

  // 실제 매장 시설 & 서비스
  const serviceBadges = [
    { label: '전용 주차 완비', desc: '매장 앞 편리한 주차 공간', icon: Car },
    { label: '전 메뉴 포장 가능', desc: '깔끔한 테이크아웃 포장', icon: Package },
    { label: '세부시티 인근 배달', desc: '호텔·콘도 배달 문의 가능', icon: Bike },
    { label: '단체석 & 룸 완비', desc: '골프·가족·회식 모임 환영', icon: Users2 },
  ];

  return (
    <section id="location" className="py-16 sm:py-24 bg-[#F8F5EE] border-t border-[#EAE3D9]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* SECTION — LOCATION HEADER */}
        <div className="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
          <p className="text-xs sm:text-sm font-bold tracking-widest text-[#8B2E1E] uppercase mb-2">
            LOCATION
          </p>
          <h2 className="text-2xl sm:text-4xl font-extrabold text-[#2C1E18] tracking-tight">
            용궁리 오시는 길 (마리아 루이사 로드 초입)
          </h2>
          <p className="mt-2 text-sm sm:text-base text-[#5E4D43]">
            세부시티 바닐라드 마리아 루이사 로드(Maria Luisa Rd) 초입에 위치해 찾아오시기 매우 편리합니다.
          </p>
        </div>

        {/* 2-Column Main Layout: Google Maps Interactive Card + Verified Restaurant Info */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start mb-16">
          {/* Left Column: Interactive Map & Prominent Direction CTA */}
          <div className="lg:col-span-7 bg-white rounded-2xl border border-[#EAE3D9] overflow-hidden shadow-xs flex flex-col justify-between">
            {/* Real Google Maps Embed with Maria Luisa Road Banilad Location */}
            <div className="relative aspect-16/10 bg-[#E5DFD5] overflow-hidden">
              <iframe
                title="용궁리 김치마을 세부시티 마리아 루이사 로드 구글 지도"
                src={RESTAURANT_INFO.googleMapsEmbedUrl}
                className="w-full h-full border-0"
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
              />

              {/* Floating Real Address Pill Overlay */}
              <div className="absolute top-3 left-3 right-3 sm:right-auto bg-white/95 backdrop-blur-xs p-3 rounded-xl border border-[#EAE3D9] shadow-md flex items-center gap-2.5">
                <MapPin className="w-5 h-5 text-[#8B2E1E] shrink-0" />
                <div>
                  <p className="text-xs font-bold text-[#2C1E18]">{RESTAURANT_INFO.brandNameKo}</p>
                  <p className="text-[11px] text-[#7A695F]">Maria Luisa Road, Banilad, Cebu City</p>
                </div>
              </div>
            </div>

            {/* Prominent Google Maps Direction Button & Grab Copy */}
            <div className="p-5 sm:p-6 bg-white border-t border-[#F0EBE3] space-y-4">
              <div className="flex flex-col sm:flex-row gap-3">
                {/* [Google Maps 길찾기] - 크고 명확한 메인 CTA */}
                <a
                  href={RESTAURANT_INFO.googleMapsUrl}
                  target="_blank"
                  rel="noreferrer"
                  className="flex-1 min-h-[52px] px-6 py-3.5 rounded-xl bg-[#2C1E18] hover:bg-[#1E140F] text-white text-base font-bold flex items-center justify-center gap-2.5 shadow-md hover:shadow-lg transition-transform active:scale-[0.98] cursor-pointer"
                >
                  <Navigation className="w-5 h-5 text-amber-300" />
                  <span>Google Maps 길찾기</span>
                </a>

                {/* Grab Search Keyword Copy Button */}
                <button
                  onClick={handleCopyGrab}
                  className="min-h-[52px] px-5 py-3.5 rounded-xl bg-[#FAF5ED] hover:bg-[#F2ECE2] text-[#2C1E18] border border-[#D9CFBF] text-sm font-semibold flex items-center justify-center gap-2 transition-colors cursor-pointer"
                >
                  {copiedGrab ? (
                    <>
                      <Check className="w-4 h-4 text-emerald-600" />
                      <span className="text-emerald-700 font-bold">검색어 복사 완료!</span>
                    </>
                  ) : (
                    <>
                      <Copy className="w-4 h-4 text-[#8B2E1E]" />
                      <span>그랩(Grab) 목적지 복사</span>
                    </>
                  )}
                </button>
              </div>

              {/* Grab Tips Note */}
              <div className="p-3 rounded-lg bg-[#FAF5ED]/80 border border-[#EAE3D9] text-xs text-[#5E4D43] leading-relaxed">
                <span className="font-bold text-[#2C1E18]">🚕 그랩(Grab) 및 택시 이용 안내:</span> 그랩 앱에서{' '}
                <span className="font-bold text-[#8B2E1E]">"{RESTAURANT_INFO.grabSearchKeyword}"</span>를
                입력하시거나, 기사님께 "Banilad Maria Luisa Road entrance 용궁리" 또는 "Streetscape 근처 마리아 루이사 초입"을 말씀해 주세요.
              </div>
            </div>
          </div>

          {/* Right Column: Detail Info (주소, 전화번호, 영업시간, 주차, 포장, 배달, 단체예약) */}
          <div className="lg:col-span-5 space-y-5">
            {/* Core Info Details Card */}
            <div className="p-6 rounded-2xl bg-white border border-[#EAE3D9] shadow-xs space-y-4">
              <h3 className="text-base font-bold text-[#2C1E18] pb-3 border-b border-[#F0EBE3] flex items-center justify-between">
                <span>매장 상세 방문 정보</span>
                <span className="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                  연중무휴 24시간
                </span>
              </h3>

              {/* 주소 */}
              <div className="flex items-start gap-3 text-sm">
                <MapPin className="w-4 h-4 text-[#8B2E1E] mt-0.5 shrink-0" />
                <div className="flex-1">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-bold text-[#7A695F]">주소 (ADDRESS)</span>
                    <button
                      onClick={handleCopyAddress}
                      className="text-[11px] text-[#8B2E1E] hover:underline flex items-center gap-1 cursor-pointer font-medium"
                    >
                      {copiedAddress ? <Check className="w-3 h-3 text-emerald-600" /> : <Copy className="w-3 h-3" />}
                      <span>{copiedAddress ? '복사됨' : '주소복사'}</span>
                    </button>
                  </div>
                  <p className="font-semibold text-[#2C1E18] mt-0.5">{RESTAURANT_INFO.address}</p>
                  <p className="text-xs text-[#7A695F] mt-0.5">{RESTAURANT_INFO.addressDetail}</p>
                </div>
              </div>

              {/* 전화번호 */}
              <div className="flex items-start gap-3 text-sm">
                <Phone className="w-4 h-4 text-[#8B2E1E] mt-0.5 shrink-0" />
                <div className="flex-1">
                  <span className="text-xs font-bold text-[#7A695F] block">전화번호 (PHONE)</span>
                  <a
                    href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
                    className="font-bold text-[#2C1E18] text-base tabular-nums hover:text-[#8B2E1E] transition-colors"
                  >
                    {RESTAURANT_INFO.phoneDisplay}
                  </a>
                </div>
              </div>

              {/* 영업시간 */}
              <div className="flex items-start gap-3 text-sm">
                <Clock className="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" />
                <div>
                  <span className="text-xs font-bold text-[#7A695F] block">영업시간 (HOURS)</span>
                  <p className="font-bold text-[#2C1E18] flex items-center gap-1.5 mt-0.5">
                    <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                    24시간 연중무휴 (OPEN 24 HOURS)
                  </p>
                </div>
              </div>
            </div>

            {/* 제공 서비스 & 편의 시설 */}
            <div className="p-6 rounded-2xl bg-white border border-[#EAE3D9] shadow-xs">
              <h4 className="text-xs font-bold text-[#7A695F] uppercase tracking-wider mb-3">
                제공 서비스 & 편의 시설
              </h4>
              <div className="grid grid-cols-2 gap-2.5">
                {serviceBadges.map((badge, idx) => {
                  const Icon = badge.icon;
                  return (
                    <div
                      key={idx}
                      className="p-3 rounded-xl bg-[#FAF5ED] border border-[#EAE3D9] flex items-center gap-2.5"
                    >
                      <div className="w-7 h-7 rounded-lg bg-white text-[#8B2E1E] flex items-center justify-center shrink-0 shadow-2xs">
                        <Icon className="w-3.5 h-3.5" />
                      </div>
                      <div>
                        <p className="text-xs font-bold text-[#2C1E18] leading-tight">
                          {badge.label}
                        </p>
                        <p className="text-[10px] text-[#7A695F] leading-tight mt-0.5">
                          {badge.desc}
                        </p>
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            {/* 세부시티 마리아 루이사 인근 주요 랜드마크 소요 시간 */}
            <div className="p-5 rounded-2xl bg-white border border-[#EAE3D9] shadow-xs">
              <h4 className="text-xs font-bold text-[#7A695F] uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                <Building className="w-3.5 h-3.5 text-[#8B2E1E]" />
                인근 주요 랜드마크 소요 시간
              </h4>
              <div className="grid grid-cols-2 gap-2 text-xs">
                {CEBU_CITY_DISTANCES.map((item, idx) => (
                  <div
                    key={idx}
                    className="p-2 rounded-lg bg-[#FAF5ED]/60 border border-[#EAE3D9]/60 flex items-center justify-between"
                  >
                    <span className="text-[#4A3B32] font-medium truncate">{item.name.split(' (')[0]}</span>
                    <span className="font-bold text-[#2C1E18] shrink-0 ml-1">{item.time}</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>

        {/* SECTION — CONTACT & GROUP RESERVATION */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
          {/* Contact Direct Card */}
          <div className="p-7 sm:p-8 rounded-2xl bg-white border border-[#EAE3D9] shadow-xs flex flex-col justify-between">
            <div>
              <p className="text-xs font-bold text-[#8B2E1E] tracking-wider uppercase mb-1">
                INSTANT CALL
              </p>
              <h3 className="text-xl sm:text-2xl font-extrabold text-[#2C1E18] tracking-tight">
                방문 전 편하게 문의하세요
              </h3>
              <p className="text-xs sm:text-sm text-[#5E4D43] mt-2 leading-relaxed">
                현재 좌석 현황 확인, 포장/배달 주문, 단체 예약 등 언제든 친절하게 안내해 드립니다.
              </p>

              {/* Large Phone Number Display */}
              <div className="my-6 p-4 rounded-xl bg-[#FAF5ED] border border-[#EAE3D9] text-center">
                <span className="text-xs text-[#7A695F] font-semibold block mb-0.5">매장 직통 전화</span>
                <a
                  href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
                  className="text-2xl sm:text-3xl font-extrabold text-[#8B2E1E] tracking-tight hover:underline tabular-nums"
                >
                  {RESTAURANT_INFO.phoneDisplay}
                </a>
                <span className="block text-[11px] text-[#7A695F] mt-1">24시간 언제든 통화 연결 가능</span>
              </div>
            </div>

            <div className="flex gap-3">
              <a
                href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
                className="flex-1 py-3 px-4 rounded-xl bg-[#8B2E1E] hover:bg-[#722518] text-white text-sm font-bold flex items-center justify-center gap-2 cursor-pointer shadow-xs transition-colors"
              >
                <Phone className="w-4 h-4" />
                <span>바로 전화걸기</span>
              </a>
              <button
                onClick={onOpenReservation}
                className="flex-1 py-3 px-4 rounded-xl bg-[#2C1E18] hover:bg-[#1E140F] text-white text-sm font-bold flex items-center justify-center gap-2 cursor-pointer shadow-xs transition-colors"
              >
                <CalendarCheck className="w-4 h-4 text-amber-300" />
                <span>온라인 예약</span>
              </button>
            </div>
          </div>

          {/* Group & Room Inquiries Card */}
          <div className="p-7 sm:p-8 rounded-2xl bg-white border border-[#EAE3D9] shadow-xs flex flex-col justify-between">
            <div>
              <p className="text-xs font-bold text-[#8B2E1E] tracking-wider uppercase mb-1">
                GROUP & ROOM RESERVATION
              </p>
              <h3 className="text-xl sm:text-2xl font-extrabold text-[#2C1E18] tracking-tight">
                단체 식사 & 프라이빗 룸
              </h3>
              <p className="text-xs sm:text-sm text-[#5E4D43] mt-2 leading-relaxed">
                가족 모임, 골프 투어팀, 동호회 및 회식 등 단체 인원도 편안하고 쾌적하게 식사하실 수 있도록 단체석과 프라이빗 룸이 준비되어 있습니다.
              </p>

              <div className="my-6 space-y-2 text-xs text-[#5E4D43]">
                <div className="flex items-center gap-2">
                  <div className="w-1.5 h-1.5 rounded-full bg-[#8B2E1E]" />
                  <span>소규모 가족 모임부터 대형 단체 회식까지 수용 가능</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="w-1.5 h-1.5 rounded-full bg-[#8B2E1E]" />
                  <span>사전 예약 시 맞춤형 테이블 세팅 및 코스 구성 지원</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="w-1.5 h-1.5 rounded-full bg-[#8B2E1E]" />
                  <span>전용 주차 공간 완비로 차량 이동 시에도 안심</span>
                </div>
              </div>
            </div>

            <button
              onClick={onOpenGroupReservation || onOpenReservation}
              className="w-full py-3.5 px-4 rounded-xl bg-[#FAF5ED] hover:bg-[#F2ECE2] text-[#2C1E18] border border-[#D9CFBF] text-sm font-bold flex items-center justify-center gap-2 cursor-pointer transition-colors shadow-2xs"
            >
              <Users2 className="w-4 h-4 text-[#8B2E1E]" />
              <span>단체 예약 및 룸 배정 문의하기</span>
            </button>
          </div>
        </div>
      </div>
    </section>
  );
};
