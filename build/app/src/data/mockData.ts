import {
  ServiceId,
  ServiceMeta,
  NewsItem,
  LifeInfoItem,
  MarketplaceItem,
  JobItem,
  RealEstateItem,
  ChatroomItem,
  RestaurantItem,
  BusinessItem,
  EventItem,
  CouponItem,
  NotificationItem,
  HomeSectionConfig,
} from '../types';

/**
 * 10대 핵심 서비스 메타데이터 정의
 * 교민/관광객 모두 접근 가능하며, 모드에 따라 우선 노출도가 조정됩니다.
 */
export const SERVICES_META: ServiceMeta[] = [
  // 교민 6대 서비스
  {
    id: 'news',
    name: '필리핀뉴스',
    enName: 'News',
    category: 'life',
    targetMode: 'resident',
    icon: 'Newspaper',
    description: '필리핀 및 세부 주요 소식',
    color: '#075A9D',
  },
  {
    id: 'life_info',
    name: '생활정보',
    enName: 'Life Info',
    category: 'life',
    targetMode: 'resident',
    icon: 'BookOpen',
    description: '비자, 통신사, 병원, 국제학교 등 세부 정착 가이드',
    color: '#079BE8',
  },
  {
    id: 'marketplace',
    name: '중고거래',
    enName: 'Market',
    category: 'community',
    targetMode: 'resident',
    icon: 'ShoppingBag',
    description: '세부 지역 중고물품 직거래',
    color: '#00A8CC',
  },
  {
    id: 'jobs',
    name: '구인구직',
    enName: 'Jobs',
    category: 'community',
    targetMode: 'resident',
    icon: 'Briefcase',
    description: '세부 지역 채용정보',
    color: '#0879E7',
  },
  {
    id: 'real_estate',
    name: '부동산',
    enName: 'Real Estate',
    category: 'life',
    targetMode: 'resident',
    icon: 'Home',
    description: '세부 지역 임대·매매 정보',
    color: '#0284C7',
  },
  {
    id: 'chatrooms',
    name: '단톡방',
    enName: 'Chat',
    category: 'community',
    targetMode: 'resident',
    icon: 'MessageCircle',
    description: '등록된 교민 단톡방',
    color: '#075A9D',
  },
  // 관광객 4대 서비스
  {
    id: 'restaurants',
    name: '맛집찾기',
    enName: 'Dining',
    category: 'tour',
    targetMode: 'tourist',
    icon: 'Utensils',
    description: '카테고리와 지역별 맛집 정보',
    color: '#0879E7',
  },
  {
    id: 'businesses',
    name: '업체찾기',
    enName: 'Tours & Spa',
    category: 'tour',
    targetMode: 'tourist',
    icon: 'Compass',
    description: '업종과 지역별 등록 업체',
    color: '#00C5E8',
  },
  {
    id: 'events',
    name: '이벤트',
    enName: 'Events',
    category: 'benefit',
    targetMode: 'tourist',
    icon: 'Sparkles',
    description: '등록된 이벤트 및 프로모션',
    color: '#079BE8',
  },
  {
    id: 'coupons',
    name: '쿠폰사용',
    enName: 'Coupons',
    category: 'benefit',
    targetMode: 'tourist',
    icon: 'Ticket',
    description: '등록된 업체 쿠폰',
    color: '#00B4D8',
  },
];
export const MOCK_NEWS_LIST: NewsItem[] = [];

export const MOCK_LIFE_INFO_LIST: LifeInfoItem[] = [];

export const MOCK_MARKETPLACE_LIST: MarketplaceItem[] = [];

export const MOCK_JOBS_LIST: JobItem[] = [];

export const MOCK_REAL_ESTATE_LIST: RealEstateItem[] = [];

export const RESIDENT_SHORTCUT_SERVICES: ServiceId[] = [
  'news',
  'life_info',
  'marketplace',
  'jobs',
  'real_estate',
  'chatrooms',
];

export const MOCK_CHATROOMS_LIST: ChatroomItem[] = [];

export const MOCK_RESTAURANTS_LIST: RestaurantItem[] = [];

export const MOCK_BUSINESSES_LIST: BusinessItem[] = [];

export const MOCK_EVENTS_LIST: EventItem[] = [];

export const MOCK_COUPONS_LIST: CouponItem[] = [];

export const MOCK_NOTIFICATIONS_LIST: NotificationItem[] = [];

export const INITIAL_HOME_SECTIONS: HomeSectionConfig[] = [
  { id: 'quick_summary', name: '세부 환율·날씨 영역', enabled: true, mode: 'resident' },
  { id: 'primary_services', name: '핵심 서비스 바로가기 (6종)', enabled: true, mode: 'resident' },
  { id: 'news_section', name: '필리핀 최신 속보 & 뉴스', enabled: true, mode: 'resident' },
  { id: 'marketplace_section', name: '방금 올라온 중고거래', enabled: true, mode: 'resident' },
  { id: 'chatrooms_section', name: '활성 교민 단톡방', enabled: true, mode: 'resident' },
  { id: 'life_section', name: '세부 생활정보 & 꿀팁', enabled: true, mode: 'resident' },
  { id: 'jobs_section', name: '세부 구인구직 픽', enabled: true, mode: 'resident' },

  { id: 'tourist_quick_summary', name: '여행 헬퍼 (페소 계산기 & 날씨)', enabled: true, mode: 'tourist' },
  { id: 'tourist_primary_services', name: '여행 핵심 바로가기 (4종)', enabled: true, mode: 'tourist' },
  { id: 'coupons_section', name: '세부어때 단독 할인 쿠폰', enabled: true, mode: 'tourist' },
  { id: 'restaurants_section', name: '등록 맛집', enabled: true, mode: 'tourist' },
  { id: 'businesses_section', name: '인기 마사지 & 해양레저 업체', enabled: true, mode: 'tourist' },
  { id: 'events_section', name: '진행 중인 이벤트 & 프로모션', enabled: true, mode: 'tourist' },
];
