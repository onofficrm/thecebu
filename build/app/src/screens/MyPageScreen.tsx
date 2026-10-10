import React, { useState } from 'react';
import { AppMode } from '../types';
import { OperationalAuth } from '../services/operationalData';
import { ModeSwitcher } from '../components/common/ModeSwitcher';
import {
  PreferredTranslationLanguage,
  translationStorage,
} from '../utils/storage';
import {
  Ticket,
  SlidersHorizontal,
  Bell,
  PhoneCall,
  ChevronRight,
  User,
  Bookmark,
  Heart,
  Building2,
  Settings,
  Languages,
} from 'lucide-react';

interface MyPageScreenProps {
  currentMode: AppMode;
  onModeChange: (mode: AppMode) => void;
  onOpenCustomize: () => void;
  onOpenCoupons: () => void;
  onOpenEmergency: () => void;
  downloadedCouponCount: number;
  usedCouponCount?: number;
  savedItems?: Array<{ id: string; type: string; title: string }>;
  onOpenSaved?: (item: { id: string; type: string; title: string }) => void;
  onOpenLogin?: () => void;
  operationalAuth?: OperationalAuth;
}

export const MyPageScreen: React.FC<MyPageScreenProps> = ({
  currentMode,
  onModeChange,
  onOpenCustomize,
  onOpenCoupons,
  onOpenEmergency,
  downloadedCouponCount,
  usedCouponCount = 0,
  savedItems = [],
  onOpenSaved,
  onOpenLogin,
  operationalAuth = { is_member: false },
}) => {
  const [savedFilter, setSavedFilter] = useState<'all' | 'content' | 'market' | 'business'>('all');
  const [notificationsEnabled, setNotificationsEnabled] = useState(true);
  const [compactCards, setCompactCards] = useState(false);
  const [translationLanguage, setTranslationLanguage] =
    useState<PreferredTranslationLanguage>(translationStorage.getLanguage());
  const filteredSaved = savedItems.filter((item) => {
    if (savedFilter === 'all') return true;
    if (savedFilter === 'content') return ['news', 'life_info', 'events'].includes(item.type);
    if (savedFilter === 'market') return ['marketplace', 'real_estate'].includes(item.type);
    return ['restaurants', 'businesses'].includes(item.type);
  });

  return (
    <div className="space-y-5 pb-24">
      {/* Profile Header Card */}
      <div className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs">
        <div className="flex items-center gap-3.5 mb-4">
          <div className="w-13 h-13 rounded-2xl bg-[#075A9D] text-white flex items-center justify-center font-bold text-lg shadow-xs">
            <User size={24} />
          </div>
          <div className="min-w-0 flex-1">
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <h2 className="text-base font-bold text-[#183247] truncate">
                  {operationalAuth.is_member ? operationalAuth.nick || '회원' : '게스트'}
                </h2>
                <span className="text-[10px] font-bold text-white bg-[#079BE8] px-1.5 py-0.5 rounded">
                  {operationalAuth.is_member ? operationalAuth.member_type || '회원' : '게스트'}
                </span>
              </div>
              {operationalAuth.is_member ? (
                <span className="rounded-lg bg-[#EAF8FF] px-2 py-1 text-[11px] font-bold text-[#079BE8]">
                  앱 MY
                </span>
              ) : onOpenLogin ? (
                <button
                  type="button"
                  onClick={onOpenLogin}
                  className="text-xs font-bold text-[#079BE8] hover:underline"
                >
                  로그인
                </button>
              ) : null}
            </div>
            <p className="text-xs text-[#617789] truncate mt-0.5">
              {operationalAuth.is_member
                ? `${operationalAuth.member_type || '회원'} · ${(
                    operationalAuth.point || 0
                  ).toLocaleString()}P`
                : '로그인하지 않은 상태입니다.'}
            </p>
          </div>
        </div>

        {/* 3 Activity Metrics Strip */}
        <div className="grid grid-cols-3 gap-2 pt-3 border-t border-[#ECF2F6] text-center font-mono tabular-nums">
          <div className="py-1">
            <span className="text-xs text-[#8799A8] block font-sans">스크랩/찜</span>
            <span className="text-base font-extrabold text-[#183247]">
              {savedItems.length}
            </span>
          </div>
          <button
            type="button"
            onClick={onOpenCoupons}
            aria-label={`보유 쿠폰 ${downloadedCouponCount}장 열기`}
            className="py-1 border-x border-[#ECF2F6] cursor-pointer hover:bg-[#F7FBFE] rounded-lg transition-colors"
          >
            <span className="text-xs text-[#079BE8] block font-sans font-bold">보유 쿠폰</span>
            <span className="text-base font-extrabold text-[#079BE8]">
              {operationalAuth.is_member
                ? operationalAuth.coupon_count || 0
                : downloadedCouponCount}
            </span>
          </button>
          <div className="py-1">
            <span className="text-xs text-[#8799A8] block font-sans">작성 글</span>
            <span className="text-base font-extrabold text-[#183247]">
              0
            </span>
          </div>
        </div>
      </div>

      {/* Mode Switcher in MY Page */}
      <div className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs">
        <div className="flex items-center justify-between mb-2.5">
          <span className="text-xs font-bold text-[#183247]">홈 화면 모드 변경</span>
          <span className="text-[11px] text-[#8799A8]">언제든 자유롭게 전환</span>
        </div>
        <ModeSwitcher currentMode={currentMode} onModeChange={onModeChange} />
      </div>

      {/* Navigation Menus */}
      <div className="bg-white border border-[#E1ECF3] rounded-2xl p-2 shadow-2xs divide-y divide-[#ECF2F6]">
        <button
          type="button"
          onClick={onOpenCustomize}
          className="w-full flex items-center justify-between p-3 hover:bg-[#F7FBFE] rounded-xl transition-colors text-left text-xs font-semibold text-[#183247] min-h-[48px]"
        >
          <div className="flex items-center gap-3">
            <SlidersHorizontal size={17} className="text-[#079BE8]" />
            <span>홈 화면 꾸미기 (나만의 바로가기 설정)</span>
          </div>
          <ChevronRight size={16} className="text-[#8799A8]" />
        </button>

        {([
          {
            key: 'content',
            label: '저장한 게시글',
            icon: Bookmark,
            count: savedItems.filter((item) => ['news', 'life_info', 'events'].includes(item.type)).length,
          },
          {
            key: 'market',
            label: '관심 상품·매물',
            icon: Heart,
            count: savedItems.filter((item) => ['marketplace', 'real_estate'].includes(item.type)).length,
          },
          {
            key: 'business',
            label: '관심 업체·맛집',
            icon: Building2,
            count: savedItems.filter((item) => ['restaurants', 'businesses'].includes(item.type)).length,
          },
        ] as const).map(({ key, label, icon: Icon, count }) => (
          <button
            key={key}
            type="button"
            onClick={() => setSavedFilter(key)}
            className="w-full flex items-center justify-between p-3 hover:bg-[#F7FBFE] rounded-xl transition-colors text-left text-xs font-semibold text-[#183247] min-h-[48px]"
          >
            <div className="flex items-center gap-3">
              <Icon size={17} className="text-[#079BE8]" />
              <span>{label}</span>
            </div>
            <div className="flex items-center gap-1.5">
              <span className="font-mono text-xs font-bold text-[#079BE8]">{count}</span>
              <ChevronRight size={16} className="text-[#8799A8]" />
            </div>
          </button>
        ))}

        <button
          type="button"
          onClick={onOpenCoupons}
          className="w-full flex items-center justify-between p-3 hover:bg-[#F7FBFE] rounded-xl transition-colors text-left text-xs font-semibold text-[#183247] min-h-[48px]"
        >
          <div className="flex items-center gap-3">
            <Ticket size={17} className="text-[#079BE8]" />
            <span>다운로드한 쿠폰 보관함</span>
          </div>
          <div className="flex items-center gap-1.5">
            <span className="text-xs font-bold text-[#079BE8] font-mono">
              {downloadedCouponCount}장 · 사용 {usedCouponCount}장
            </span>
            <ChevronRight size={16} className="text-[#8799A8]" />
          </div>
        </button>

        <button
          type="button"
          onClick={onOpenEmergency}
          className="w-full flex items-center justify-between p-3 hover:bg-[#F7FBFE] rounded-xl transition-colors text-left text-xs font-semibold text-[#183247] min-h-[48px]"
        >
          <div className="flex items-center gap-3">
            <PhoneCall size={17} className="text-[#075A9D]" />
            <span>세부 비상연락처 (영사관 / 응급의료 / 한인회)</span>
          </div>
          <ChevronRight size={16} className="text-[#8799A8]" />
        </button>

        <button
          type="button"
          onClick={() => setNotificationsEnabled((current) => !current)}
          className="w-full flex items-center justify-between p-3 hover:bg-[#F7FBFE] rounded-xl text-left text-xs font-semibold text-[#183247] min-h-[48px]"
          aria-pressed={notificationsEnabled}
        >
          <div className="flex items-center gap-3">
            <Bell size={17} className="text-[#0879E7]" />
            <span>키워드 알림 & 푸시 설정</span>
          </div>
          <span className="text-[11px] text-[#079BE8] font-bold">
            {notificationsEnabled ? '설정됨 (ON)' : '꺼짐 (OFF)'}
          </span>
        </button>

        <button
          type="button"
          onClick={() => setCompactCards((current) => !current)}
          className="w-full flex items-center justify-between p-3 hover:bg-[#F7FBFE] rounded-xl transition-colors text-left text-xs font-semibold text-[#183247] min-h-[48px]"
        >
          <div className="flex items-center gap-3">
            <Settings size={17} className="text-[#617789]" />
            <span>환경설정 · 카드 간격</span>
          </div>
          <span className="text-[11px] font-bold text-[#079BE8]">
            {compactCards ? '좁게' : '기본'}
          </span>
        </button>

        <div className="flex min-h-[56px] w-full items-center justify-between gap-3 rounded-xl p-3 text-left text-xs font-semibold text-[#183247]">
          <div className="flex min-w-0 items-center gap-3">
            <Languages size={17} className="shrink-0 text-[#079BE8]" />
            <div className="min-w-0">
              <span className="block">기본 번역 언어</span>
              <span className="block text-[10px] font-normal text-[#8799A8]">
                게시글 상세에서 마지막 선택을 기억합니다.
              </span>
            </div>
          </div>
          <select
            value={translationLanguage}
            onChange={(event) => {
              const language = event.target.value as PreferredTranslationLanguage;
              setTranslationLanguage(language);
              translationStorage.setLanguage(language);
            }}
            aria-label="기본 번역 언어"
            className="h-9 shrink-0 rounded-lg border border-[#B9E5FC] bg-[#F1F9FE] px-2 text-[11px] font-bold text-[#075A9D] outline-none"
          >
            <option value="en">English</option>
            <option value="ja">日本語</option>
            <option value="zh">中文</option>
          </select>
        </div>
      </div>

      <section className="rounded-2xl border border-[#E1ECF3] bg-white p-4">
        <div className="mb-3 flex items-center justify-between">
          <h3 className="text-sm font-bold text-[#183247]">MY 저장 목록</h3>
          <button
            type="button"
            onClick={() => setSavedFilter('all')}
            className="text-[11px] font-bold text-[#079BE8]"
          >
            전체 보기
          </button>
        </div>
        {filteredSaved.length === 0 ? (
          <div className="rounded-xl bg-[#F7FBFE] px-3 py-5 text-center">
            <p className="text-xs font-bold text-[#183247]">저장한 항목이 없습니다</p>
            <p className="mt-1 text-[11px] text-[#8799A8]">상세 화면의 하트 버튼으로 저장해보세요.</p>
          </div>
        ) : (
          <div className="divide-y divide-[#ECF2F6]">
            {filteredSaved.map((item) => (
              <button
                key={`${item.type}-${item.id}`}
                type="button"
                onClick={() => onOpenSaved?.(item)}
                className={`flex w-full items-center justify-between gap-2 text-left ${
                  compactCards ? 'min-h-[42px] py-1.5' : 'min-h-[48px] py-2'
                }`}
              >
                <div className="min-w-0">
                  <span className="text-[10px] font-bold text-[#079BE8]">{item.type}</span>
                  <p className="truncate text-xs font-semibold text-[#183247]">{item.title}</p>
                </div>
                <ChevronRight size={15} className="shrink-0 text-[#8799A8]" />
              </button>
            ))}
          </div>
        )}
      </section>

      {/* App Info & Disclaimer */}
      <div className="p-3 text-center text-xs text-[#8799A8] space-y-1">
        <p className="font-semibold text-[#183247]">세부어때 (THE CEBU) Mobile App</p>
        <p className="text-[11px]">
          운영 데이터와 연결된 세부어때 모바일 앱입니다.
        </p>
      </div>
    </div>
  );
};
