import React, { useState, useMemo } from 'react';
import {
  SERVICES_META,
  MOCK_NEWS_LIST,
  MOCK_MARKETPLACE_LIST,
  MOCK_LIFE_INFO_LIST,
  MOCK_JOBS_LIST,
  MOCK_REAL_ESTATE_LIST,
  MOCK_CHATROOMS_LIST,
} from '../data/mockData';
import {
  ServiceId,
  NewsItem,
  MarketplaceItem,
  LifeInfoItem,
  JobItem,
  RealEstateItem,
  ChatroomItem,
} from '../types';
import { SectionHeader } from '../components/common/SectionHeader';
import { ServiceCard } from '../components/common/ServiceCard';
import { CategoryChip } from '../components/common/CategoryChip';
import { EmptyState } from '../components/common/EmptyState';
import {
  ChevronRight,
  MessageCircle,
  Users,
  Pencil,
  Eye,
} from 'lucide-react';
import { TodayInfo } from '../services/operationalData';

interface ResidentHomeScreenProps {
  onNavigateService: (serviceId: ServiceId) => void;
  onSelectNews: (item: NewsItem) => void;
  onSelectMarket: (item: MarketplaceItem) => void;
  onSelectLife: (item: LifeInfoItem) => void;
  onSelectJob: (item: JobItem) => void;
  onSelectRealEstate?: (item: RealEstateItem) => void;
  onSelectChat: (item: ChatroomItem) => void;
  onOpenEmergency: () => void;
  customShortcuts: ServiceId[];
  onOpenCustomize: () => void;
  todayInfo: TodayInfo | null;
}

type FeedFilterCategory = '전체' | '뉴스' | '생활정보' | '중고거래' | '구인구직' | '부동산';

export const ResidentHomeScreen: React.FC<ResidentHomeScreenProps> = ({
  onNavigateService,
  onSelectNews,
  onSelectMarket,
  onSelectLife,
  onSelectJob,
  onSelectRealEstate,
  onSelectChat,
  onOpenEmergency,
  customShortcuts,
  onOpenCustomize,
  todayInfo,
}) => {
  // Feed Category Filter state
  const [feedCategory, setFeedCategory] = useState<FeedFilterCategory>('전체');

  const personalizedServices = useMemo(() => {
    return customShortcuts
      .map((id) => SERVICES_META.find((service) => service.id === id))
      .filter((service): service is NonNullable<typeof service> => Boolean(service));
  }, [customShortcuts]);

  const recommendedResidentServices = useMemo(() => {
    return SERVICES_META.filter(
      (service) =>
        service.targetMode === 'resident' && !customShortcuts.includes(service.id)
    );
  }, [customShortcuts]);

  // 4 Tourist Services for secondary bridge access
  const touristServices = useMemo(() => {
    return SERVICES_META.filter(
      (service) =>
        service.targetMode === 'tourist' && !customShortcuts.includes(service.id)
    );
  }, [customShortcuts]);

  // Top Headline News for "오늘의 세부"
  const topNews = MOCK_NEWS_LIST.find((n) => n.isImportant) || MOCK_NEWS_LIST[0];

  // Combined Feed Items for "지금 세부에서는"
  const feedItems = useMemo(() => {
    const list: Array<{
      id: string;
      title: string;
      summary: string;
      categoryLabel: string;
      type: FeedFilterCategory;
      publishedAt: string;
      thumbnail?: string;
      stats?: { views?: number; likes?: number; comments?: number };
      rawItem: any;
      serviceId: ServiceId;
    }> = [];

    // News
    MOCK_NEWS_LIST.slice(0, 3).forEach((n) => {
      list.push({
        id: `feed-news-${n.id}`,
        title: n.title,
        summary: n.summary,
        categoryLabel: `뉴스 · ${n.category}`,
        type: '뉴스',
        publishedAt: n.publishedAt,
        thumbnail: n.imageUrl || n.thumbnail,
        stats: { views: n.readCount },
        rawItem: n,
        serviceId: 'news',
      });
    });

    // Life Info
    MOCK_LIFE_INFO_LIST.slice(0, 2).forEach((l) => {
      list.push({
        id: `feed-life-${l.id}`,
        title: l.title,
        summary: l.excerpt,
        categoryLabel: `생활 · ${l.category}`,
        type: '생활정보',
        publishedAt: l.date,
        stats: { views: l.views, likes: l.likes, comments: l.comments },
        rawItem: l,
        serviceId: 'life_info',
      });
    });

    // Marketplace
    MOCK_MARKETPLACE_LIST.slice(0, 2).forEach((m) => {
      list.push({
        id: `feed-market-${m.id}`,
        title: m.title,
        summary: `₱${m.pricePHP.toLocaleString()} (${m.location}) · ${m.status}`,
        categoryLabel: `중고 · ${m.category}`,
        type: '중고거래',
        publishedAt: m.postedAt,
        thumbnail: m.thumbnail,
        stats: { likes: m.likes, comments: m.chatCount },
        rawItem: m,
        serviceId: 'marketplace',
      });
    });

    // Jobs
    MOCK_JOBS_LIST.slice(0, 2).forEach((j) => {
      list.push({
        id: `feed-job-${j.id}`,
        title: j.title,
        summary: `${j.company} · ${j.salary} · ${j.location}`,
        categoryLabel: `구인구직 · ${j.jobType}`,
        type: '구인구직',
        publishedAt: j.postedAt,
        rawItem: j,
        serviceId: 'jobs',
      });
    });

    // Real Estate
    MOCK_REAL_ESTATE_LIST.slice(0, 2).forEach((r) => {
      list.push({
        id: `feed-re-${r.id}`,
        title: r.title,
        summary: `₱${r.pricePHP.toLocaleString()} · ${r.specs} · ${r.location}`,
        categoryLabel: `부동산 · ${r.propertyType}`,
        type: '부동산',
        publishedAt: r.postedAt,
        thumbnail: r.thumbnail,
        rawItem: r,
        serviceId: 'real_estate',
      });
    });

    // 전체 탭 첫 화면에서는 한 종류가 몰리지 않도록 최신 커뮤니티 유형을 섞어 노출합니다.
    return [
      list[0],
      list[3],
      list[5],
      list[7],
      list[9],
      list[1],
      list[4],
      list[6],
      list[8],
      list[10],
      list[2],
    ].filter(Boolean);
  }, []);

  // Filtered Feed Items
  const filteredFeed = useMemo(() => {
    if (feedCategory === '전체') return feedItems;
    return feedItems.filter((item) => item.type === feedCategory);
  }, [feedItems, feedCategory]);

  const handleFeedItemClick = (item: (typeof feedItems)[0]) => {
    switch (item.serviceId) {
      case 'news':
        onSelectNews(item.rawItem);
        break;
      case 'marketplace':
        onSelectMarket(item.rawItem);
        break;
      case 'life_info':
        onSelectLife(item.rawItem);
        break;
      case 'jobs':
        onSelectJob(item.rawItem);
        break;
      case 'real_estate':
        if (onSelectRealEstate) onSelectRealEstate(item.rawItem);
        else onNavigateService('real_estate');
        break;
      default:
        onNavigateService(item.serviceId);
    }
  };

  return (
    <div className="space-y-6 pb-24">
      {/* 1. 오늘의 세부 (Today's Cebu Section) */}
      <section className="bg-white rounded-2xl p-4 shadow-2xs ring-1 ring-[#E1ECF3]/80">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="w-2 h-2 rounded-full bg-[#079BE8]" />
            <h2 className="text-sm font-black text-[#183247] tracking-tight">
              오늘의 세부
            </h2>
          </div>
          <button
            type="button"
            onClick={onOpenEmergency}
            className="min-h-[36px] -mr-1 px-1 text-[11px] font-bold text-[#079BE8] flex items-center gap-0.5"
          >
            비상연락망
            <ChevronRight size={13} />
          </button>
        </div>

        {/* 실시간 생활 정보 */}
        <div className="grid grid-cols-2 gap-2 mt-3">
          <div
            className="min-w-0 rounded-xl bg-[#F1F9FE] px-3 py-2.5"
            title={todayInfo?.weather ? `출처: ${todayInfo.weather.source} · ${todayInfo.weather.updatedAt}` : undefined}
          >
            <p className="text-xs font-bold text-[#183247]">날씨 정보</p>
            {todayInfo?.weather ? (
              <>
                <p className="mt-1 truncate text-[11px] font-bold text-[#0879E7]">
                  {todayInfo.weather.region}{' '}
                  {todayInfo.weather.currentTemp !== null
                    ? `${todayInfo.weather.currentTemp}°`
                    : todayInfo.weather.summary}
                  {todayInfo.weather.currentTemp !== null && todayInfo.weather.summary
                    ? ` · ${todayInfo.weather.summary}`
                    : ''}
                </p>
                <p className="mt-0.5 truncate text-[9px] text-[#617789]">
                  {todayInfo.weather.minTemp !== null && todayInfo.weather.maxTemp !== null
                    ? `${todayInfo.weather.minTemp}°~${todayInfo.weather.maxTemp}° · `
                    : ''}
                  비 {todayInfo.weather.rainChance}%
                </p>
              </>
            ) : (
              <p className="mt-1 text-[10px] text-[#617789]">정보를 불러오는 중</p>
            )}
          </div>
          <div
            className="min-w-0 rounded-xl bg-[#F7FBFE] px-3 py-2.5"
            title={todayInfo?.exchange ? `기준환율 · 출처: ${todayInfo.exchange.source} · ${todayInfo.exchange.updatedAt}` : undefined}
          >
            <p className="text-xs font-bold text-[#183247]">환율 정보</p>
            {todayInfo?.exchange ? (
              <>
                <p className="mt-1 truncate text-[11px] font-bold text-[#0879E7]">
                  ₱1 = ₩{todayInfo.exchange.krwPerPhp.toLocaleString('ko-KR', {
                    maximumFractionDigits: 2,
                  })}
                </p>
                <p className="mt-0.5 truncate text-[9px] text-[#617789]">
                  ₩10,000 ≈ ₱
                  {todayInfo.exchange.phpPer10000Krw.toLocaleString('ko-KR', {
                    maximumFractionDigits: 0,
                  })}{' '}
                  · 기준환율
                </p>
              </>
            ) : (
              <p className="mt-1 text-[10px] text-[#617789]">정보를 불러오는 중</p>
            )}
          </div>
        </div>

        {/* Featured Main News */}
        {topNews && (
          <button
            type="button"
            onClick={() => onSelectNews(topNews)}
            className="w-full mt-3 py-3 border-y border-[#ECF2F6] group text-left"
          >
            <div className="flex gap-3">
              {topNews.thumbnail && (
                <div className="w-22 h-20 rounded-xl overflow-hidden bg-[#EAF8FF] shrink-0 border border-[#E1ECF3]">
                  <img
                    src={topNews.thumbnail}
                    alt={topNews.title}
                    referrerPolicy="no-referrer"
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                </div>
              )}
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1">
                  <span className="text-[10px] font-bold text-white bg-[#079BE8] px-1.5 py-0.2 rounded">
                    주요뉴스
                  </span>
                  <span className="text-[11px] text-[#8799A8]">
                    {topNews.publishedAt}
                  </span>
                </div>
                <h3 className="text-xs sm:text-sm font-bold text-[#183247] line-clamp-2 group-hover:text-[#079BE8] leading-snug">
                  {topNews.title}
                </h3>
                <p className="text-[11px] text-[#617789] line-clamp-1 mt-1">
                  {topNews.summary}
                </p>
              </div>
            </div>
          </button>
        )}
        {!topNews && (
          <div className="mt-3">
            <EmptyState
              title="등록된 주요 소식이 없습니다"
              description="운영 데이터가 등록되면 여기에 표시됩니다."
            />
          </div>
        )}
      </section>

      {/* 2. 개인화 바로가기 */}
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

        {recommendedResidentServices.length > 0 && (
          <div>
            <p className="mb-2 px-1 text-[11px] font-bold text-[#617789]">교민 추천 메뉴</p>
            <div className="grid grid-cols-3 gap-2">
              {recommendedResidentServices.map((service) => (
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

      {/* 3. 최신 교민 소식 피드 ("지금 세부에서는") */}
      <section>
        <SectionHeader
          title="지금 세부에서는"
          subtitle="교민 소식 및 커뮤니티 업데이트"
        />

        {/* Category Tabs */}
        <div className="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 mb-3">
          {(['전체', '뉴스', '생활정보', '중고거래', '구인구직', '부동산'] as FeedFilterCategory[]).map(
            (tab) => (
              <CategoryChip
                key={tab}
                label={tab}
                active={feedCategory === tab}
                onClick={() => setFeedCategory(tab)}
              />
            )
          )}
        </div>

        {/* Feed List Items */}
        {filteredFeed.length === 0 ? (
          <EmptyState
            title="등록된 소식이 없습니다"
            description="해당 카테고리의 새로운 글이 올라오면 여기에 표시됩니다."
          />
        ) : (
          <div className="bg-white rounded-2xl ring-1 ring-[#E1ECF3]/80 divide-y divide-[#ECF2F6] overflow-hidden">
            {filteredFeed.slice(0, 5).map((item) => (
              <button
                key={item.id}
                type="button"
                onClick={() => handleFeedItemClick(item)}
                className="w-full p-3.5 flex gap-3 text-left hover:bg-[#F7FBFE] transition-colors"
              >
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 mb-1">
                    <span className="text-[10px] font-extrabold text-[#079BE8]">
                      {item.categoryLabel}
                    </span>
                    <span className="text-[10px] text-[#8799A8]">{item.publishedAt}</span>
                  </div>
                  <h3 className="text-sm font-bold text-[#183247] leading-snug line-clamp-2">
                    {item.title}
                  </h3>
                  <p className="text-xs text-[#617789] mt-1 line-clamp-1">{item.summary}</p>
                  {(item.stats?.views !== undefined || item.stats?.comments !== undefined) && (
                    <div className="flex items-center gap-3 mt-2 text-[10px] text-[#8799A8]">
                      {item.stats?.views !== undefined && (
                        <span className="flex items-center gap-1">
                          <Eye size={11} />
                          {item.stats.views.toLocaleString()}
                        </span>
                      )}
                      {item.stats?.comments !== undefined && item.stats.comments > 0 && (
                        <span className="flex items-center gap-1">
                          <MessageCircle size={11} />
                          {item.stats.comments}
                        </span>
                      )}
                    </div>
                  )}
                </div>
                {item.thumbnail && (
                  <img
                    src={item.thumbnail}
                    alt=""
                    referrerPolicy="no-referrer"
                    className="w-20 h-20 rounded-xl object-cover bg-[#EAF8FF] shrink-0"
                  />
                )}
              </button>
            ))}
          </div>
        )}
      </section>

      {/* 4. 최근 올라온 중고거래 미리보기 */}
      <section>
        <SectionHeader
          title="최근 올라온 중고거래"
          subtitle="등록된 세부 지역 중고 매물"
          actionText="전체보기"
          onAction={() => onNavigateService('marketplace')}
        />
        {MOCK_MARKETPLACE_LIST.length === 0 ? (
          <EmptyState title="등록된 중고거래가 없습니다" description="새 매물이 등록되면 여기에 표시됩니다." />
        ) : (
          <div className="grid grid-cols-2 gap-2.5">
            {MOCK_MARKETPLACE_LIST.slice(0, 4).map((item) => (
            <button
              key={item.id}
              type="button"
              onClick={() => onSelectMarket(item)}
              className="bg-white border border-[#E1ECF3] rounded-2xl p-2.5 text-left group hover:border-[#079BE8] transition-colors flex flex-col justify-between"
            >
              <div>
                <div className="w-full h-28 rounded-xl overflow-hidden bg-[#EAF8FF] relative border border-[#E1ECF3] mb-2">
                  <img
                    src={item.thumbnail}
                    alt={item.title}
                    referrerPolicy="no-referrer"
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                  <span
                    className={`absolute top-1.5 left-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded ${
                      item.status === '판매중'
                        ? 'bg-[#075A9D] text-white'
                        : 'bg-[#8799A8] text-white'
                    }`}
                  >
                    {item.status}
                  </span>
                </div>
                <h3 className="text-xs font-bold text-[#183247] line-clamp-2 leading-snug">
                  {item.title}
                </h3>
              </div>

              <div className="mt-2 pt-1 border-t border-[#ECF2F6]">
                <div className="font-mono text-xs font-extrabold text-[#075A9D] tabular-nums">
                  ₱{item.pricePHP.toLocaleString()}
                </div>
                <div className="text-[10px] text-[#8799A8] truncate mt-0.5">
                  {item.location}
                </div>
              </div>
            </button>
            ))}
          </div>
        )}
      </section>

      {/* 5. 새로운 구인구직 미리보기 */}
      <section>
        <SectionHeader
          title="새로운 구인구직"
          subtitle="세부 현지 채용 및 한국어 가능자 포지션"
          actionText="전체보기"
          onAction={() => onNavigateService('jobs')}
        />
        {MOCK_JOBS_LIST.length === 0 ? (
          <EmptyState title="등록된 채용정보가 없습니다" description="새 채용공고가 등록되면 여기에 표시됩니다." />
        ) : (
          <div className="space-y-2.5">
            {MOCK_JOBS_LIST.slice(0, 3).map((job) => (
            <button
              key={job.id}
              type="button"
              onClick={() => onSelectJob(job)}
              className="w-full bg-white border border-[#E1ECF3] rounded-2xl p-3.5 hover:border-[#079BE8] transition-colors text-left"
            >
              <div className="flex items-start justify-between gap-2 mb-1">
                <span className="text-[11px] font-bold text-[#079BE8]">
                  {job.jobType}
                </span>
                <span className="text-[10px] text-[#8799A8] font-mono">
                  {job.postedAt}
                </span>
              </div>
              <h3 className="text-xs sm:text-sm font-bold text-[#183247] line-clamp-1">
                {job.title}
              </h3>
              <p className="text-xs text-[#617789] mt-0.5 font-medium">
                {job.company}
              </p>
              <div className="mt-2 pt-2 border-t border-[#ECF2F6] flex items-center justify-between text-[11px] text-[#8799A8]">
                <span>{job.location}</span>
                <span className="font-extrabold text-[#075A9D] font-mono">
                  {job.salary}
                </span>
              </div>
            </button>
            ))}
          </div>
        )}
      </section>

      {/* 6. 단톡방 미리보기 ("지금 이야기 중") */}
      <section>
        <SectionHeader
          title="지금 이야기 중"
          subtitle="등록된 교민 단톡방"
          actionText="전체보기"
          onAction={() => onNavigateService('chatrooms')}
        />
        {MOCK_CHATROOMS_LIST.length === 0 ? (
          <EmptyState title="등록된 단톡방이 없습니다" description="운영 중인 단톡방이 등록되면 여기에 표시됩니다." />
        ) : (
          <div className="space-y-2.5">
            {MOCK_CHATROOMS_LIST.map((chat) => (
            <button
              key={chat.id}
              type="button"
              onClick={() => onSelectChat(chat)}
              className="w-full bg-white border border-[#E1ECF3] rounded-2xl p-3.5 hover:border-[#079BE8] transition-colors text-left"
            >
              <div className="flex items-start justify-between gap-3">
                <div className="w-10 h-10 rounded-xl bg-[#EAF8FF] text-[#079BE8] flex items-center justify-center shrink-0">
                  <MessageCircle size={20} />
                </div>

                <div className="flex-1 min-w-0">
                  <div className="flex items-center justify-between gap-1 mb-0.5">
                    <div className="flex items-center gap-1.5 min-w-0">
                      <h3 className="text-xs sm:text-sm font-bold text-[#183247] truncate">
                        {chat.title}
                      </h3>
                      <span className="text-[10px] text-[#8799A8] flex items-center gap-0.5 font-mono">
                        <Users size={11} />
                        {chat.memberCount}
                      </span>
                    </div>

                    <span className="text-[10px] text-[#8799A8] shrink-0 font-mono">
                      {chat.lastMessageTime}
                    </span>
                  </div>

                  <p className="text-xs text-[#617789] line-clamp-1 leading-normal">
                    {chat.lastMessage || chat.description}
                  </p>
                </div>

                {chat.unreadCount !== undefined && chat.unreadCount > 0 && (
                  <div className="shrink-0 self-center">
                    <span className="w-5 h-5 rounded-full bg-[#0879E7] text-white text-[10px] font-black flex items-center justify-center">
                      {chat.unreadCount}
                    </span>
                  </div>
                )}
              </div>
            </button>
            ))}
          </div>
        )}
      </section>

      {/* 7. 관광객 서비스 연결 배너 */}
      <section className="bg-white border border-[#E1ECF3] rounded-2xl p-3.5">
        <div className="flex items-center justify-between mb-2">
          <span className="text-xs font-bold text-[#183247]">
            세부 여행·맛집·쿠폰도 필요하신가요?
          </span>
          <span className="text-[10px] text-[#8799A8]">관광객 서비스 바로가기</span>
        </div>
        <div className="grid grid-cols-4 gap-2">
          {touristServices.map((service) => (
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
