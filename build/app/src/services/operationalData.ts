import {
  MOCK_BUSINESSES_LIST,
  MOCK_CHATROOMS_LIST,
  MOCK_COUPONS_LIST,
  MOCK_EVENTS_LIST,
  MOCK_JOBS_LIST,
  MOCK_LIFE_INFO_LIST,
  MOCK_MARKETPLACE_LIST,
  MOCK_NEWS_LIST,
  MOCK_REAL_ESTATE_LIST,
  MOCK_RESTAURANTS_LIST,
} from '../data/mockData';
import { ServiceId } from '../types';

const API_URL =
  'https://thecebu.co.kr/api/eottae.php?action=app_services&limit=50';

const collections: Record<ServiceId, any[]> = {
  news: MOCK_NEWS_LIST,
  life_info: MOCK_LIFE_INFO_LIST,
  marketplace: MOCK_MARKETPLACE_LIST,
  jobs: MOCK_JOBS_LIST,
  real_estate: MOCK_REAL_ESTATE_LIST,
  chatrooms: MOCK_CHATROOMS_LIST,
  restaurants: MOCK_RESTAURANTS_LIST,
  businesses: MOCK_BUSINESSES_LIST,
  events: MOCK_EVENTS_LIST,
  coupons: MOCK_COUPONS_LIST,
};

export interface OperationalAuth {
  is_member: boolean;
  nick?: string;
  initial?: string;
  member_type?: string;
  point?: number;
  coupon_count?: number;
  login_url?: string;
  register_url?: string;
  mypage_url?: string;
  logout_url?: string;
}
export interface TodayInfo {
  weather: {
    available: boolean;
    region: string;
    summary: string;
    currentTemp: number | null;
    minTemp: number | null;
    maxTemp: number | null;
    rainChance: number;
    updatedAt: string;
    source: string;
  } | null;
  exchange: {
    available: boolean;
    base: 'PHP';
    quote: 'KRW';
    krwPerPhp: number;
    phpPer10000Krw: number;
    updatedAt: string;
    source: string;
  } | null;
  cachedAt?: string;
}

export interface OperationalDataResult {
  auth: OperationalAuth;
  fetchedAt: string;
  todayInfo: TodayInfo | null;
}

const replaceCollection = (target: any[], incoming: unknown) => {
  target.splice(0, target.length, ...(Array.isArray(incoming) ? incoming : []));
};

export async function loadOperationalData(
  signal?: AbortSignal
): Promise<OperationalDataResult> {
  const response = await fetch(API_URL, {
    method: 'GET',
    // The packaged Capacitor app has a capacitor://localhost origin.
    // This endpoint is public and responds with Access-Control-Allow-Origin: *,
    // so credentials must be omitted for standards-compliant CORS handling.
    credentials: 'omit',
    cache: 'no-store',
    headers: { Accept: 'application/json' },
    signal,
  });

  if (!response.ok) {
    throw new Error(`운영 데이터 요청 실패 (${response.status})`);
  }

  const payload = await response.json();
  if (!payload?.success || !payload?.data) {
    throw new Error(payload?.message || '운영 데이터 형식이 올바르지 않습니다.');
  }

  (Object.keys(collections) as ServiceId[]).forEach((serviceId) => {
    replaceCollection(collections[serviceId], payload.data[serviceId]);
  });

  return {
    auth: payload.data.auth || { is_member: false },
    fetchedAt: String(payload.data.fetchedAt || ''),
    todayInfo: payload.data.todayInfo || null,
  };
}
