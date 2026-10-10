import React, { useEffect, useState } from 'react';
import {
  Share2,
  Heart,
  Phone,
  MessageCircle,
  MapPin,
  Clock,
  Download,
  Check,
  Bell,
  BellOff,
  Send,
  ShieldAlert,
  Map as MapIcon,
} from 'lucide-react';
import { BottomSheet } from './BottomSheet';
import { AppMap } from './AppMap';
import {
  AppliedTranslation,
  PostTranslationPanel,
} from './PostTranslationPanel';
import { formatNumericText } from '../../utils/format';

interface DetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  item: any;
  type: string;
  onDownloadCoupon?: (coupon: any) => void;
  isCouponDownloaded?: boolean;
  isSaved?: boolean;
  onToggleSaved?: () => void;
  isCouponUsed?: boolean;
  onUseCoupon?: (coupon: any) => void;
}

const contentParagraphs = (value: string) => {
  const normalized = formatNumericText(value)
    .replace(/\r/g, '')
    .replace(/\s*([•▪︎■◆✔✅☑️➡️‼️※])\s*/g, '\n$1 ')
    .replace(/\n{3,}/g, '\n\n')
    .trim();
  const lines = normalized.split(/\n+/).map((line) => line.trim()).filter(Boolean);
  if (lines.length > 1) return lines;

  const sentences = normalized.match(/[^.!?。]+[.!?。]?/g)?.map((part) => part.trim()).filter(Boolean) || [];
  if (sentences.length < 2) return normalized ? [normalized] : [];

  const paragraphs: string[] = [];
  let current = '';
  sentences.forEach((sentence) => {
    if (current && `${current} ${sentence}`.length > 170) {
      paragraphs.push(current);
      current = sentence;
    } else {
      current = current ? `${current} ${sentence}` : sentence;
    }
  });
  if (current) paragraphs.push(current);
  return paragraphs;
};

export const DetailModal: React.FC<DetailModalProps> = ({
  isOpen,
  onClose,
  item,
  type,
  onDownloadCoupon,
  isCouponDownloaded = false,
  isSaved = false,
  onToggleSaved,
  isCouponUsed = false,
  onUseCoupon,
}) => {
  const [localSaved, setLocalSaved] = useState(false);
  const [copied, setCopied] = useState(false);
  const [actionNotice, setActionNotice] = useState<string | null>(null);
  const [chatInput, setChatInput] = useState('');
  const [localMessages, setLocalMessages] = useState<Record<string, string[]>>({});
  const [chatNotifications, setChatNotifications] = useState(true);
  const [appliedTranslation, setAppliedTranslation] = useState<AppliedTranslation | null>(null);
  const [showMap, setShowMap] = useState(false);

  useEffect(() => {
    setAppliedTranslation(null);
    setShowMap(false);
  }, [item?.id, type]);

  if (!item) return null;
  const saved = onToggleSaved ? isSaved : localSaved;
  const chatMessages = localMessages[item.id] || [];
  const displayTitle = appliedTranslation?.title || item.title || item.name;
  const displayContent =
    appliedTranslation?.content ||
    item.content ||
    item.summary ||
    item.excerpt ||
    item.description ||
    '';
  const readableContent = contentParagraphs(displayContent);
  const kakaoId = String(item.kakaoId || '').trim();
  const phone = String(item.phone || (type === 'real_estate' ? item.contact : '') || '').trim();
  const dialPhoneCandidate = phone.replace(/[^0-9+]/g, '');
  const dialPhone = dialPhoneCandidate.replace(/\D/g, '').length >= 7 ? dialPhoneCandidate : '';

  const triggerNotice = (msg: string) => {
    setActionNotice(msg);
    setTimeout(() => setActionNotice(null), 3000);
  };

  const handleShare = () => {
    try {
      const shareUrl = `${window.location.origin}${window.location.pathname}#service=${type}&id=${item.id}`;
      navigator.clipboard.writeText(shareUrl);
      setCopied(true);
      triggerNotice('링크가 클립보드에 복사되었습니다.');
      setTimeout(() => setCopied(false), 2000);
    } catch {
      setCopied(true);
      triggerNotice('공유 링크가 복사되었습니다.');
      setTimeout(() => setCopied(false), 2000);
    }
  };

  return (
    <BottomSheet isOpen={isOpen} onClose={onClose} title={item.title || item.name || '상세 정보'}>
      <div className="space-y-4">
        {/* Hero image if exists */}
        {item.thumbnail && (
          <div className="w-full h-44 rounded-2xl overflow-hidden bg-[#EAF8FF] relative border border-[#E1ECF3]">
            <img
              src={item.thumbnail}
              alt={item.title || item.name}
              referrerPolicy="no-referrer"
              className="w-full h-full object-cover"
            />
            {item.category && (
              <span className="absolute top-2 left-2 bg-[#075A9D]/90 text-white text-[10px] font-bold px-2 py-0.5 rounded">
                {item.category}
              </span>
            )}
          </div>
        )}

        {/* Title and subtitle */}
        <div>
          <div className="flex items-center gap-2 mb-1">
            {item.status && (
              <span
                className={`text-[10px] font-bold px-2 py-0.5 rounded ${
                  item.status === '판매중' || item.status === '진행중'
                    ? 'bg-[#EAF8FF] text-[#075A9D] border border-[#B9E5FC]'
                    : item.status === '예약중' || item.status === '마감임박'
                    ? 'bg-[#F1F9FE] text-[#0879E7] border border-[#C7E9FB]'
                    : 'bg-[#F1F9FE] text-[#8799A8] border border-[#E1ECF3]'
                }`}
              >
                {item.status}
              </span>
            )}
            {item.jobType && (
              <span className="text-[10px] font-bold bg-[#EAF8FF] text-[#075A9D] px-2 py-0.5 rounded border border-[#B9E5FC]">
                {item.jobType}
              </span>
            )}
            {item.propertyType && (
              <span className="text-[10px] font-bold bg-[#EAF8FF] text-[#075A9D] px-2 py-0.5 rounded border border-[#B9E5FC]">
                {item.propertyType} · {item.priceType || '월세'}
              </span>
            )}
            {item.category && !item.thumbnail && (
              <span className="text-[10px] font-bold bg-[#F1F9FE] text-[#617789] px-2 py-0.5 rounded">
                {item.category}
              </span>
            )}
          </div>

          <h2 className="text-lg font-bold text-[#183247] leading-snug">
            {displayTitle}
          </h2>
          {item.company && (
            <p className="text-xs font-semibold text-[#079BE8] mt-0.5">
              {item.company}
            </p>
          )}
          {item.source && (
            <div className="flex items-center gap-2 text-xs text-[#617789] mt-1">
              <span>출처: {item.source}</span>
              {item.publishedAt && <span>· {item.publishedAt}</span>}
              {item.verifiedDate && (
                <span className="text-[#075A9D] font-semibold bg-[#EAF8FF] px-1.5 py-0.2 rounded text-[10px] border border-[#B9E5FC]">
                  {item.verifiedDate}
                </span>
              )}
            </div>
          )}
          {item.author && !item.source && (
            <p className="text-xs text-[#617789] mt-0.5">
              작성자: {item.author} · {item.date || item.postedAt}
            </p>
          )}
          <PostTranslationPanel item={item} onApply={setAppliedTranslation} />
        </div>

        {/* Price / Discount Highlight */}
        {item.pricePHP !== undefined && (
          <div className="p-3.5 bg-[#F1F9FE] border border-[#E1ECF3] rounded-xl flex items-baseline justify-between font-mono">
            <div>
              <span className="text-xs text-[#617789] block font-sans">희망 거래가</span>
              <span className="text-xl font-black text-[#075A9D]">
                ₱{item.pricePHP.toLocaleString()}
              </span>
            </div>
            {item.priceKRW && (
              <span className="text-xs text-[#0879E7] font-bold">
                (약 {item.priceKRW.toLocaleString()}원)
              </span>
            )}
          </div>
        )}

        {item.discount && (
          <div className="p-3.5 bg-[#EAF8FF] border border-[#B9E5FC] rounded-xl flex items-center justify-between">
            <div>
              <span className="text-xs text-[#075A9D] font-bold block">
                {item.storeName}
              </span>
              <span className="text-lg font-black text-[#079BE8] font-mono">
                {item.discount}
              </span>
            </div>
            <span className="text-xs text-[#617789]">
              유효기간: {item.validUntil}
            </span>
          </div>
        )}

        {/* Specific Details */}
        <div className="space-y-2.5 text-xs text-[#617789] leading-relaxed bg-white border border-[#E1ECF3] rounded-xl p-3.5">
          {/* Main content body or translated body */}
          {displayContent && (
            <div className="rounded-xl border border-[#E1ECF3] bg-[#F7FBFE] p-4">
              <p className="mb-3 text-[11px] font-black tracking-wide text-[#075A9D]">
                소개
              </p>
              <div className="space-y-3 text-[13px] leading-6 text-[#29485F]">
                {readableContent.map((paragraph, index) => {
                  const isBullet = /^[•▪︎■◆✔✅☑️➡️‼️※]/.test(paragraph);
                  return (
                    <p
                      key={`${index}-${paragraph.slice(0, 12)}`}
                      className={isBullet ? 'rounded-lg bg-white px-3 py-2 font-medium text-[#183247]' : ''}
                    >
                      {paragraph}
                    </p>
                  );
                })}
              </div>
            </div>
          )}

          {/* Job specific */}
          {item.salary && (
            <div className="p-2.5 bg-[#EAF8FF] rounded-lg text-xs font-semibold text-[#075A9D] border border-[#B9E5FC]">
              💰 급여 조건: <span className="font-bold text-[#079BE8]">{formatNumericText(item.salary)}</span>
            </div>
          )}
          {item.workingHours && (
            <p className="text-[11px] text-[#617789]">
              🕒 근무 형태: <span className="text-[#183247] font-medium">{item.workingHours}</span>
            </p>
          )}
          {item.requirements && (
            <div className="text-[11px] bg-[#F7FBFE] p-2.5 rounded-lg border border-[#E1ECF3] text-[#183247]">
              <span className="font-bold block text-[#075A9D] mb-0.5">📋 지원 자격 및 우대:</span>
              <span>{formatNumericText(item.requirements)}</span>
            </div>
          )}
          {item.deadline && (
            <p className="text-[11px] text-[#8799A8]">마감일: {item.deadline}</p>
          )}

          {/* Real estate specific */}
          {item.specs && (
            <p className="font-semibold text-[#075A9D]">매물 제원: {item.specs}</p>
          )}
          {item.deposit && (
            <p className="text-[11px] text-[#617789] bg-[#F1F9FE] p-2 rounded-lg border border-[#E1ECF3]">
              보증금/계약 조건: {item.deposit}
            </p>
          )}

          {/* Chatroom specific */}
          {item.memberCount && (
            <p className="font-semibold text-[#079BE8]">
              참여 인원: {item.memberCount}명 / 정원 {item.maxMembers}명
            </p>
          )}
          {item.lastMessage && (
            <div className="p-2 bg-[#F7FBFE] rounded-lg border border-[#E1ECF3]">
              <span className="text-[10px] text-[#8799A8] block">최근 대화 ({item.lastMessageTime}):</span>
              <span className="text-xs font-medium text-[#183247]">"{item.lastMessage}"</span>
            </div>
          )}
          {item.tags && item.tags.length > 0 && (
            <div className="flex flex-wrap gap-1 pt-1">
              {item.tags.map((t: string) => (
                <span key={t} className="text-[10px] text-[#075A9D] bg-[#EAF8FF] px-1.5 py-0.5 rounded border border-[#B9E5FC]">
                  #{t}
                </span>
              ))}
            </div>
          )}

          {/* Restaurant & Tour specific */}
          {item.signatureMenu && (
            <p className="font-semibold text-[#0879E7]">대표 메뉴: {formatNumericText(item.signatureMenu)}</p>
          )}
          {item.perk && (
            <p className="font-semibold text-[#079BE8]">혜택/특전: {formatNumericText(item.perk)}</p>
          )}
          {item.conditions && (
            <p className="text-[11px] text-[#8799A8]">이용 조건: {formatNumericText(item.conditions)}</p>
          )}
          {item.period && (
            <p className="font-semibold text-[#075A9D]">이벤트 기간: {item.period}</p>
          )}
          {item.host && (
            <p className="text-[11px] text-[#617789]">주최·업체: {item.host}</p>
          )}
          {item.location && (
            <div className="flex items-center gap-1.5 pt-1 text-[#183247]">
              <MapPin size={14} className="text-[#079BE8] shrink-0" />
              <span>위치: {item.location}</span>
            </div>
          )}
          {item.area && (
            <div className="flex items-center gap-1.5 pt-1 text-[#183247]">
              <MapPin size={14} className="text-[#079BE8] shrink-0" />
              <span>지역: {item.area}</span>
            </div>
          )}
          {item.openHours && (
            <div className="flex items-center gap-1.5 text-[#183247]">
              <Clock size={14} className="text-[#079BE8] shrink-0" />
              <span>영업시간: {item.openHours}</span>
            </div>
          )}
          {item.priceRange && (
            <p className="text-[11px] text-[#617789]">예상 가격대: {item.priceRange}</p>
          )}
          {item.phone && (
            <p className="text-[11px] text-[#617789]">연락처: {item.phone}</p>
          )}
          {item.kakaoId && (
            <p className="text-[11px] text-[#617789]">카카오톡: {item.kakaoId}</p>
          )}
          {item.couponAvailable && (
            <div className="rounded-lg border border-[#B9E5FC] bg-[#EAF8FF] p-2 text-[11px] font-bold text-[#075A9D]">
              세부어때 쿠폰 혜택이 있는 업체입니다.
            </div>
          )}

          {(type === 'restaurants' || type === 'businesses' || type === 'life_info') && (
            <button
              type="button"
              onClick={() => setShowMap((current) => !current)}
              aria-label={`${item.title || item.name} 관련 정보를 세부어때 지도에서 보기`}
              className="flex min-h-[40px] w-full items-center justify-center gap-1.5 rounded-xl border border-[#B9E5FC] bg-[#F1F9FE] text-[11px] font-bold text-[#075A9D]"
            >
              <MapIcon size={14} />
              {showMap ? '앱 지도 닫기' : '앱에서 지도보기'}
            </button>
          )}

          {showMap && (type === 'restaurants' || type === 'businesses' || type === 'life_info') && (
            <AppMap
              places={[
                {
                  id: String(item.id),
                  name: item.name || item.title,
                  category: item.category,
                  area: item.area || item.location,
                  address: item.mapQuery,
                  lat: item.lat,
                  lng: item.lng,
                },
              ]}
              selectedArea={item.area || item.location || '세부'}
              heightClassName="h-60"
              enableDirections={Boolean(item.lat && item.lng)}
            />
          )}
        </div>

        {type === 'chatrooms' && (
          <div className="space-y-3 rounded-2xl border border-[#E1ECF3] bg-[#F7FBFE] p-3">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-xs font-bold text-[#183247]">채팅 시뮬레이션</p>
                <p className="text-[10px] text-[#8799A8]">메시지는 현재 기기의 로컬 화면에만 표시됩니다.</p>
              </div>
              <button
                type="button"
                onClick={() => {
                  setChatNotifications((current) => !current);
                  triggerNotice(chatNotifications ? '이 채팅방 알림을 껐습니다.' : '이 채팅방 알림을 켰습니다.');
                }}
                className="flex min-h-[36px] items-center gap-1 rounded-lg bg-white px-2 text-[11px] font-bold text-[#617789]"
              >
                {chatNotifications ? <Bell size={13} /> : <BellOff size={13} />}
                알림 {chatNotifications ? 'ON' : 'OFF'}
              </button>
            </div>

            <div className="max-h-40 space-y-2 overflow-y-auto rounded-xl bg-white p-3">
              <div className="mr-8 rounded-xl rounded-tl-sm bg-[#EAF8FF] p-2 text-[11px] text-[#183247]">
                {item.lastMessage || '아직 표시할 메시지가 없습니다.'}
              </div>
              {chatMessages.map((message, index) => (
                <div
                  key={`${item.id}-${index}`}
                  className="ml-8 rounded-xl rounded-tr-sm bg-[#079BE8] p-2 text-[11px] text-white"
                >
                  {message}
                </div>
              ))}
            </div>

            <form
              className="flex gap-2"
              onSubmit={(event) => {
                event.preventDefault();
                const message = chatInput.trim();
                if (!message) return;
                setLocalMessages((current) => ({
                  ...current,
                  [item.id]: [...(current[item.id] || []), message],
                }));
                setChatInput('');
              }}
            >
              <input
                value={chatInput}
                onChange={(event) => setChatInput(event.target.value)}
                placeholder="로컬 메시지를 입력하세요"
                className="h-11 min-w-0 flex-1 rounded-xl border border-[#E1ECF3] bg-white px-3 text-xs outline-none focus:border-[#079BE8]"
              />
              <button
                type="submit"
                className="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl bg-[#079BE8] text-white"
                aria-label="로컬 메시지 보내기"
              >
                <Send size={16} />
              </button>
            </form>

            <button
              type="button"
              onClick={() => triggerNotice('신고·차단은 시뮬레이션이며 실제 사용자에게 영향을 주지 않습니다.')}
              className="flex min-h-[40px] w-full items-center justify-center gap-1.5 rounded-xl border border-[#E1ECF3] bg-white text-[11px] font-semibold text-[#617789]"
            >
              <ShieldAlert size={14} />
              신고·차단 메뉴
            </button>
          </div>
        )}

        {/* Temporary Notice banner */}
        {actionNotice && (
          <div className="p-2.5 bg-[#EAF8FF] border border-[#079BE8] text-[#075A9D] rounded-xl text-xs font-bold text-center animate-fadeIn">
            {actionNotice}
          </div>
        )}

        {/* Action Buttons */}
        <div className="pt-2 flex items-center gap-2">
          {/* Like */}
          <button
            type="button"
            onClick={() => {
              if (onToggleSaved) onToggleSaved();
              else setLocalSaved((current) => !current);
            }}
            className={`min-h-[44px] min-w-[44px] rounded-xl border flex items-center justify-center transition-colors ${
              saved
                ? 'border-[#0879E7] text-[#0879E7] bg-[#EAF8FF]'
                : 'border-[#E1ECF3] text-[#617789] hover:bg-[#F1F9FE]'
            }`}
            aria-label={type === 'news' || type === 'life_info' ? '북마크' : '관심 항목 저장'}
            aria-pressed={saved}
          >
            <Heart size={18} fill={saved ? '#0879E7' : 'none'} />
          </button>

          {/* Share */}
          <button
            type="button"
            onClick={handleShare}
            className="min-h-[44px] min-w-[44px] rounded-xl border border-[#E1ECF3] text-[#617789] hover:bg-[#F1F9FE] flex items-center justify-center transition-colors"
            aria-label="공유하기"
          >
            {copied ? <Check size={18} className="text-[#079BE8]" /> : <Share2 size={18} />}
          </button>

          {/* Primary Action Button */}
          {type === 'coupons' ? (
            <button
              type="button"
              onClick={() => {
                if (isCouponUsed) return;
                if (isCouponDownloaded && onUseCoupon) {
                  onUseCoupon(item);
                  triggerNotice('쿠폰을 사용 완료 상태로 변경했습니다. 실제 할인권은 발급되지 않습니다.');
                } else if (onDownloadCoupon) {
                  onDownloadCoupon(item);
                  triggerNotice('쿠폰함에 다운로드 보관되었습니다.');
                }
              }}
              disabled={isCouponUsed}
              className={`flex-1 min-h-[44px] px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-colors ${
                isCouponUsed
                  ? 'bg-[#EAF8FF] text-[#075A9D] border border-[#B9E5FC] cursor-default'
                  : 'bg-[#079BE8] hover:bg-[#0879E7] text-white shadow-xs'
              }`}
            >
              {isCouponUsed ? <Check size={16} /> : <Download size={16} />}
              <span>
                {isCouponUsed
                  ? '사용 완료'
                  : isCouponDownloaded
                  ? '사용 완료 처리'
                  : '내 쿠폰에 담기'}
              </span>
            </button>
          ) : type === 'chatrooms' ? (
            <button
              type="button"
              onClick={() => {
                triggerNotice(`[채팅방] '${item.title}' 카카오톡 오픈채팅 시뮬레이션에 연결되었습니다.`);
              }}
              className="flex-1 min-h-[44px] px-4 rounded-xl bg-[#079BE8] hover:bg-[#0879E7] text-white text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition-colors"
            >
              <MessageCircle size={16} />
              <span>오픈채팅 참여하기</span>
            </button>
          ) : type === 'jobs' ? (
            <button
              type="button"
              onClick={() => {
                triggerNotice(`[채용 문의] 담당자 연락처 (${item.contact || '인사팀'}) 연결을 준비합니다.`);
              }}
              className="flex-1 min-h-[44px] px-4 rounded-xl bg-[#075A9D] hover:bg-[#064a82] text-white text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition-colors"
            >
              <Phone size={16} />
              <span>지원 및 채용 문의</span>
            </button>
          ) : type === 'marketplace' ? (
            <button
              type="button"
              onClick={() => {
                triggerNotice(`[중고 직거래] 판매자 '${item.seller}'님과의 1:1 대화창을 시작합니다.`);
              }}
              className="flex-1 min-h-[44px] px-4 rounded-xl bg-[#079BE8] hover:bg-[#0879E7] text-white text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition-colors"
            >
              <MessageCircle size={16} />
              <span>판매자에게 채팅 문의</span>
            </button>
          ) : kakaoId ? (
            <button
              type="button"
              onClick={async () => {
                try {
                  await navigator.clipboard.writeText(kakaoId);
                  triggerNotice(`카카오톡 ID '${kakaoId}'를 복사했습니다. 카카오톡에서 친구 검색 후 문의해주세요.`);
                } catch {
                  triggerNotice(`카카오톡 ID: ${kakaoId}`);
                }
              }}
              className="flex-1 min-h-[44px] px-4 rounded-xl bg-[#079BE8] hover:bg-[#0879E7] text-white text-xs font-bold flex items-center justify-center gap-2 shadow-xs transition-colors"
            >
              <MessageCircle size={16} />
              <span>카카오톡 문의</span>
            </button>
          ) : dialPhone ? (
            <a
              href={`tel:${dialPhone}`}
              className="flex min-h-[44px] flex-1 items-center justify-center gap-2 rounded-xl bg-[#079BE8] px-4 text-xs font-bold text-white shadow-xs transition-colors hover:bg-[#0879E7]"
              aria-label={`${item.title || item.name} 전화 문의 ${phone}`}
            >
              <Phone size={16} />
              <span>전화 문의</span>
            </a>
          ) : null}
        </div>
      </div>
    </BottomSheet>
  );
};
