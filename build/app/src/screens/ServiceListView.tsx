import React, { useState } from 'react';
import { ServiceId } from '../types';
import {
  SERVICES_META,
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
} from '../data/mockData';
import { ContentCard } from '../components/common/ContentCard';
import { CategoryChip } from '../components/common/CategoryChip';
import { EmptyState } from '../components/common/EmptyState';
import { DetailModal } from '../components/common/DetailModal';
import { ServiceIcon } from '../components/common/ServiceIcon';
import { Plus, MapPin, Search } from 'lucide-react';

interface ServiceListViewProps {
  serviceId: ServiceId;
  onBack: () => void;
  onDownloadCoupon?: (coupon: any) => void;
  downloadedCouponIds?: string[];
  onShowToast?: (message: string, type: 'success' | 'info') => void;
  savedItemIds?: string[];
  onToggleSaved?: (item: any, serviceId: ServiceId) => void;
  usedCouponIds?: string[];
  onUseCoupon?: (coupon: any) => void;
}

export const ServiceListView: React.FC<ServiceListViewProps> = ({
  serviceId,
  onDownloadCoupon,
  downloadedCouponIds = [],
  savedItemIds = [],
  onToggleSaved,
  usedCouponIds = [],
  onUseCoupon,
  onShowToast,
}) => {
  const serviceMeta = SERVICES_META.find((s) => s.id === serviceId) || SERVICES_META[0];
  const [selectedSubCategory, setSelectedSubCategory] = useState<string>('전체');
  const [selectedLocation, setSelectedLocation] = useState<string>('전체 지역');
  const [sortBy, setSortBy] = useState<'latest' | 'popular'>('latest');
  const [activeItem, setActiveItem] = useState<any>(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [priceFilter, setPriceFilter] = useState<'all' | 'under30' | '30to60' | 'over60'>('all');
  const [priceType, setPriceType] = useState<'전체' | '월세' | '매매' | '단기임대'>('전체');
  const [showMarketplaceForm, setShowMarketplaceForm] = useState(false);
  const [localMarketplaceItems, setLocalMarketplaceItems] = useState<any[]>([]);
  const [marketDraft, setMarketDraft] = useState({
    title: '',
    price: '',
    location: '',
    description: '',
  });

  // Dynamic dataset retrieval based on serviceId
  let items: any[] = [];
  let subCategories: string[] = ['전체'];
  let locationFilters: string[] = [];

  switch (serviceId) {
    case 'news':
      items = MOCK_NEWS_LIST;
      subCategories = ['전체', '이민/체류', '교통/도로', '항공/교통', '영사/안전'];
      break;
    case 'marketplace':
      items = [...localMarketplaceItems, ...MOCK_MARKETPLACE_LIST];
      subCategories = ['전체', '가전/디지털', '오토바이/차량', '가구/인테리어', '스포츠/레저', '생활용품'];
      locationFilters = ['전체 지역', '세부 IT파크', '만다우에', '바닐라드', '막탄'];
      break;
    case 'restaurants':
      items = MOCK_RESTAURANTS_LIST;
      subCategories = ['전체', '해산물 / 필리핀 퓨전', '바베큐 & 필리피노 다이닝', '양식 / 오션뷰 펍', '정통 한식 / 숯불구이', '카페 / 디저트'];
      locationFilters = ['전체 지역', '막탄', '세부 시티', 'IT Park', '만다웨'];
      break;
    case 'businesses':
      items = MOCK_BUSINESSES_LIST;
      subCategories = ['전체', '마사지', '렌터카', '투어', '쇼핑', '병원', '기타 서비스'];
      locationFilters = ['전체 지역', '막탄', '세부시티', '아얄라'];
      break;
    case 'life_info':
      items = MOCK_LIFE_INFO_LIST;
      subCategories = ['전체', '비자·이민', '병원·의료', '학교·교육', '교통·통신', '은행·금융', '기타 생활정보'];
      break;
    case 'jobs':
      items = MOCK_JOBS_LIST;
      subCategories = ['전체', '정규직 (비자 지원)', '정규직', '계약직'];
      locationFilters = ['전체 지역', '세부 IT파크', '막탄', '바닐라드'];
      break;
    case 'real_estate':
      items = MOCK_REAL_ESTATE_LIST;
      subCategories = ['전체', '콘도', '빌라/하우스', '원룸', '상가/오피스'];
      locationFilters = ['전체 지역', '세부 IT파크', '막탄', '세부 비즈니스 파크', '바닐라드'];
      break;
    case 'chatrooms':
      items = MOCK_CHATROOMS_LIST;
      subCategories = ['전체', '자유소통', 'Q&A', '직거래 장터', '채용공고'];
      break;
    case 'events':
      items = MOCK_EVENTS_LIST;
      subCategories = ['전체', '진행중', '예정', '종료'];
      break;
    case 'coupons':
      items = MOCK_COUPONS_LIST;
      subCategories = ['전체', '맛집', '스파', '투어'];
      break;
    default:
      items = [];
  }

  // Filter items
  let filteredItems = items.filter((item) => {
    if (searchTerm.trim()) {
      const query = searchTerm.trim().toLocaleLowerCase();
      const searchable = [
        item.title,
        item.name,
        item.summary,
        item.excerpt,
        item.description,
        item.company,
        item.location,
        item.area,
        item.category,
      ]
        .filter(Boolean)
        .join(' ')
        .toLocaleLowerCase();
      if (!searchable.includes(query)) return false;
    }

    // 1. Category filter
    if (selectedSubCategory !== '전체') {
      const categoryQuery =
        selectedSubCategory === '렌터카' ? '렌트카' : selectedSubCategory;
      const catMatch =
        (item.category && item.category.includes(categoryQuery)) ||
        (item.propertyType && item.propertyType.includes(categoryQuery)) ||
        (item.jobType && item.jobType.includes(categoryQuery)) ||
        (item.status && item.status.includes(categoryQuery));
      if (!catMatch) return false;
    }

    // 2. Location filter (if applicable)
    if (selectedLocation !== '전체 지역' && locationFilters.length > 0) {
      const loc = item.location || item.area || '';
      if (!loc.includes(selectedLocation.replace('전체 지역', ''))) {
        return false;
      }
    }

    if (serviceId === 'real_estate') {
      if (priceType !== '전체' && item.priceType !== priceType) return false;
      if (priceFilter === 'under30' && item.pricePHP >= 30000) return false;
      if (priceFilter === '30to60' && (item.pricePHP < 30000 || item.pricePHP > 60000)) return false;
      if (priceFilter === 'over60' && item.pricePHP <= 60000) return false;
    }

    return true;
  });

  // Sort items
  if (sortBy === 'popular') {
    filteredItems = [...filteredItems].sort((a, b) => {
      const popA = a.readCount || a.views || a.likes || a.rating || a.memberCount || 0;
      const popB = b.readCount || b.views || b.likes || b.rating || b.memberCount || 0;
      return popB - popA;
    });
  }

  return (
    <div className="space-y-4 pb-24">
      {/* Top Banner for this Service */}
      <div className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div
              className="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0"
              style={{ backgroundColor: `${serviceMeta.color}15`, color: serviceMeta.color }}
            >
              <ServiceIcon iconName={serviceMeta.icon} size={22} color={serviceMeta.color} />
            </div>
            <div className="min-w-0">
              <div className="flex items-center gap-2">
                <h1 className="text-base font-bold text-[#183247]">{serviceMeta.name}</h1>
                <span className="text-[10px] font-bold text-[#075A9D] bg-[#EAF8FF] px-2 py-0.5 rounded border border-[#B9E5FC]">
                  {serviceMeta.targetMode === 'resident'
                    ? '교민 추천'
                    : serviceMeta.targetMode === 'tourist'
                    ? '관광 추천'
                    : '공통'}
                </span>
              </div>
              <p className="text-xs text-[#617789] truncate mt-0.5">{serviceMeta.description}</p>
            </div>
          </div>

          {/* Action button in top banner */}
          {serviceId === 'marketplace' && (
            <button
              type="button"
              onClick={() => setShowMarketplaceForm((current) => !current)}
              className="bg-[#079BE8] hover:bg-[#0879E7] text-white px-3 py-2 rounded-xl text-xs font-bold flex items-center gap-1 shrink-0 shadow-2xs transition-colors"
            >
              <Plus size={15} />
              <span>{showMarketplaceForm ? '닫기' : '글쓰기'}</span>
            </button>
          )}
        </div>
      </div>

      {serviceId === 'marketplace' && showMarketplaceForm && (
        <form
          className="space-y-2 rounded-2xl border border-[#B9E5FC] bg-white p-4"
          onSubmit={(event) => {
            event.preventDefault();
            if (!marketDraft.title.trim()) {
              onShowToast?.('상품명을 입력해주세요.', 'info');
              return;
            }
            setLocalMarketplaceItems((current) => [
              {
                id: `local-market-${Date.now()}`,
                title: marketDraft.title.trim(),
                pricePHP: Math.max(0, Number(marketDraft.price) || 0),
                priceKRW: 0,
                location: marketDraft.location.trim() || '지역 미입력',
                status: '판매중',
                postedAt: '방금 전',
                category: '기타',
                seller: '나',
                likes: 0,
                chatCount: 0,
                thumbnail: '',
                description: marketDraft.description.trim(),
              },
              ...current,
            ]);
            setMarketDraft({ title: '', price: '', location: '', description: '' });
            setShowMarketplaceForm(false);
            onShowToast?.('앱 안에 중고거래 글을 임시 등록했습니다.', 'success');
          }}
        >
          <h2 className="text-sm font-bold text-[#183247]">앱에서 중고거래 등록</h2>
          <input
            value={marketDraft.title}
            onChange={(event) => setMarketDraft((current) => ({ ...current, title: event.target.value }))}
            placeholder="상품명"
            className="h-11 w-full rounded-xl border border-[#E1ECF3] px-3 text-xs outline-none focus:border-[#079BE8]"
          />
          <div className="grid grid-cols-2 gap-2">
            <input
              type="number"
              min="0"
              value={marketDraft.price}
              onChange={(event) => setMarketDraft((current) => ({ ...current, price: event.target.value }))}
              placeholder="가격 (₱)"
              className="h-11 min-w-0 rounded-xl border border-[#E1ECF3] px-3 text-xs outline-none focus:border-[#079BE8]"
            />
            <input
              value={marketDraft.location}
              onChange={(event) => setMarketDraft((current) => ({ ...current, location: event.target.value }))}
              placeholder="거래 지역"
              className="h-11 min-w-0 rounded-xl border border-[#E1ECF3] px-3 text-xs outline-none focus:border-[#079BE8]"
            />
          </div>
          <textarea
            value={marketDraft.description}
            onChange={(event) => setMarketDraft((current) => ({ ...current, description: event.target.value }))}
            placeholder="상품 설명"
            rows={4}
            className="w-full resize-none rounded-xl border border-[#E1ECF3] p-3 text-xs outline-none focus:border-[#079BE8]"
          />
          <button
            type="submit"
            className="min-h-[44px] w-full rounded-xl bg-[#079BE8] text-xs font-bold text-white"
          >
            앱에 등록하기
          </button>
        </form>
      )}

      <div className="flex gap-2">
        <label className="relative flex-1">
          <Search
            size={16}
            className="absolute left-3 top-1/2 -translate-y-1/2 text-[#079BE8]"
          />
          <input
            type="search"
            value={searchTerm}
            onChange={(event) => setSearchTerm(event.target.value)}
            placeholder={`${serviceMeta.name}에서 검색`}
            className="h-11 w-full rounded-xl border border-[#E1ECF3] bg-white pl-9 pr-3 text-xs text-[#183247] outline-none focus:border-[#079BE8]"
          />
        </label>
      </div>

      {/* Subcategory Filter Tabs */}
      {subCategories.length > 1 && (
        <div className="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
          {subCategories.map((cat) => (
            <CategoryChip
              key={cat}
              label={cat}
              active={selectedSubCategory === cat}
              onClick={() => setSelectedSubCategory(cat)}
            />
          ))}
        </div>
      )}

      {/* Location Filter Strip */}
      {locationFilters.length > 0 && (
        <div className="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 text-xs">
          <span className="text-[11px] font-bold text-[#617789] shrink-0 pl-1 flex items-center gap-1">
            <MapPin size={12} className="text-[#079BE8]" />
            지역:
          </span>
          {locationFilters.map((loc) => (
            <button
              key={loc}
              type="button"
              onClick={() => setSelectedLocation(loc)}
              className={`px-2.5 py-1 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors ${
                selectedLocation === loc
                  ? 'bg-[#075A9D] text-white'
                  : 'bg-white border border-[#E1ECF3] text-[#617789] hover:bg-[#F1F9FE]'
              }`}
            >
              {loc}
            </button>
          ))}
        </div>
      )}

      {serviceId === 'real_estate' && (
        <div className="space-y-2 rounded-2xl border border-[#E1ECF3] bg-white p-3">
          <div className="flex gap-1.5 overflow-x-auto no-scrollbar">
            {(['전체', '월세', '매매', '단기임대'] as const).map((value) => (
              <CategoryChip
                key={value}
                label={value === '전체' ? '임대·매매 전체' : value}
                active={priceType === value}
                onClick={() => setPriceType(value)}
              />
            ))}
          </div>
          <div className="flex gap-1.5 overflow-x-auto no-scrollbar">
            {([
              ['all', '가격 전체'],
              ['under30', '3만₱ 미만'],
              ['30to60', '3만~6만₱'],
              ['over60', '6만₱ 초과'],
            ] as const).map(([value, label]) => (
              <button
                key={value}
                type="button"
                onClick={() => setPriceFilter(value)}
                className={`whitespace-nowrap rounded-lg px-2.5 py-1.5 text-[11px] font-semibold ${
                  priceFilter === value
                    ? 'bg-[#075A9D] text-white'
                    : 'bg-[#F1F9FE] text-[#617789]'
                }`}
              >
                {label}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Sort strip */}
      <div className="flex items-center justify-between px-1 text-xs text-[#617789]">
        <span>총 {filteredItems.length}개의 항목</span>
        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={() => setSortBy('latest')}
            className={`font-semibold transition-colors ${
              sortBy === 'latest' ? 'text-[#075A9D] font-bold' : 'text-[#8799A8]'
            }`}
          >
            최신순
          </button>
          <span>·</span>
          <button
            type="button"
            onClick={() => setSortBy('popular')}
            className={`font-semibold transition-colors ${
              sortBy === 'popular' ? 'text-[#075A9D] font-bold' : 'text-[#8799A8]'
            }`}
          >
            인기순
          </button>
        </div>
      </div>

      {/* Content list */}
      {filteredItems.length === 0 ? (
        <EmptyState
          title="등록된 항목이 없습니다"
          description={
            items.length === 0
              ? '운영 데이터가 등록되면 여기에 표시됩니다.'
              : '현재 검색어나 필터 조건에 맞는 항목이 없습니다.'
          }
          actionText={items.length > 0 ? '검색·필터 초기화' : undefined}
          onAction={
            items.length > 0
              ? () => {
                  setSearchTerm('');
                  setSelectedSubCategory('전체');
                  setSelectedLocation('전체 지역');
                  setPriceType('전체');
                  setPriceFilter('all');
                }
              : undefined
          }
        />
      ) : (
        <div className="space-y-2.5">
          {filteredItems.map((item) => {
            const isCoupon = serviceId === 'coupons';
            const isDownloaded = isCoupon && downloadedCouponIds.includes(item.id);
            const isUsed = isCoupon && usedCouponIds.includes(item.id);

            return (
              <ContentCard
                key={item.id}
                title={item.title || item.name}
                subtitle={
                  item.summary ||
                  item.excerpt ||
                  item.description ||
                  item.perk ||
                  (item.company ? `${item.company} · ${item.salary}` : undefined)
                }
                category={
                  item.category ||
                  item.jobType ||
                  (item.propertyType ? `${item.propertyType} · ${item.priceType || '월세'}` : undefined)
                }
                thumbnail={item.thumbnail}
                price={
                  item.pricePHP
                    ? { php: item.pricePHP, krw: item.priceKRW }
                    : undefined
                }
                metadata={[
                  item.location ? { value: item.location } : undefined,
                  item.area ? { value: item.area } : undefined,
                  item.source ? { value: item.source } : undefined,
                  item.publishedAt ? { value: item.publishedAt } : undefined,
                  item.postedAt ? { value: item.postedAt } : undefined,
                  item.validUntil ? { value: item.validUntil } : undefined,
                  item.status ? { value: item.status } : undefined,
                ].filter(Boolean) as any}
                actionText={
                  isCoupon
                    ? isUsed
                      ? '사용 완료'
                      : isDownloaded
                      ? '보관됨'
                      : '쿠폰받기'
                    : '상세보기'
                }
                onClick={() => setActiveItem(item)}
              />
            );
          })}
        </div>
      )}

      {/* Detail Modal */}
      <DetailModal
        isOpen={Boolean(activeItem)}
        onClose={() => setActiveItem(null)}
        item={activeItem}
        type={serviceId}
        onDownloadCoupon={onDownloadCoupon}
        isCouponDownloaded={
          activeItem && serviceId === 'coupons'
            ? downloadedCouponIds.includes(activeItem.id)
            : false
        }
        isSaved={activeItem ? savedItemIds.includes(activeItem.id) : false}
        onToggleSaved={() => {
          if (activeItem && onToggleSaved) onToggleSaved(activeItem, serviceId);
        }}
        isCouponUsed={activeItem ? usedCouponIds.includes(activeItem.id) : false}
        onUseCoupon={onUseCoupon}
      />

    </div>
  );
};
