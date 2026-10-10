import React, { useState } from 'react';
import { Heart, MessageCircle } from 'lucide-react';
import { formatCount, formatNumericText } from '../../utils/format';

interface ContentCardProps {
  title: string;
  subtitle?: string;
  metadata?: Array<{ label?: string; value: string | number }>;
  price?: {
    php: number;
    krw?: number;
    badge?: string;
  };
  thumbnail?: string;
  badge?: string;
  onClick: () => void;
  onLikeToggle?: () => void;
  isLiked?: boolean;
  likeCount?: number;
  commentCount?: number;
  actionText?: string;
  category?: string;
}

export const ContentCard: React.FC<ContentCardProps> = ({
  title,
  subtitle,
  metadata = [],
  price,
  thumbnail,
  badge,
  onClick,
  onLikeToggle,
  isLiked = false,
  likeCount,
  commentCount,
  actionText,
  category,
}) => {
  const [imgError, setImgError] = useState(false);

  return (
    <article
      onClick={onClick}
      role="button"
      tabIndex={0}
      onKeyDown={(e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          onClick();
        }
      }}
      className="bg-white border border-[#E1ECF3] rounded-2xl p-3.5 hover:border-[#079BE8] hover:shadow-xs transition-all duration-200 cursor-pointer text-left relative focus:outline-none focus:ring-2 focus:ring-[#079BE8]/30 select-none"
    >
      <div className="flex gap-3">
        {/* Optional Thumbnail */}
        {thumbnail && (
          <div className="w-20 h-20 sm:w-22 sm:h-22 rounded-xl overflow-hidden bg-[#EAF8FF] shrink-0 relative border border-[#E1ECF3]">
            {!imgError ? (
              <img
                src={thumbnail}
                alt={title}
                referrerPolicy="no-referrer"
                onError={() => setImgError(true)}
                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
            ) : (
              <div className="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-[#EAF8FF] to-[#D9F1FD] text-[#075A9D] text-[10px] p-1 text-center font-medium">
                <span>세부어때</span>
                <span>이미지</span>
              </div>
            )}
            {badge && (
              <span className="absolute top-1 left-1 bg-[#075A9D]/90 text-white text-[9px] font-bold px-1.5 py-0.5 rounded leading-none">
                {badge}
              </span>
            )}
          </div>
        )}

        {/* Content Body */}
        <div className="flex-1 min-w-0 flex flex-col justify-between">
          <div>
            {/* Category & Status (Quiet unboxed text) */}
            {category && (
              <div className="text-[11px] font-bold text-[#079BE8] mb-0.5">
                {category}
              </div>
            )}

            {/* Title */}
            <h3 className="text-sm font-bold text-[#183247] leading-snug line-clamp-2">
              {formatNumericText(title)}
            </h3>

            {/* Subtitle / Excerpt */}
            {subtitle && (
              <p className="text-xs text-[#617789] mt-1 line-clamp-1 leading-normal">
                {formatNumericText(subtitle)}
              </p>
            )}
          </div>

          {/* Pricing or Primary Info */}
          {price && (
            <div className="mt-2 flex items-baseline gap-1.5 font-mono tabular-nums">
              <span className="text-sm font-extrabold text-[#075A9D]">
                ₱{price.php.toLocaleString()}
              </span>
              {price.krw && (
                <span className="text-[11px] text-[#8799A8]">
                  (약 {price.krw.toLocaleString()}원)
                </span>
              )}
            </div>
          )}

          {/* Unboxed Metadata Strip (Zero-Pill Discipline: · separators) */}
          <div className="mt-2 pt-1 border-t border-[#ECF2F6] flex items-center justify-between text-[11px] text-[#8799A8]">
            <div className="flex items-center gap-1.5 truncate">
              {metadata.map((item, idx) => (
                <React.Fragment key={idx}>
                  {idx > 0 && <span aria-hidden="true" className="text-[#C7D7E2]">·</span>}
                  <span className="truncate">
                    {item.label ? `${item.label} ` : ''}
                    {formatNumericText(item.value)}
                  </span>
                </React.Fragment>
              ))}
            </div>

            {/* Action text or stats */}
            <div className="flex items-center gap-2 shrink-0 ml-2">
              {commentCount !== undefined && commentCount > 0 && (
                <span className="flex items-center gap-0.5 text-[#8799A8]">
                  <MessageCircle size={12} />
                  <span>{formatCount(commentCount)}</span>
                </span>
              )}
              {likeCount !== undefined && (
                <span
                  onClick={(e) => {
                    if (onLikeToggle) {
                      e.stopPropagation();
                      onLikeToggle();
                    }
                  }}
                  className={`flex items-center gap-0.5 transition-colors ${
                    isLiked ? 'text-[#0879E7]' : 'text-[#8799A8]'
                  }`}
                >
                  <Heart size={12} fill={isLiked ? '#0879E7' : 'none'} />
                  <span>{formatCount(likeCount)}</span>
                </span>
              )}
              {actionText && (
                <span className="text-xs font-bold text-[#079BE8]">
                  {actionText}
                </span>
              )}
            </div>
          </div>
        </div>
      </div>
    </article>
  );
};
