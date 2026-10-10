import React, { useState, useMemo } from 'react';
import {
  SERVICES_META,
  MOCK_RESTAURANTS_LIST,
  MOCK_BUSINESSES_LIST,
  MOCK_COUPONS_LIST,
  MOCK_EVENTS_LIST,
} from '../data/mockData';
import {
  ServiceId,
  RestaurantItem,
  BusinessItem,
  CouponItem,
  EventItem,
} from '../types';
import { SectionHeader } from '../components/common/SectionHeader';
import { ServiceCard } from '../components/common/ServiceCard';
import { ContentCard } from '../components/common/ContentCard';
import { CouponCard } from '../components/cards/CouponCard';
import { CategoryChip } from '../components/common/CategoryChip';
import { EmptyState } from '../components/common/EmptyState';
import { AppMap, AppMapPlace } from '../components/common/AppMap';
import { CebuHelperWidget } from '../components/widgets/CebuHelperWidget';
import { TodayInfo } from '../services/operationalData';
import {
  requestCurrentLocation,
  UserLocation,
} from '../utils/location';
import {
  Search,
  MapPin,
  List,
  Map as MapIcon,
  Heart,
  ChevronRight,
  Compass,
  Utensils,
  Coffee,
  Sparkles,
  ShoppingBag,
  Pencil,
  LocateFixed,
  LoaderCircle,
} from 'lucide-react';

interface TouristHomeScreenProps {
  onNavigateService: (serviceId: ServiceId) => void;
  onSelectRestaurant: (item: RestaurantItem) => void;
  onSelectBusiness: (item: BusinessItem) => void;
  onSelectEvent: (item: EventItem) => void;
  onSelectCoupon: (item: CouponItem) => void;
  onOpenEmergency: () => void;
  onOpenSearch: (keyword?: string) => void;
  customShortcuts: ServiceId[];
  onOpenCustomize: () => void;
  todayInfo: TodayInfo | null;
  onShowToast?: (message: string, type?: 'success' | 'info' | 'warning') => void;
}

type TouristCategoryTab = '맛집' | '카페' | '마사지' | '호핑투어' | '쇼핑';
type TouristArea = '전체 세부' | '막탄' | '세부시티' | 'IT Park' | '아얄라 주변' | '만다웨';
const toCoordinate = (value?: number | string) => {
  const parsed = typeof value === 'number' ? value : Number.parseFloat(String(value || ''));
  return Number.isFinite(parsed) ? parsed : null;
};

const distanceInKm = (from: UserLocation, latitude: number, longitude: number) => {
  const earthRadiusKm = 6371;
  const toRadians = (degrees: number) => (degrees * Math.PI) / 180;
  const latitudeDelta = toRadians(latitude - from.latitude);
  const longitudeDelta = toRadians(longitude - from.longitude);
  const startLatitude = toRadians(from.latitude);
  const endLatitude = toRadians(latitude);
  const haversine =
    Math.sin(latitudeDelta / 2) ** 2 +
    Math.cos(startLatitude) *
      Math.cos(endLatitude) *
      Math.sin(longitudeDelta / 2) ** 2;

  return earthRadiusKm * 2 * Math.atan2(Math.sqrt(haversine), Math.sqrt(1 - haversine));
};

const formatDistance = (distance: number) =>
  distance < 1 ? `${Math.max(1, Math.round(distance * 1000)).toLocaleString('ko-KR')}m` : `${distance.toFixed(1)}km`;

export const TouristHomeScreen: React.FC<TouristHomeScreenProps> = ({
  onNavigateService,
  onSelectRestaurant,
  onSelectBusiness,
  onSelectEvent,
  onSelectCoupon,
  onOpenEmergency,
  onOpenSearch,
  customShortcuts,
  onOpenCustomize,
  todayInfo,
  onShowToast,
}) => {
  // 1. Filter states
  const [selectedArea, setSelectedArea] = useState<TouristArea>('전체 세부');
  const [recommendCategory, setRecommendCategory] = useState<TouristCategoryTab>('맛집');
  const [viewMode, setViewMode] = useState<'list' | 'map'>('list');
  const [likedIds, setLikedIds] = useState<string[]>([]);
  const [searchKeyword, setSearchKeyword] = useState('');
  const [userLocation, setUserLocation] = useState<UserLocation | null>(null);
  const [locationStatus, setLocationStatus] = useState<'idle' | 'loading' | 'success' | 'error'>('idle');
  const [locationError, setLocationError] = useState('');
  const [nearbyRadius, setNearbyRadius] = useState(5);

  const personalizedServices = useMemo(() => {
    return customShortcuts
      .map((id) => SERVICES_META.find((service) => service.id === id))
      .filter((service): service is NonNullable<typeof service> => Boolean(service));
  }, [customShortcuts]);

  const recommendedTouristServices = useMemo(() => {
    return SERVICES_META.filter(
      (service) =>
        service.targetMode === 'tourist' && !customShortcuts.includes(service.id)
    );
  }, [customShortcuts]);

  // Resident Services (Bridge navigation)
  const residentServices = useMemo(() => {
    return SERVICES_META.filter(
      (service) =>
        service.targetMode === 'resident' && !customShortcuts.includes(service.id)
    );
  }, [customShortcuts]);

  const areaList: TouristArea[] = [
    '전체 세부',
    '막탄',
    '세부시티',
    'IT Park',
    '아얄라 주변',
    '만다웨',
  ];

  const categoryList: Array<{ label: TouristCategoryTab; icon: any }> = [
    { label: '맛집', icon: Utensils },
    { label: '카페', icon: Coffee },
    { label: '마사지', icon: Sparkles },
    { label: '호핑투어', icon: Compass },
    { label: '쇼핑', icon: ShoppingBag },
  ];

  const toggleLike = (id: string) => {
    setLikedIds((prev) =>
      prev.includes(id) ? prev.filter((i) => i !== id) : [...prev, id]
    );
  };

  const filterByArea = (itemArea?: string) => {
    if (selectedArea === '전체 세부' || !itemArea) return true;
    if (selectedArea === '막탄') return itemArea.includes('막탄');
    if (selectedArea === '세부시티') return itemArea.includes('세부 시티') || itemArea.includes('IT');
    if (selectedArea === 'IT Park') return itemArea.includes('IT');
    if (selectedArea === '아얄라 주변') return itemArea.includes('아얄라') || itemArea.includes('비즈니스');
    if (selectedArea === '만다웨') return itemArea.includes('만다우에') || itemArea.includes('AS포츄나');
    return true;
  };

  const selectArea = (area: TouristArea) => {
    setSelectedArea(area);
    setUserLocation(null);
    setLocationStatus('idle');
    setLocationError('');
  };

  const findCurrentLocation = async () => {
    setLocationStatus('loading');
    setLocationError('');

    try {
      const location = await requestCurrentLocation();
      setUserLocation(location);
      setSelectedArea('전체 세부');
      setLocationStatus('success');
      onShowToast?.('현재 위치에서 가까운 업체순으로 정렬했습니다.', 'success');
    } catch (error) {
      const message =
        error instanceof Error
          ? error.message
          : '현재 위치를 확인할 수 없습니다. 지역을 직접 선택해주세요.';
      setUserLocation(null);
      setLocationStatus('error');
      setLocationError(message);
      onShowToast?.(message, 'warning');
    }
  };

  // Filtered dataset for "내 주변 추천"
  const nearbyPlaces = useMemo(() => {
    const list: Array<{
      id: string;
      name: string;
      category: string;
      area: string;
      desc: string;
      thumbnail: string;
      hasCoupon: boolean;
      lat?: number | string;
      lng?: number | string;
      type: 'restaurant' | 'business';
      rawItem: any;
    }> = [];

    // Restaurants
    MOCK_RESTAURANTS_LIST.forEach((r) => {
      if (filterByArea(r.area)) {
        list.push({
          id: r.id,
          name: r.name,
          category: r.category,
          area: r.area,
          desc: r.signatureMenu,
          thumbnail: r.thumbnail,
          hasCoupon: r.couponAvailable,
          lat: r.lat,
          lng: r.lng,
          type: 'restaurant',
          rawItem: r,
        });
      }
    });

    // Businesses
    MOCK_BUSINESSES_LIST.forEach((b) => {
      if (filterByArea(b.area)) {
        list.push({
          id: b.id,
          name: b.name,
          category: b.category,
          area: b.area,
          desc: b.perk,
          thumbnail: b.thumbnail,
          hasCoupon: b.perk.includes('할인'),
          lat: b.lat,
          lng: b.lng,
          type: 'business',
          rawItem: b,
        });
      }
    });

    if (!userLocation) return list;

    return list
      .map((place) => {
        const latitude = toCoordinate(place.lat);
        const longitude = toCoordinate(place.lng);
        if (latitude === null || longitude === null) return { ...place, distanceKm: null };
        return {
          ...place,
          distanceKm: distanceInKm(userLocation, latitude, longitude),
        };
      })
      .filter(
        (place): place is typeof place & { distanceKm: number } =>
          place.distanceKm !== null && place.distanceKm <= nearbyRadius
      )
      .sort((left, right) => left.distanceKm - right.distanceKm);
  }, [selectedArea, userLocation, nearbyRadius]);

  // Filtered dataset for "세부에서 어디 갈까?"
  const categoryPlaces = useMemo(() => {
    if (recommendCategory === '맛집') {
      return MOCK_RESTAURANTS_LIST.filter(
        (r) => !r.category.includes('카페') && filterByArea(r.area)
      );
    }
    if (recommendCategory === '카페') {
      return MOCK_RESTAURANTS_LIST.filter(
        (r) => r.category.includes('카페') && filterByArea(r.area)
      );
    }
    if (recommendCategory === '마사지') {
      return MOCK_BUSINESSES_LIST.filter(
        (b) => b.category.includes('마사지') && filterByArea(b.area)
      );
    }
    if (recommendCategory === '호핑투어') {
      return MOCK_BUSINESSES_LIST.filter(
        (b) => b.category.includes('호핑') && filterByArea(b.area)
      );
    }
    if (recommendCategory === '쇼핑') {
      return MOCK_BUSINESSES_LIST.filter(
        (b) => b.category.includes('쇼핑') && filterByArea(b.area)
      );
    }
    return MOCK_RESTAURANTS_LIST;
  }, [recommendCategory, selectedArea]);

  return (
    <div className="space-y-6 pb-24">
      {/* 1. 상단 여행 환영 영역 & 빠른 검색 (공식 브랜드 그라데이션 적용) */}
      <section className="bg-gradient-to-r from-[#00C5E8] via-[#079BE8] to-[#0879E7] text-white rounded-3xl p-5 shadow-sm text-left">
        <div className="flex items-center justify-between mb-2">
          <span className="text-[11px] font-extrabold text-white uppercase tracking-wider bg-white/20 backdrop-blur-xs px-2.5 py-0.5 rounded-full">
            CEBU TRAVEL GUIDE
          </span>
          <span className="text-[10px] text-white/90 font-medium">
            관광객 맞춤 모드
          </span>
        </div>

        <h1 className="text-lg sm:text-xl font-black text-white tracking-tight leading-snug mb-1">
          세부 여행, 더 편리하고 알뜰하게
        </h1>
        <p className="text-xs text-white/90 leading-relaxed mb-4">
          맛집·스파·투어와 등록된 혜택 정보를 확인하세요.
        </p>

        {/* Tourist Search Input */}
        <form
          onSubmit={(event) => {
            event.preventDefault();
            onOpenSearch(searchKeyword.trim());
          }}
          className="bg-white text-[#183247] rounded-2xl p-1.5 flex items-center gap-1 shadow-md focus-within:ring-2 focus-within:ring-white/50"
        >
          <Search size={18} className="text-[#079BE8] shrink-0 ml-2" />
          <input
            type="search"
            value={searchKeyword}
            onChange={(event) => setSearchKeyword(event.target.value)}
            placeholder="맛집, 마사지, 투어, 업체를 검색하세요"
            className="min-w-0 flex-1 h-9 px-1 text-xs text-[#183247] placeholder:text-[#8799A8] outline-none bg-transparent"
          />
          <button
            type="submit"
            className="min-h-[36px] px-3 text-[11px] font-bold text-white bg-[#079BE8] hover:bg-[#0879E7] rounded-xl shrink-0"
          >
            검색
          </button>
        </form>
      </section>

      {/* 2. 지역 선택 (Area Selector) */}
      <section className="space-y-2">
        <div className="flex items-center justify-between px-1">
          <div className="flex items-center gap-1.5 text-xs font-bold text-[#183247]">
            <MapPin size={14} className="text-[#079BE8]" />
            <span>탐색 지역 선택</span>
          </div>
          <button
            type="button"
            onClick={findCurrentLocation}
            disabled={locationStatus === 'loading'}
            className="flex min-h-[34px] items-center gap-1.5 rounded-xl border border-[#B9E5FC] bg-[#EAF8FF] px-2.5 text-[11px] font-bold text-[#075A9D] transition-colors hover:bg-[#D9F1FD] disabled:cursor-wait disabled:opacity-70"
          >
            {locationStatus === 'loading' ? (
              <LoaderCircle size={14} className="animate-spin" />
            ) : (
              <LocateFixed size={14} className="text-[#079BE8]" />
            )}
            <span>
              {locationStatus === 'loading'
                ? '위치 확인 중'
                : userLocation
                  ? '현재 위치 갱신'
                  : '현재 위치로 찾기'}
            </span>
          </button>
        </div>

        {/* Area selection chips */}
        <div className="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
          {areaList.map((area) => (
            <CategoryChip
              key={area}
              label={area}
              active={!userLocation && selectedArea === area}
              onClick={() => selectArea(area)}
            />
          ))}
        </div>
        {locationError && (
          <p role="alert" className="px-1 text-[11px] leading-relaxed text-[#B45309]">
            {locationError}
          </p>
        )}
        {userLocation && (
          <p className="flex items-center gap-1 px-1 text-[11px] font-medium text-[#075A9D]">
            <LocateFixed size={12} />
            현재 위치를 기준으로 가까운 장소를 보여드립니다.
          </p>
        )}
      </section>

      {/* Weather & Currency Travel Helper */}
      <CebuHelperWidget
        mode="tourist"
        onOpenEmergencySheet={onOpenEmergency}
        todayInfo={todayInfo}
      />

      {/* 3. 개인화 바로가기 */}
      <section className="space-y-3">
        <div className="flex items-center justify-between px-1">
          <div>
            <h2 className="text-base font-bold text-[#183247] tracking-tight">
              나만의 바로가기
            </h2>
            <p className="text-xs text-[#617789] mt-0.5 leading-normal">
              선택한 메뉴를 원하는 순서대로 이용하세요
            </p>
          </div>
          <button
            type="button"
            onClick={onOpenCustomize}
            className="min-h-[36px] px-2.5 py-1 bg-[#EAF8FF] hover:bg-[#D9F1FD] text-[#075A9D] border border-[#B9E5FC] rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-2xs active:scale-[0.98]"
          >
            <Pencil size={13} className="text-[#079BE8]" />
            <span>내 홈 꾸미기</span>
          </button>
        </div>

        {personalizedServices.length > 0 ? (
          <div className="grid grid-cols-4 gap-2">
            {personalizedServices.map((service) => (
              <ServiceCard
                key={service.id}
                service={service}
                onClick={onNavigateService}
                layout="grid"
              />
            ))}
          </div>
        ) : (
          <div className="rounded-2xl border border-dashed border-[#B9E5FC] bg-[#F1F9FE] px-4 py-4 text-center">
            <p className="text-xs font-bold text-[#183247]">선택한 바로가기가 없습니다</p>
            <p className="mt-1 text-[11px] text-[#617789]">
              내 홈 꾸미기에서 자주 쓰는 메뉴를 추가하거나 전체 메뉴를 이용하세요.
            </p>
          </div>
        )}

        {recommendedTouristServices.length > 0 && (
          <div>
            <p className="mb-2 px-1 text-[11px] font-bold text-[#617789]">여행 추천 메뉴</p>
            <div className="grid grid-cols-4 gap-2">
              {recommendedTouristServices.map((service) => (
                <ServiceCard
                  key={service.id}
                  service={service}
                  onClick={onNavigateService}
                  layout="compact"
                />
              ))}
            </div>
          </div>
        )}
      </section>

      {/* 4. 세부 추천 콘텐츠 ("세부에서 어디 갈까?") */}
      <section className="space-y-3">
        <SectionHeader
          title="세부에서 어디 갈까?"
          subtitle="카테고리별 등록 장소 모아보기"
        />

        {/* 5 Category Button Bar */}
        <div className="grid grid-cols-5 gap-1.5">
          {categoryList.map(({ label, icon: IconComponent }) => {
            const isActive = recommendCategory === label;
            return (
              <button
                key={label}
                type="button"
                onClick={() => setRecommendCategory(label)}
                className={`flex flex-col items-center justify-center py-2 px-1 rounded-2xl border transition-all text-center min-h-[56px] ${
                  isActive
                    ? 'bg-[#079BE8] text-white border-[#079BE8] shadow-xs'
                    : 'bg-white text-[#617789] border-[#E1ECF3] hover:bg-[#F1F9FE]'
                }`}
              >
                <IconComponent size={17} className={isActive ? 'text-white' : 'text-[#079BE8]'} />
                <span className="text-[11px] font-bold mt-1 tracking-tight truncate w-full">
                  {label}
                </span>
              </button>
            );
          })}
        </div>

        {/* Card Carousel for Category Places */}
        <div className="space-y-2.5 pt-1">
          {categoryPlaces.length === 0 ? (
            <EmptyState
              title={`${selectedArea}의 ${recommendCategory} 정보가 준비 중입니다`}
              description="다른 지역이나 카테고리를 선택해 보세요."
              actionText={selectedArea === '전체 세부' ? undefined : '전체 세부 보기'}
              onAction={selectedArea === '전체 세부' ? undefined : () => setSelectedArea('전체 세부')}
            />
          ) : (
            categoryPlaces.slice(0, 3).map((item: any) => (
              <ContentCard
                key={item.id}
                title={item.name}
                subtitle={item.signatureMenu || item.perk}
                category={item.category}
                thumbnail={item.thumbnail}
                metadata={[
                  { value: item.area },
                  item.openHours ? { value: item.openHours } : undefined,
                ].filter(Boolean) as any}
                actionText="상세보기"
                onClick={() => {
                  if (item.signatureMenu) onSelectRestaurant(item);
                  else onSelectBusiness(item);
                }}
              />
            ))
          )}
        </div>
      </section>

      {/* 5. 내 주변 추천 */}
      <section className="space-y-3">
        <div className="flex items-center justify-between px-1">
          <div>
            <h2 className="text-base font-bold text-[#183247] tracking-tight">
              내 주변 추천
            </h2>
            <p className="text-xs text-[#617789] mt-0.5 leading-normal">
              {userLocation
                ? `현재 위치 ${nearbyRadius}km 이내 · 가까운 순`
                : `${selectedArea} 지역의 등록 업체 목록`}
            </p>
          </div>

          {/* List vs Map Switcher */}
          <div className="flex items-center bg-[#EAF8FF] p-0.5 rounded-xl border border-[#E1ECF3]">
            <button
              type="button"
              onClick={() => setViewMode('list')}
              className={`min-h-[32px] px-2.5 py-1 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors ${
                viewMode === 'list'
                  ? 'bg-white text-[#075A9D] shadow-xs'
                  : 'text-[#617789] hover:text-[#075A9D]'
              }`}
            >
              <List size={13} />
              <span>목록</span>
            </button>
            <button
              type="button"
              onClick={() => setViewMode('map')}
              className={`min-h-[32px] px-2.5 py-1 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors ${
                viewMode === 'map'
                  ? 'bg-white text-[#075A9D] shadow-xs'
                  : 'text-[#617789] hover:text-[#075A9D]'
              }`}
            >
              <MapIcon size={13} />
              <span>지도</span>
            </button>
          </div>
        </div>

        {userLocation && (
          <div className="flex items-center gap-1.5 overflow-x-auto px-1 pb-0.5 no-scrollbar" aria-label="검색 반경">
            {[1, 3, 5, 10].map((radius) => (
              <button
                key={radius}
                type="button"
                onClick={() => setNearbyRadius(radius)}
                aria-pressed={nearbyRadius === radius}
                className={`min-h-[32px] shrink-0 rounded-full border px-3 text-[11px] font-bold transition-colors ${
                  nearbyRadius === radius
                    ? 'border-[#079BE8] bg-[#079BE8] text-white'
                    : 'border-[#D7E6EF] bg-white text-[#617789]'
                }`}
              >
                {radius}km
              </button>
            ))}
          </div>
        )}

        {/* View Mode: List */}
        {viewMode === 'list' && (
          nearbyPlaces.length === 0 ? (
            <EmptyState
              title={
                userLocation
                  ? `${nearbyRadius}km 이내에 등록된 업체가 없습니다`
                  : `${selectedArea} 추천 업체를 준비하고 있습니다`
              }
              description={
                userLocation
                  ? '검색 반경을 넓히거나 지역을 직접 선택해보세요.'
                  : '다른 지역을 선택하거나 업체찾기에서 등록된 업체를 확인해보세요.'
              }
              actionText={
                userLocation
                  ? nearbyRadius < 10
                    ? '검색 반경 넓히기'
                    : '전체 세부 보기'
                  : selectedArea === '전체 세부'
                    ? undefined
                    : '전체 세부 보기'
              }
              onAction={
                userLocation
                  ? nearbyRadius < 10
                    ? () => setNearbyRadius(nearbyRadius === 1 ? 3 : nearbyRadius === 3 ? 5 : 10)
                    : () => selectArea('전체 세부')
                  : selectedArea === '전체 세부'
                    ? undefined
                    : () => selectArea('전체 세부')
              }
            />
          ) : (
            <div className="space-y-2.5">
              {nearbyPlaces.slice(0, 4).map((place) => {
              const isLiked = likedIds.includes(place.id);
              return (
                <div
                  key={place.id}
                  onClick={() => {
                    if (place.type === 'restaurant') onSelectRestaurant(place.rawItem);
                    else onSelectBusiness(place.rawItem);
                  }}
                  role="button"
                  tabIndex={0}
                  onKeyDown={(event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                      event.preventDefault();
                      if (place.type === 'restaurant') onSelectRestaurant(place.rawItem);
                      else onSelectBusiness(place.rawItem);
                    }
                  }}
                  className="bg-white border border-[#E1ECF3] rounded-2xl p-3.5 hover:border-[#079BE8] transition-colors cursor-pointer text-left group"
                >
                  <div className="flex gap-3">
                    <div className="w-22 h-22 rounded-xl overflow-hidden bg-[#EAF8FF] shrink-0 relative border border-[#E1ECF3]">
                      <img
                        src={place.thumbnail}
                        alt={place.name}
                        referrerPolicy="no-referrer"
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                      {place.hasCoupon && (
                        <span className="absolute top-1 left-1 bg-[#0879E7] text-white text-[9px] font-bold px-1.5 py-0.5 rounded leading-none">
                          쿠폰
                        </span>
                      )}
                    </div>

                    <div className="flex-1 min-w-0 flex flex-col justify-between">
                      <div>
                        <div className="flex items-center justify-between gap-1 mb-0.5">
                          <span className="text-[11px] font-bold text-[#079BE8] truncate">
                            {place.category}
                          </span>
                          <button
                            type="button"
                            onClick={(e) => {
                              e.stopPropagation();
                              toggleLike(place.id);
                            }}
                            className={`p-1 -mr-1 transition-colors ${
                              isLiked ? 'text-[#0879E7]' : 'text-[#8799A8] hover:text-[#0879E7]'
                            }`}
                            aria-label="찜하기"
                          >
                            <Heart size={15} fill={isLiked ? '#0879E7' : 'none'} />
                          </button>
                        </div>

                        <h3 className="text-xs sm:text-sm font-bold text-[#183247] line-clamp-1">
                          {place.name}
                        </h3>

                        <p className="text-[11px] text-[#617789] line-clamp-1 mt-0.5">
                          {place.desc}
                        </p>
                      </div>

                      <div className="mt-2 pt-1 border-t border-[#ECF2F6] flex items-center justify-between text-[11px] text-[#8799A8]">
                        <span className="truncate">
                          {userLocation && 'distanceKm' in place && typeof place.distanceKm === 'number'
                            ? `현재 위치에서 ${formatDistance(place.distanceKm)}`
                            : place.area}
                        </span>
                        <span className="text-xs font-bold text-[#075A9D] group-hover:text-[#079BE8] flex items-center shrink-0">
                          상세보기 <ChevronRight size={13} />
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              );
              })}
            </div>
          )
        )}

        {/* View Mode: in-app map */}
        {viewMode === 'map' && (
          <AppMap
            places={nearbyPlaces.map((place) => ({
              id: `${place.type}-${place.id}`,
              name: place.name,
              category: place.category,
              area: place.area,
              lat: place.lat,
              lng: place.lng,
              rawItem: place.rawItem,
            }))}
            selectedArea={selectedArea}
            heightClassName="h-72"
            userLocation={userLocation}
            onSelectPlace={(place: AppMapPlace) => {
              const rawItem = place.rawItem;
              if (!rawItem) return;
              if (MOCK_RESTAURANTS_LIST.some((item) => item.id === rawItem.id)) {
                onSelectRestaurant(rawItem);
              } else {
                onSelectBusiness(rawItem);
              }
            }}
          />
        )}
      </section>

      {/* 6. 쿠폰 혜택 섹션 ("세부어때 회원 혜택") */}
      <section>
        <SectionHeader
          title="세부어때 회원 혜택"
          subtitle="등록된 쿠폰의 조건과 유효기간을 확인하세요"
          actionText="쿠폰함 가기"
          onAction={() => onNavigateService('coupons')}
        />
        {MOCK_COUPONS_LIST.length === 0 ? (
          <EmptyState title="등록된 쿠폰이 없습니다" description="운영 쿠폰이 등록되면 여기에 표시됩니다." />
        ) : (
          <div className="space-y-2.5">
            {MOCK_COUPONS_LIST.slice(0, 3).map((coupon) => (
              <CouponCard
                key={coupon.id}
                coupon={coupon}
                onClick={() => onSelectCoupon(coupon)}
              />
            ))}
          </div>
        )}
      </section>

      {/* 7. 이벤트 섹션 ("지금 진행 중인 이벤트") */}
      <section>
        <SectionHeader
          title="지금 진행 중인 이벤트"
          subtitle="프로모션 및 세부 축제 일정 안내"
          actionText="전체보기"
          onAction={() => onNavigateService('events')}
        />
        {MOCK_EVENTS_LIST.length === 0 ? (
          <EmptyState title="등록된 이벤트가 없습니다" description="운영 이벤트가 등록되면 여기에 표시됩니다." />
        ) : (
          <div className="space-y-2.5">
            {MOCK_EVENTS_LIST.map((evt) => (
            <button
              key={evt.id}
              type="button"
              onClick={() => onSelectEvent(evt)}
              className="w-full bg-white border border-[#E1ECF3] rounded-2xl p-3 hover:border-[#079BE8] transition-colors text-left flex gap-3"
            >
              <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2 mb-1">
                  <span className="text-[10px] font-bold text-white bg-[#079BE8] px-2 py-0.5 rounded">
                    {evt.badge}
                  </span>
                  <span className="text-[10px] text-[#617789] font-mono truncate">
                    {evt.period}
                  </span>
                </div>
                <h3 className="text-xs sm:text-sm font-bold text-[#183247] line-clamp-2">
                  {evt.title}
                </h3>
                <div className="mt-2 flex items-center justify-between gap-2 text-[11px] text-[#8799A8]">
                  <span className="truncate">{evt.location}</span>
                  <span className="text-[#079BE8] font-bold flex items-center shrink-0">
                    상세보기 <ChevronRight size={12} />
                  </span>
                </div>
              </div>
            </button>
            ))}
          </div>
        )}
      </section>

      {/* 8. 교민 서비스 바로가기 */}
      <section className="bg-white border border-[#E1ECF3] rounded-2xl p-3.5">
        <div className="flex items-center justify-between mb-2">
          <span className="text-xs font-bold text-[#183247]">
            세부 생활정보나 중고거래가 궁금하신가요?
          </span>
          <span className="text-[10px] text-[#8799A8]">교민 서비스 바로가기</span>
        </div>
        <div className="grid grid-cols-3 gap-2">
          {residentServices.slice(0, 3).map((service) => (
            <ServiceCard
              key={service.id}
              service={service}
              onClick={onNavigateService}
              layout="compact"
            />
          ))}
        </div>
      </section>
    </div>
  );
};
