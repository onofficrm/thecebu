import React, { useState } from 'react';
import { SearchBar } from '../components/common/SearchBar';
import { CategoryChip } from '../components/common/CategoryChip';
import { ContentCard } from '../components/common/ContentCard';
import { EmptyState } from '../components/common/EmptyState';
import {
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
import { AppMode } from '../types';
import { Clock, X } from 'lucide-react';

interface UniversalSearchScreenProps {
  onSelectItem: (item: any, type: string) => void;
  initialSearchTerm?: string;
  currentMode?: AppMode;
}

export const UniversalSearchScreen: React.FC<UniversalSearchScreenProps> = ({
  onSelectItem,
  initialSearchTerm = '',
  currentMode = 'resident',
}) => {
  const [searchTerm, setSearchTerm] = useState(initialSearchTerm);
  const [selectedFilter, setSelectedFilter] = useState<string>('all');
  const [searchScope, setSearchScope] = useState<'mode' | 'all'>('mode');
  const [recentSearches, setRecentSearches] = useState<string[]>([]);

  const handleSearchSubmit = (term: string) => {
    if (!term) return;
    setSearchTerm(term);
    if (!recentSearches.includes(term)) {
      setRecentSearches([term, ...recentSearches.slice(0, 4)]);
    }
  };

  const removeRecent = (termToRemove: string) => {
    setRecentSearches(recentSearches.filter((t) => t !== termToRemove));
  };

  const clearAllRecent = () => {
    setRecentSearches([]);
  };

  // Perform search across mock datasets
  const lowerQuery = searchTerm.toLowerCase().trim();

  const newsResults = MOCK_NEWS_LIST.filter(
    (n) => n.title.toLowerCase().includes(lowerQuery) || n.summary.toLowerCase().includes(lowerQuery)
  );
  const marketResults = MOCK_MARKETPLACE_LIST.filter(
    (m) => m.title.toLowerCase().includes(lowerQuery) || m.location.toLowerCase().includes(lowerQuery)
  );
  const restResults = MOCK_RESTAURANTS_LIST.filter(
    (r) =>
      r.name.toLowerCase().includes(lowerQuery) ||
      r.area.toLowerCase().includes(lowerQuery) ||
      r.category.toLowerCase().includes(lowerQuery) ||
      r.signatureMenu.toLowerCase().includes(lowerQuery)
  );
  const bizResults = MOCK_BUSINESSES_LIST.filter(
    (b) =>
      b.name.toLowerCase().includes(lowerQuery) ||
      b.area.toLowerCase().includes(lowerQuery) ||
      b.category.toLowerCase().includes(lowerQuery) ||
      b.perk.toLowerCase().includes(lowerQuery)
  );
  const lifeResults = MOCK_LIFE_INFO_LIST.filter(
    (l) => l.title.toLowerCase().includes(lowerQuery) || l.excerpt.toLowerCase().includes(lowerQuery)
  );
  const jobsResults = MOCK_JOBS_LIST.filter(
    (j) => j.title.toLowerCase().includes(lowerQuery) || j.company.toLowerCase().includes(lowerQuery) || j.location.toLowerCase().includes(lowerQuery)
  );
  const realEstateResults = MOCK_REAL_ESTATE_LIST.filter(
    (r) => r.title.toLowerCase().includes(lowerQuery) || r.location.toLowerCase().includes(lowerQuery) || r.specs.toLowerCase().includes(lowerQuery)
  );
  const chatResults = MOCK_CHATROOMS_LIST.filter(
    (item) =>
      item.title.toLowerCase().includes(lowerQuery) ||
      item.description.toLowerCase().includes(lowerQuery)
  );
  const eventResults = MOCK_EVENTS_LIST.filter(
    (item) =>
      item.title.toLowerCase().includes(lowerQuery) ||
      item.summary.toLowerCase().includes(lowerQuery)
  );
  const couponResults = MOCK_COUPONS_LIST.filter(
    (item) =>
      item.title.toLowerCase().includes(lowerQuery) ||
      item.storeName.toLowerCase().includes(lowerQuery)
  );

  const residentScope = ['news', 'market', 'life', 'jobs', 'real_estate', 'chat'];
  const touristScope = ['dining', 'tour', 'events', 'coupons'];
  const scopeAllows = (filter: string) =>
    searchScope === 'all' ||
    (currentMode === 'resident' ? residentScope : touristScope).includes(filter);

  const totalResultsCount =
    (scopeAllows('news') && (selectedFilter === 'all' || selectedFilter === 'news') ? newsResults.length : 0) +
    (scopeAllows('market') && (selectedFilter === 'all' || selectedFilter === 'market') ? marketResults.length : 0) +
    (scopeAllows('dining') && (selectedFilter === 'all' || selectedFilter === 'dining') ? restResults.length : 0) +
    (scopeAllows('tour') && (selectedFilter === 'all' || selectedFilter === 'tour') ? bizResults.length : 0) +
    (scopeAllows('life') && (selectedFilter === 'all' || selectedFilter === 'life') ? lifeResults.length : 0) +
    (scopeAllows('jobs') && (selectedFilter === 'all' || selectedFilter === 'jobs') ? jobsResults.length : 0) +
    (scopeAllows('real_estate') && (selectedFilter === 'all' || selectedFilter === 'real_estate') ? realEstateResults.length : 0) +
    (scopeAllows('chat') && (selectedFilter === 'all' || selectedFilter === 'chat') ? chatResults.length : 0) +
    (scopeAllows('events') && (selectedFilter === 'all' || selectedFilter === 'events') ? eventResults.length : 0) +
    (scopeAllows('coupons') && (selectedFilter === 'all' || selectedFilter === 'coupons') ? couponResults.length : 0);

  const isSearching = searchTerm.trim().length > 0;

  return (
    <div className="space-y-4 pb-24">
      {/* Search Input Bar */}
      <SearchBar
        value={searchTerm}
        onChange={setSearchTerm}
        onSearch={handleSearchSubmit}
        autoFocus={false}
      />

      <div className="grid grid-cols-2 rounded-xl border border-[#E1ECF3] bg-[#F1F9FE] p-1">
        <button
          type="button"
          onClick={() => {
            setSearchScope('mode');
            setSelectedFilter('all');
          }}
          className={`min-h-[38px] rounded-lg text-xs font-bold ${
            searchScope === 'mode'
              ? 'bg-white text-[#075A9D] shadow-xs'
              : 'text-[#617789]'
          }`}
        >
          {currentMode === 'resident' ? '교민 서비스 우선' : '여행 서비스 우선'}
        </button>
        <button
          type="button"
          onClick={() => {
            setSearchScope('all');
            setSelectedFilter('all');
          }}
          className={`min-h-[38px] rounded-lg text-xs font-bold ${
            searchScope === 'all'
              ? 'bg-white text-[#075A9D] shadow-xs'
              : 'text-[#617789]'
          }`}
        >
          전체 10개 서비스
        </button>
      </div>

      {/* Filter Chips Bar */}
      <div className="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
        <CategoryChip
          label="전체"
          active={selectedFilter === 'all'}
          onClick={() => setSelectedFilter('all')}
        />
        <CategoryChip
          label="뉴스"
          active={selectedFilter === 'news'}
          onClick={() => setSelectedFilter('news')}
          count={isSearching ? newsResults.length : undefined}
        />
        <CategoryChip
          label="중고거래"
          active={selectedFilter === 'market'}
          onClick={() => setSelectedFilter('market')}
          count={isSearching ? marketResults.length : undefined}
        />
        <CategoryChip
          label="맛집"
          active={selectedFilter === 'dining'}
          onClick={() => setSelectedFilter('dining')}
          count={isSearching ? restResults.length : undefined}
        />
        <CategoryChip
          label="스파·투어"
          active={selectedFilter === 'tour'}
          onClick={() => setSelectedFilter('tour')}
          count={isSearching ? bizResults.length : undefined}
        />
        <CategoryChip
          label="생활정보"
          active={selectedFilter === 'life'}
          onClick={() => setSelectedFilter('life')}
          count={isSearching ? lifeResults.length : undefined}
        />
        <CategoryChip
          label="구인구직"
          active={selectedFilter === 'jobs'}
          onClick={() => setSelectedFilter('jobs')}
          count={isSearching ? jobsResults.length : undefined}
        />
        <CategoryChip
          label="부동산"
          active={selectedFilter === 'real_estate'}
          onClick={() => setSelectedFilter('real_estate')}
          count={isSearching ? realEstateResults.length : undefined}
        />
        <CategoryChip
          label="단톡방"
          active={selectedFilter === 'chat'}
          onClick={() => setSelectedFilter('chat')}
          count={isSearching ? chatResults.length : undefined}
        />
        <CategoryChip
          label="이벤트"
          active={selectedFilter === 'events'}
          onClick={() => setSelectedFilter('events')}
          count={isSearching ? eventResults.length : undefined}
        />
        <CategoryChip
          label="쿠폰"
          active={selectedFilter === 'coupons'}
          onClick={() => setSelectedFilter('coupons')}
          count={isSearching ? couponResults.length : undefined}
        />
      </div>

      {/* Pre-Search State */}
      {!isSearching && (
        <div className="space-y-4 pt-1">
          {/* Recent Searches */}
          {recentSearches.length > 0 && (
            <div className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-bold text-[#183247] flex items-center gap-1.5">
                  <Clock size={14} className="text-[#079BE8]" />
                  <span>최근 검색어</span>
                </span>
                <button
                  type="button"
                  onClick={clearAllRecent}
                  className="text-[11px] text-[#8799A8] hover:text-[#183247]"
                >
                  모두 지우기
                </button>
              </div>
              <div className="flex flex-wrap gap-2">
                {recentSearches.map((term) => (
                  <div
                    key={term}
                    className="inline-flex items-center gap-1 bg-[#EAF8FF] border border-[#B9E5FC] text-[#075A9D] text-xs font-medium px-2.5 py-1.5 rounded-lg"
                  >
                    <span
                      onClick={() => handleSearchSubmit(term)}
                      className="cursor-pointer hover:underline"
                    >
                      {term}
                    </span>
                    <button
                      type="button"
                      onClick={() => removeRecent(term)}
                      className="text-[#079BE8] hover:text-[#075A9D] ml-1 p-0.5"
                    >
                      <X size={12} />
                    </button>
                  </div>
                ))}
              </div>
            </div>
          )}

          {recentSearches.length === 0 && (
            <EmptyState
              title="검색어를 입력해주세요"
              description="등록된 전체 서비스 데이터를 한 번에 검색할 수 있습니다."
            />
          )}
        </div>
      )}

      {/* Search Results Display */}
      {isSearching && (
        <div className="space-y-4">
          <div className="flex items-center justify-between px-1">
            <span className="text-xs text-[#617789]">
              &apos;{searchTerm}&apos; 검색 결과 ({totalResultsCount}건)
            </span>
          </div>

          {totalResultsCount === 0 ? (
            <EmptyState
              title="검색 결과가 없습니다"
              description="단어의 철자가 맞는지 확인하거나, 다른 검색어로 다시 찾아보세요."
              actionText="검색어 지우기"
              onAction={() => setSearchTerm('')}
            />
          ) : (
            <div className="space-y-2.5">
              {/* News */}
              {scopeAllows('news') && (selectedFilter === 'all' || selectedFilter === 'news') &&
                newsResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.title}
                    subtitle={item.summary}
                    category="뉴스"
                    metadata={[{ value: item.source }, { value: item.publishedAt }]}
                    onClick={() => onSelectItem(item, 'news')}
                  />
                ))}

              {/* Marketplace */}
              {scopeAllows('market') && (selectedFilter === 'all' || selectedFilter === 'market') &&
                marketResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.title}
                    category="중고거래"
                    thumbnail={item.thumbnail}
                    price={{ php: item.pricePHP, krw: item.priceKRW }}
                    metadata={[{ value: item.location }, { value: item.postedAt }]}
                    onClick={() => onSelectItem(item, 'marketplace')}
                  />
                ))}

              {/* Restaurants */}
              {scopeAllows('dining') && (selectedFilter === 'all' || selectedFilter === 'dining') &&
                restResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.name}
                    subtitle={item.signatureMenu}
                    category="맛집"
                    thumbnail={item.thumbnail}
                    metadata={[{ value: item.area }]}
                    onClick={() => onSelectItem(item, 'restaurants')}
                  />
                ))}

              {/* Businesses */}
              {scopeAllows('tour') && (selectedFilter === 'all' || selectedFilter === 'tour') &&
                bizResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.name}
                    subtitle={item.perk}
                    category="스파·투어"
                    thumbnail={item.thumbnail}
                    metadata={[{ value: item.area }]}
                    onClick={() => onSelectItem(item, 'businesses')}
                  />
                ))}

              {/* Life Info */}
              {scopeAllows('life') && (selectedFilter === 'all' || selectedFilter === 'life') &&
                lifeResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.title}
                    subtitle={item.excerpt}
                    category="생활정보"
                    metadata={[{ value: item.author }, { value: item.date }]}
                    onClick={() => onSelectItem(item, 'life_info')}
                  />
                ))}

              {/* Jobs */}
              {scopeAllows('jobs') && (selectedFilter === 'all' || selectedFilter === 'jobs') &&
                jobsResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.title}
                    subtitle={`${item.company} · ${item.salary}`}
                    category="구인구직"
                    metadata={[{ value: item.location }, { value: item.jobType }, { value: item.postedAt }]}
                    onClick={() => onSelectItem(item, 'jobs')}
                  />
                ))}

              {/* Real Estate */}
              {scopeAllows('real_estate') && (selectedFilter === 'all' || selectedFilter === 'real_estate') &&
                realEstateResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.title}
                    subtitle={item.specs}
                    category="부동산"
                    thumbnail={item.thumbnail}
                    price={{ php: item.pricePHP }}
                    metadata={[{ value: item.location }, { value: item.propertyType }, { value: item.postedAt }]}
                    onClick={() => onSelectItem(item, 'real_estate')}
                  />
                ))}

              {scopeAllows('chat') && (selectedFilter === 'all' || selectedFilter === 'chat') &&
                chatResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.title}
                    subtitle={item.description}
                    category="단톡방"
                    metadata={[{ value: `${item.memberCount}명 참여` }, { value: item.category }]}
                    onClick={() => onSelectItem(item, 'chatrooms')}
                  />
                ))}

              {scopeAllows('events') && (selectedFilter === 'all' || selectedFilter === 'events') &&
                eventResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.title}
                    subtitle={item.summary}
                    category={`이벤트 · ${item.status}`}
                    metadata={[{ value: item.period }, { value: item.location }]}
                    onClick={() => onSelectItem(item, 'events')}
                  />
                ))}

              {scopeAllows('coupons') && (selectedFilter === 'all' || selectedFilter === 'coupons') &&
                couponResults.map((item) => (
                  <ContentCard
                    key={item.id}
                    title={item.title}
                    subtitle={`${item.storeName} · ${item.discount}`}
                    category="쿠폰"
                    metadata={[{ value: item.validUntil }, { value: item.conditions }]}
                    onClick={() => onSelectItem(item, 'coupons')}
                  />
                ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
};
