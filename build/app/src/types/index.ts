export type AppMode = 'resident' | 'tourist';

export type MainTab =
  | 'home'
  | 'news'
  | 'chatrooms'
  | 'services'
  | 'mypage'
  | 'search'
  | 'benefits';

export type ServiceId =
  // Resident primary (6)
  | 'news'
  | 'life_info'
  | 'marketplace'
  | 'jobs'
  | 'real_estate'
  | 'chatrooms'
  // Tourist primary (4)
  | 'restaurants'
  | 'businesses'
  | 'events'
  | 'coupons';

export interface ServiceMeta {
  id: ServiceId;
  name: string;
  enName: string;
  category: 'life' | 'community' | 'tour' | 'benefit';
  targetMode: 'resident' | 'tourist' | 'both';
  icon: string;
  badge?: string;
  description: string;
  color: string;
}

export interface TranslationMeta {
  boTable: string;
  wrId: number;
  token: string;
  endpoint?: string;
  sourceLanguage?: string;
}

export interface TranslatableContent {
  translation?: TranslationMeta;
  boTable?: string;
  wrId?: number;
  translationToken?: string;
  translationEndpoint?: string;
  sourceLanguage?: string;
  operationalUrl?: string;
  internalMapUrl?: string;
}

export interface NewsItem extends TranslatableContent {
  id: string;
  title: string;
  source: string;
  publishedAt: string;
  summary: string;
  category: string;
  readCount: number;
  imageUrl?: string;
  thumbnail?: string;
  isImportant?: boolean;
  content?: string;
  sourceUrl?: string;
  author?: string;
}

export interface LifeInfoItem extends TranslatableContent {
  id: string;
  title: string;
  category: string;
  author: string;
  date: string;
  views: number;
  likes: number;
  comments: number;
  excerpt: string;
  content?: string;
  source?: string;
  verifiedDate?: string;
  mapQuery?: string;
}

export interface MarketplaceItem extends TranslatableContent {
  id: string;
  title: string;
  pricePHP: number;
  priceKRW: number;
  location: string;
  status: '판매중' | '예약중' | '거래완료';
  postedAt: string;
  category: string;
  seller: string;
  likes: number;
  chatCount: number;
  thumbnail: string;
  description?: string;
}

export interface JobItem extends TranslatableContent {
  id: string;
  title: string;
  company: string;
  salary: string;
  location: string;
  jobType: string;
  postedAt: string;
  deadline: string;
  contact: string;
  description?: string;
  requirements?: string;
  workingHours?: string;
}

export interface RealEstateItem extends TranslatableContent {
  id: string;
  title: string;
  propertyType: '콘도' | '빌라/하우스' | '원룸' | '상가/오피스';
  priceType: '월세' | '매매' | '단기임대';
  pricePHP: number;
  location: string;
  specs: string;
  postedAt: string;
  furnished: boolean;
  thumbnail: string;
  description?: string;
  deposit?: string;
  contact?: string;
}

export interface ChatroomItem extends TranslatableContent {
  id: string;
  title: string;
  category: string;
  memberCount: number;
  maxMembers: number;
  description: string;
  tags: string[];
  isOfficial: boolean;
  linkUrl: string;
  lastMessage?: string;
  lastMessageTime?: string;
  unreadCount?: number;
}

export interface RestaurantItem extends TranslatableContent {
  id: string;
  name: string;
  category: string;
  area: string;
  rating: number;
  reviewCount: number;
  signatureMenu: string;
  discountRate?: number;
  couponAvailable: boolean;
  openHours: string;
  priceRange: string;
  thumbnail: string;
  phone: string;
  kakaoId?: string;
  mapQuery?: string;
  lat?: number | string;
  lng?: number | string;
}

export interface BusinessItem extends TranslatableContent {
  id: string;
  name: string;
  category: string;
  area: string;
  rating: number;
  reviewCount: number;
  perk: string;
  openHours: string;
  thumbnail: string;
  kakaoId: string;
  phone: string;
  mapQuery?: string;
  lat?: number | string;
  lng?: number | string;
}

export interface EventItem extends TranslatableContent {
  id: string;
  title: string;
  period: string;
  location: string;
  host: string;
  status: '진행중' | '마감임박' | '예정';
  summary: string;
  badge: string;
}

export interface CouponItem extends TranslatableContent {
  id: string;
  storeName: string;
  title: string;
  discount: string;
  validUntil: string;
  category: string;
  conditions: string;
  downloadCount: number;
  isDownloaded?: boolean;
}

export interface NotificationItem {
  id: string;
  title: string;
  content: string;
  timeAgo: string;
  category: '공지' | '쿠폰' | '새글' | '알림';
  isRead: boolean;
  targetMode?: AppMode | 'all';
}

export interface HomeSectionConfig {
  id: string;
  name: string;
  enabled: boolean;
  mode: AppMode;
}
