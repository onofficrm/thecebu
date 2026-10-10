import React, { useState, useEffect, useCallback } from 'react';
import {
  AppMode,
  MainTab,
  ServiceId,
  NewsItem,
  MarketplaceItem,
  LifeInfoItem,
  JobItem,
  ChatroomItem,
  RestaurantItem,
  BusinessItem,
  EventItem,
  CouponItem,
  HomeSectionConfig,
} from './types';
import {
  INITIAL_HOME_SECTIONS,
  MOCK_NEWS_LIST,
  MOCK_MARKETPLACE_LIST,
  MOCK_RESTAURANTS_LIST,
  MOCK_BUSINESSES_LIST,
  MOCK_LIFE_INFO_LIST,
  MOCK_JOBS_LIST,
  MOCK_REAL_ESTATE_LIST,
  MOCK_CHATROOMS_LIST,
  MOCK_EVENTS_LIST,
  MOCK_COUPONS_LIST,
} from './data/mockData';
import { modeStorage, shortcutStorage } from './utils/storage';

// Common Components
import { AppHeader } from './components/common/AppHeader';
import { BottomNavigation } from './components/common/BottomNavigation';
import { BrandLogo } from './components/common/BrandLogo';
import { ToastMessage, ToastData } from './components/common/ToastMessage';
import { ConfirmDialog } from './components/common/ConfirmDialog';
import { DetailModal } from './components/common/DetailModal';
import { EmergencyContactsSheet } from './components/common/EmergencyContactsSheet';
import { ModeSwitchSheet } from './components/common/ModeSwitchSheet';
import { LoadingSkeleton } from './components/common/LoadingSkeleton';
import { AuthModal } from './components/auth/AuthModal';
import {
  loadOperationalData,
  OperationalAuth,
  TodayInfo,
} from './services/operationalData';

// Screens
import { OnboardingModeSelectScreen } from './screens/OnboardingModeSelectScreen';
import { ResidentHomeScreen } from './screens/ResidentHomeScreen';
import { TouristHomeScreen } from './screens/TouristHomeScreen';
import { TouristBenefitsScreen } from './screens/TouristBenefitsScreen';
import { AllMenuScreen } from './screens/AllMenuScreen';
import { HomeCustomizeScreen } from './screens/HomeCustomizeScreen';
import { UniversalSearchScreen } from './screens/UniversalSearchScreen';
import { NotificationScreen } from './screens/NotificationScreen';
import { MyPageScreen } from './screens/MyPageScreen';
import { ServiceListView } from './screens/ServiceListView';

// Icons for frame controls
import { Smartphone, RotateCcw, Wifi, Battery, Share2, Layers } from 'lucide-react';

const SHARED_CONTENT: Record<ServiceId, any[]> = {
  news: MOCK_NEWS_LIST,
  marketplace: MOCK_MARKETPLACE_LIST,
  restaurants: MOCK_RESTAURANTS_LIST,
  businesses: MOCK_BUSINESSES_LIST,
  life_info: MOCK_LIFE_INFO_LIST,
  jobs: MOCK_JOBS_LIST,
  real_estate: MOCK_REAL_ESTATE_LIST,
  chatrooms: MOCK_CHATROOMS_LIST,
  events: MOCK_EVENTS_LIST,
  coupons: MOCK_COUPONS_LIST,
};

const readSharedRoute = (): { service: ServiceId; id: string; item: any } | null => {
  try {
    if (typeof window === 'undefined' || !window.location.hash) return null;
    const params = new URLSearchParams(window.location.hash.slice(1));
    const service = params.get('service') as ServiceId | null;
    const id = params.get('id');
    if (!service || !id || !SHARED_CONTENT[service]) return null;
    const item = SHARED_CONTENT[service].find((entry) => entry.id === id);
    return item ? { service, id, item } : null;
  } catch {
    return null;
  }
};

export default function App() {
  const [initialSharedRoute] = useState(readSharedRoute);
  const [dataVersion, setDataVersion] = useState(0);
  const [dataReloadKey, setDataReloadKey] = useState(0);
  const [dataStatus, setDataStatus] = useState<'loading' | 'ready' | 'error'>('loading');
  const [dataError, setDataError] = useState('');
  const [operationalAuth, setOperationalAuth] = useState<OperationalAuth>({
    is_member: false,
  });
  const [todayInfo, setTodayInfo] = useState<TodayInfo | null>(null);

  // 1. Initial Mode & Onboarding from Safe Storage
  const [mode, setMode] = useState<AppMode>(() => {
    return modeStorage.getMode() || 'resident';
  });

  const [hasSeenOnboarding, setHasSeenOnboarding] = useState<boolean>(() => {
    // If returning user has completed onboarding, true
    return modeStorage.hasCompletedOnboarding();
  });

  // Main Tabs & Routing
  const [activeTab, setActiveTab] = useState<MainTab>('home');
  const [activeServiceId, setActiveServiceId] = useState<ServiceId | null>(null);

  // Sub-screens & Bottom Sheets
  const [isModeSheetOpen, setIsModeSheetOpen] = useState(false);
  const [isSearchOpen, setIsSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [isNotificationsOpen, setIsNotificationsOpen] = useState(false);
  const [isCustomizeOpen, setIsCustomizeOpen] = useState(false);
  const [isEmergencyOpen, setIsEmergencyOpen] = useState(false);
  const [isAuthOpen, setIsAuthOpen] = useState(false);

  // Detail Modal
  const [detailItem, setDetailItem] = useState<any>(() => initialSharedRoute?.item || null);
  const [detailType, setDetailType] = useState<string>(() => initialSharedRoute?.service || '');

  // User State
  const [downloadedCouponIds, setDownloadedCouponIds] = useState<string[]>([]);
  const [usedCouponIds, setUsedCouponIds] = useState<string[]>([]);
  const [savedItems, setSavedItems] = useState<Array<{
    id: string;
    type: ServiceId;
    title: string;
  }>>([]);
  const [residentShortcuts, setResidentShortcuts] = useState<ServiceId[]>(() => {
    return shortcutStorage.getShortcuts('resident');
  });
  const [touristShortcuts, setTouristShortcuts] = useState<ServiceId[]>(() => {
    return shortcutStorage.getShortcuts('tourist');
  });
  const [homeSections, setHomeSections] = useState<HomeSectionConfig[]>(INITIAL_HOME_SECTIONS);
  const [toast, setToast] = useState<ToastData | null>(null);
  const [confirmDialog, setConfirmDialog] = useState<{
    isOpen: boolean;
    title: string;
    message: string;
    onConfirm: () => void;
  } | null>(null);

  // Desktop Simulator Frame Settings (360px, 390px, 430px, full)
  const [frameWidth, setFrameWidth] = useState<'390' | '360' | '430' | '768' | 'full'>('390');

  const showToast = useCallback((message: string, type: 'success' | 'info' | 'warning' = 'info') => {
    setToast({
      id: Date.now().toString(),
      message,
      type,
    });
  }, []);

  useEffect(() => {
    const controller = new AbortController();
    setDataStatus('loading');
    setDataError('');

    loadOperationalData(controller.signal)
      .then((result) => {
        setOperationalAuth(result.auth);
        setTodayInfo(result.todayInfo);
        setDataVersion((current) => current + 1);
        setDataStatus('ready');
        const route = readSharedRoute();
        if (route) {
          setDetailItem(route.item);
          setDetailType(route.service);
        }
      })
      .catch((error) => {
        if (error instanceof DOMException && error.name === 'AbortError') return;
        setDataError(error instanceof Error ? error.message : '운영 데이터를 불러오지 못했습니다.');
        setDataStatus('error');
      });

    return () => controller.abort();
  }, [dataReloadKey]);

  // 2. Direct Deeplink Support (shared content renders on the first paint)
  useEffect(() => {
    const handleHashRouting = () => {
      const route = readSharedRoute();
      if (route) {
        setDetailItem(route.item);
        setDetailType(route.service);
      } else {
        setDetailItem(null);
        setDetailType('');
      }
    };

    window.addEventListener('hashchange', handleHashRouting);
    window.addEventListener('popstate', handleHashRouting);
    return () => {
      window.removeEventListener('hashchange', handleHashRouting);
      window.removeEventListener('popstate', handleHashRouting);
    };
  }, []);

  const openDetail = useCallback((item: any, type: ServiceId) => {
    setDetailItem(item);
    setDetailType(type);
    try {
      const hash = `#service=${encodeURIComponent(type)}&id=${encodeURIComponent(item.id)}`;
      if (window.location.hash !== hash) {
        window.history.pushState({ screen: 'detail' }, '', hash);
      }
    } catch {
      // Detail access still works if History API is unavailable.
    }
  }, []);

  const closeDetail = useCallback(() => {
    setDetailItem(null);
    setDetailType('');
    try {
      if (window.location.hash) {
        window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}`);
      }
    } catch {
      // Ignore URL cleanup failures.
    }
  }, []);

  // 3. Mode Switch Handler (Immediate state transition & persistent storage)
  const handleModeSwitch = (newMode: AppMode) => {
    if (newMode === mode) return;
    setMode(newMode);
    modeStorage.setMode(newMode);
    showToast(
      newMode === 'resident'
        ? '교민 모드로 전환되었습니다 (생활·중고·구인 중심)'
        : '관광객 모드로 전환되었습니다 (맛집·투어·쿠폰 중심)',
      'success'
    );
  };

  // 4. Onboarding Complete Handler
  const handleOnboardingComplete = (selectedMode: AppMode) => {
    setMode(selectedMode);
    modeStorage.setMode(selectedMode);
    modeStorage.setOnboardingCompleted();
    setHasSeenOnboarding(true);
    showToast(
      `${selectedMode === 'resident' ? '교민' : '관광객'} 모드로 시작합니다.`,
      'success'
    );
  };

  // 5. Reset Onboarding for testing / demo
  const handleResetOnboarding = () => {
    modeStorage.resetOnboarding();
    setHasSeenOnboarding(false);
    setIsSearchOpen(false);
    setIsNotificationsOpen(false);
    setIsCustomizeOpen(false);
    setIsModeSheetOpen(false);
    setActiveServiceId(null);
    setActiveTab('home');
    closeDetail();
    showToast('모드 선택 온보딩 화면으로 초기화되었습니다.', 'info');
  };

  // Service Navigation
  const handleNavigateService = (serviceId: ServiceId) => {
    setActiveServiceId(serviceId);
  };

  // Coupon Download
  const handleDownloadCoupon = (coupon: CouponItem) => {
    if (!downloadedCouponIds.includes(coupon.id)) {
      setDownloadedCouponIds([...downloadedCouponIds, coupon.id]);
      showToast(`'${coupon.storeName}' 쿠폰이 내 보관함에 저장되었습니다!`, 'success');
    } else {
      showToast('이미 보관된 쿠폰입니다.', 'info');
    }
  };

  const handleUseCoupon = (coupon: CouponItem) => {
    if (!downloadedCouponIds.includes(coupon.id)) {
      showToast('먼저 쿠폰을 내 쿠폰함에 담아주세요.', 'warning');
      return;
    }
    if (!usedCouponIds.includes(coupon.id)) {
      setUsedCouponIds((current) => [...current, coupon.id]);
      showToast('사용 완료로 표시했습니다. 실제 쿠폰 사용이나 검증은 이루어지지 않습니다.', 'success');
    }
  };

  const handleToggleSaved = (item: any, type: ServiceId) => {
    setSavedItems((current) => {
      const exists = current.some((entry) => entry.id === item.id);
      if (exists) {
        showToast('저장 목록에서 삭제했습니다.', 'info');
        return current.filter((entry) => entry.id !== item.id);
      }
      showToast('MY 저장 목록에 추가했습니다.', 'success');
      return [
        ...current,
        {
          id: item.id,
          type,
          title: item.title || item.name || '저장한 항목',
        },
      ];
    });
  };

  // Back Navigation Handler
  const handleBack = () => {
    if (detailItem) {
      closeDetail();
    } else if (activeServiceId) {
      setActiveServiceId(null);
    } else if (isCustomizeOpen) {
      setIsCustomizeOpen(false);
    } else if (isNotificationsOpen) {
      setIsNotificationsOpen(false);
    } else if (isSearchOpen) {
      setIsSearchOpen(false);
    } else if (activeTab !== 'home') {
      setActiveTab('home');
    }
  };

  const isSubPage =
    Boolean(activeServiceId) ||
    isCustomizeOpen ||
    isNotificationsOpen ||
    isSearchOpen;

  // Title for AppHeader
  let headerTitle: string | undefined = undefined;
  if (isCustomizeOpen) headerTitle = '홈 화면 꾸미기';
  else if (isNotificationsOpen) headerTitle = '알림 센터';
  else if (isSearchOpen) headerTitle = '통합 검색';
  else if (activeServiceId) {
    const sName =
      activeServiceId === 'news'
        ? '필리핀뉴스'
        : activeServiceId === 'marketplace'
        ? '중고거래'
        : activeServiceId === 'restaurants'
        ? '맛집찾기'
        : activeServiceId === 'businesses'
        ? '업체찾기'
        : activeServiceId === 'life_info'
        ? '생활정보'
        : activeServiceId === 'jobs'
        ? '구인구직'
        : activeServiceId === 'real_estate'
        ? '부동산'
        : activeServiceId === 'chatrooms'
        ? '단톡방'
        : activeServiceId === 'events'
        ? '이벤트'
        : '쿠폰사용';
    headerTitle = sName;
  } else if (activeTab === 'services') {
    headerTitle = '전체 메뉴';
  } else if (activeTab === 'mypage') {
    headerTitle = '마이페이지';
  }

  // Render Core Content
  const renderMainContent = () => {
    // If first-time user without completed onboarding, show onboarding screen
    if (!hasSeenOnboarding) {
      return (
        <OnboardingModeSelectScreen
          onSelectMode={handleOnboardingComplete}
        />
      );
    }

    if (dataStatus === 'loading') {
      return (
        <div className="p-4 pb-24" aria-live="polite">
          <LoadingSkeleton type="list" count={6} />
        </div>
      );
    }

    if (dataStatus === 'error') {
      return (
        <div className="p-4 pb-24">
          <div className="rounded-2xl border border-[#F8C9C5] bg-white p-6 text-center">
            <h2 className="text-sm font-bold text-[#183247]">운영 정보를 불러오지 못했습니다</h2>
            <p className="mt-1 text-xs text-[#617789]">{dataError}</p>
            <button
              type="button"
              onClick={() => setDataReloadKey((current) => current + 1)}
              className="mt-4 min-h-[44px] rounded-xl bg-[#079BE8] px-5 text-xs font-bold text-white"
            >
              다시 시도
            </button>
          </div>
        </div>
      );
    }

    if (isSearchOpen) {
      return (
        <div className="p-4">
          <UniversalSearchScreen
            key={`search-${dataVersion}`}
            initialSearchTerm={searchQuery}
            currentMode={mode}
            onSelectItem={(item, type) => openDetail(item, type as ServiceId)}
          />
        </div>
      );
    }

    if (isNotificationsOpen) {
      return (
        <div className="p-4">
          <NotificationScreen />
        </div>
      );
    }

    if (isCustomizeOpen) {
      return (
        <div className="p-4">
          <HomeCustomizeScreen
            currentMode={mode}
            currentShortcuts={mode === 'resident' ? residentShortcuts : touristShortcuts}
            onSaveShortcuts={(newShortcuts) => {
              if (mode === 'resident') {
                setResidentShortcuts(newShortcuts);
                shortcutStorage.setShortcuts('resident', newShortcuts);
              } else {
                setTouristShortcuts(newShortcuts);
                shortcutStorage.setShortcuts('tourist', newShortcuts);
              }
              showToast('홈 바로가기가 변경되었습니다.', 'success');
            }}
            onClose={() => setIsCustomizeOpen(false)}
            showToast={showToast}
          />
        </div>
      );
    }

    if (activeServiceId) {
      return (
        <div className="p-4">
          <ServiceListView
            serviceId={activeServiceId}
            onBack={() => setActiveServiceId(null)}
            onDownloadCoupon={handleDownloadCoupon}
            downloadedCouponIds={downloadedCouponIds}
            onShowToast={showToast}
            savedItemIds={savedItems.map((item) => item.id)}
            onToggleSaved={handleToggleSaved}
            usedCouponIds={usedCouponIds}
            onUseCoupon={handleUseCoupon}
          />
        </div>
      );
    }

    // Main Tab Routing
    switch (activeTab) {
      case 'home':
        return (
          <div className="p-4">
            {mode === 'resident' ? (
              <ResidentHomeScreen
                key={`resident-${dataVersion}`}
                onNavigateService={handleNavigateService}
                onSelectNews={(item) => openDetail(item, 'news')}
                onSelectMarket={(item) => openDetail(item, 'marketplace')}
                onSelectLife={(item) => openDetail(item, 'life_info')}
                onSelectJob={(item) => openDetail(item, 'jobs')}
                onSelectRealEstate={(item) => openDetail(item, 'real_estate')}
                onSelectChat={(item) => openDetail(item, 'chatrooms')}
                onOpenEmergency={() => setIsEmergencyOpen(true)}
                customShortcuts={residentShortcuts}
                onOpenCustomize={() => setIsCustomizeOpen(true)}
                todayInfo={todayInfo}
              />
            ) : (
              <TouristHomeScreen
                key={`tourist-${dataVersion}`}
                onNavigateService={handleNavigateService}
                onSelectRestaurant={(item) => openDetail(item, 'restaurants')}
                onSelectBusiness={(item) => openDetail(item, 'businesses')}
                onSelectEvent={(item) => openDetail(item, 'events')}
                onSelectCoupon={(item) => openDetail(item, 'coupons')}
                onOpenEmergency={() => setIsEmergencyOpen(true)}
                onOpenSearch={(kw) => {
                  setSearchQuery(kw || '');
                  setIsSearchOpen(true);
                }}
                customShortcuts={touristShortcuts}
                onOpenCustomize={() => setIsCustomizeOpen(true)}
                todayInfo={todayInfo}
                onShowToast={showToast}
              />
            )}
          </div>
        );

      case 'news':
        return (
          <div className="p-4">
            <ServiceListView
              serviceId="news"
              onBack={() => setActiveTab('home')}
              onDownloadCoupon={handleDownloadCoupon}
              downloadedCouponIds={downloadedCouponIds}
              savedItemIds={savedItems.map((item) => item.id)}
              onToggleSaved={handleToggleSaved}
            />
          </div>
        );

      case 'chatrooms':
        return (
          <div className="p-4">
            <ServiceListView
              serviceId="chatrooms"
              onBack={() => setActiveTab('home')}
              onDownloadCoupon={handleDownloadCoupon}
              downloadedCouponIds={downloadedCouponIds}
              savedItemIds={savedItems.map((item) => item.id)}
              onToggleSaved={handleToggleSaved}
            />
          </div>
        );

      case 'search':
        // Tab 2 for tourist: Dining/Tour
        return (
          <div className="p-4">
            {mode === 'resident' ? (
              <ServiceListView
                serviceId="marketplace"
                onBack={() => setActiveTab('home')}
                onDownloadCoupon={handleDownloadCoupon}
                downloadedCouponIds={downloadedCouponIds}
                savedItemIds={savedItems.map((item) => item.id)}
                onToggleSaved={handleToggleSaved}
              />
            ) : (
              <ServiceListView
                serviceId="restaurants"
                onBack={() => setActiveTab('home')}
                onDownloadCoupon={handleDownloadCoupon}
                downloadedCouponIds={downloadedCouponIds}
                savedItemIds={savedItems.map((item) => item.id)}
                onToggleSaved={handleToggleSaved}
              />
            )}
          </div>
        );

      case 'services':
        return (
          <div className="p-4">
            <AllMenuScreen
              currentMode={mode}
              onModeChange={handleModeSwitch}
              onSelectService={handleNavigateService}
              onOpenEmergency={() => setIsEmergencyOpen(true)}
            />
          </div>
        );

      case 'benefits':
        return (
          <div className="p-4">
            <TouristBenefitsScreen
              onNavigateService={handleNavigateService}
              onSelectCoupon={(item) => openDetail(item, 'coupons')}
              onSelectEvent={(item) => openDetail(item, 'events')}
            />
          </div>
        );

      case 'mypage':
        return (
          <div className="p-4">
            <MyPageScreen
              currentMode={mode}
              onModeChange={handleModeSwitch}
              onOpenCustomize={() => setIsCustomizeOpen(true)}
              onOpenCoupons={() => {
                setActiveServiceId('coupons');
              }}
              onOpenEmergency={() => setIsEmergencyOpen(true)}
              downloadedCouponCount={downloadedCouponIds.length}
              usedCouponCount={usedCouponIds.length}
              savedItems={savedItems}
              onOpenSaved={(saved) => {
                const item =
                  SHARED_CONTENT[saved.type as ServiceId]?.find(
                    (entry) => entry.id === saved.id
                  ) || saved;
                openDetail(item, saved.type as ServiceId);
              }}
              onOpenLogin={() => {
                setIsAuthOpen(true);
              }}
              operationalAuth={operationalAuth}
            />
          </div>
        );

      default:
        return null;
    }
  };

  // Determine frame width class
  const frameWidthClass =
    frameWidth === '360'
      ? 'w-full max-w-[360px]'
      : frameWidth === '390'
      ? 'w-full max-w-[390px]'
      : frameWidth === '430'
      ? 'w-full max-w-[430px]'
      : frameWidth === '768'
      ? 'w-full max-w-[768px]'
      : 'w-full max-w-md';

  return (
    <div className="min-h-screen bg-[#F0F5FA] text-[#183247] flex flex-col items-center justify-start antialiased selection:bg-[#079BE8]/20 selection:text-[#075A9D]">
      {/* Top Desktop Presentation Toolbar */}
      <nav aria-label="기기 미리보기 도구" className="hidden sm:flex w-full bg-white text-[#183247] px-4 py-2 items-center justify-between gap-3 text-xs border-b border-[#E1ECF3] shadow-sm shrink-0 z-40">
        <div className="flex items-center gap-2">
          <BrandLogo size="sm" variant="full" />
          <span className="text-[11px] text-[#617789] hidden md:inline">
            | 공식 브랜드 디자인 리뉴얼
          </span>
        </div>

        <div className="flex items-center gap-2">
          {/* Viewport Width Switcher */}
          <div className="hidden sm:flex items-center bg-[#F1F9FE] border border-[#E1ECF3] rounded-lg p-0.5">
            <button
              type="button"
              onClick={() => setFrameWidth('360')}
              className={`px-2 py-1 rounded text-[11px] font-medium transition-colors ${
                frameWidth === '360' ? 'bg-white text-[#075A9D] font-bold shadow-xs' : 'text-[#617789] hover:text-[#183247]'
              }`}
            >
              360px
            </button>
            <button
              type="button"
              onClick={() => setFrameWidth('390')}
              className={`px-2 py-1 rounded text-[11px] font-medium transition-colors ${
                frameWidth === '390' ? 'bg-white text-[#075A9D] font-bold shadow-xs' : 'text-[#617789] hover:text-[#183247]'
              }`}
            >
              390px
            </button>
            <button
              type="button"
              onClick={() => setFrameWidth('430')}
              className={`px-2 py-1 rounded text-[11px] font-medium transition-colors ${
                frameWidth === '430' ? 'bg-white text-[#075A9D] font-bold shadow-xs' : 'text-[#617789] hover:text-[#183247]'
              }`}
            >
              430px
            </button>
            <button
              type="button"
              onClick={() => setFrameWidth('768')}
              className={`px-2 py-1 rounded text-[11px] font-medium transition-colors ${
                frameWidth === '768' ? 'bg-white text-[#075A9D] font-bold shadow-xs' : 'text-[#617789] hover:text-[#183247]'
              }`}
            >
              태블릿
            </button>
            <button
              type="button"
              onClick={() => setFrameWidth('full')}
              className={`px-2 py-1 rounded text-[11px] font-medium transition-colors ${
                frameWidth === 'full' ? 'bg-white text-[#075A9D] font-bold shadow-xs' : 'text-[#617789] hover:text-[#183247]'
              }`}
            >
              반응형
            </button>
          </div>

          {/* Quick Mode Toggle in Topbar */}
          <button
            type="button"
            onClick={() => setIsModeSheetOpen(true)}
            className={`px-2.5 py-1 rounded-lg text-xs font-bold transition-colors flex items-center gap-1 ${
              mode === 'resident'
                ? 'bg-[#079BE8] text-white hover:bg-[#0879E7]'
                : 'bg-[#00C5E8] text-[#183247] hover:bg-[#00B4D8]'
            }`}
          >
            <span>{mode === 'resident' ? '🌴 교민 모드' : '✈️ 관광객 모드'}</span>
            <span className="text-[10px] opacity-75">▼</span>
          </button>

          {/* Reset Onboarding for testing */}
          <button
            type="button"
            onClick={handleResetOnboarding}
            title="온보딩 첫 실행 화면 다시 보기"
            className="px-2 py-1 bg-[#F1F9FE] hover:bg-[#EAF8FF] border border-[#E1ECF3] rounded-lg text-[11px] text-[#617789] flex items-center gap-1 transition-colors"
          >
            <RotateCcw size={12} />
            <span className="hidden md:inline">온보딩 재실행</span>
          </button>
        </div>
      </nav>

      {/* Main Container / Mobile Device Simulator */}
      <div className="w-full flex-1 flex items-start justify-center p-0 sm:py-6 sm:px-4">
        <main
          className={`${frameWidthClass} bg-[#F7FBFE] min-h-screen sm:min-h-[844px] sm:max-h-[92vh] sm:rounded-[40px] shadow-2xl flex flex-col overflow-hidden sm:border-[8px] sm:border-[#C9D7E2] relative transition-all duration-300`}
        >
          {/* Simulated Smartphone Status Bar */}
          <div className="hidden sm:flex items-center justify-between px-6 pt-3 pb-1 bg-white select-none text-[12px] font-bold text-[#183247] border-b border-[#ECF2F6]">
            <span>09:41</span>
            {/* Dynamic Island / Notch */}
            <div className="w-20 h-4 bg-[#183247] rounded-full mx-auto" />
            <div className="flex items-center gap-1.5 text-[#183247]">
              <Wifi size={13} />
              <Battery size={15} />
            </div>
          </div>

          {/* App Header (shown except onboarding screen) */}
          {hasSeenOnboarding && (
            <AppHeader
              title={headerTitle}
              showBack={isSubPage || (activeTab !== 'home' && !headerTitle)}
              onBack={handleBack}
              currentMode={mode}
              onOpenModeSheet={() => setIsModeSheetOpen(true)}
              onOpenNotifications={() => setIsNotificationsOpen(true)}
              onOpenSearch={() => {
                setSearchQuery('');
                setIsSearchOpen(true);
              }}
              unreadCount={2}
            />
          )}

          {/* Scrollable Viewport Content */}
          <div className="flex-1 overflow-y-auto no-scrollbar relative">
            <div
              key={
                !hasSeenOnboarding
                  ? 'onboarding'
                  : `${mode}-${activeTab}-${activeServiceId || 'root'}-${isSearchOpen ? 'search' : ''}-${isNotificationsOpen ? 'notifications' : ''}`
              }
              className="min-h-full app-screen-enter"
            >
              {renderMainContent()}
            </div>
          </div>

          {/* Bottom Navigation (shown on main tabs) */}
          {hasSeenOnboarding && !isSubPage && (
            <BottomNavigation
              currentTab={activeTab}
              onTabChange={(tab) => {
                setActiveTab(tab);
                setActiveServiceId(null);
                setIsSearchOpen(false);
                setIsNotificationsOpen(false);
                setIsCustomizeOpen(false);
              }}
              currentMode={mode}
            />
          )}

          {/* Simulated Home Indicator for Phone Shell */}
          <div className="hidden sm:block h-3 bg-white w-full select-none shrink-0">
            <div className="w-32 h-1 bg-[#CBD5E1] rounded-full mx-auto" />
          </div>
        </main>
      </div>

      {/* Global Modals & Sheets */}
      {/* 1. Mode Switch BottomSheet */}
      <ModeSwitchSheet
        isOpen={isModeSheetOpen}
        onClose={() => setIsModeSheetOpen(false)}
        currentMode={mode}
        onSelectMode={handleModeSwitch}
      />

      {/* 2. Detail Modal */}
      <DetailModal
        isOpen={Boolean(detailItem)}
        onClose={closeDetail}
        item={detailItem}
        type={detailType}
        onDownloadCoupon={handleDownloadCoupon}
        isCouponDownloaded={
          detailItem && downloadedCouponIds.includes(detailItem.id)
        }
        isSaved={detailItem ? savedItems.some((entry) => entry.id === detailItem.id) : false}
        onToggleSaved={() => {
          if (detailItem && detailType) {
            handleToggleSaved(detailItem, detailType as ServiceId);
          }
        }}
        isCouponUsed={detailItem ? usedCouponIds.includes(detailItem.id) : false}
        onUseCoupon={handleUseCoupon}
      />

      {/* 3. Emergency Contacts Sheet */}
      <EmergencyContactsSheet
        isOpen={isEmergencyOpen}
        onClose={() => setIsEmergencyOpen(false)}
      />

      {/* 4. Auth Modal (Cursor Integration Ready) */}
      <AuthModal
        isOpen={isAuthOpen}
        onClose={() => setIsAuthOpen(false)}
      />

      {/* 5. Toast Feedback */}
      <ToastMessage toast={toast} onClose={() => setToast(null)} />

      {/* 6. Confirm Dialog */}
      {confirmDialog && (
        <ConfirmDialog
          isOpen={confirmDialog.isOpen}
          title={confirmDialog.title}
          message={confirmDialog.message}
          onConfirm={confirmDialog.onConfirm}
          onClose={() => setConfirmDialog(null)}
        />
      )}
    </div>
  );
}
