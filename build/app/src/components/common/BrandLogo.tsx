import React from 'react';

export interface BrandLogoProps {
  size?: 'sm' | 'md' | 'lg' | 'xl';
  variant?: 'full' | 'compact' | 'symbol';
  modeBadge?: 'resident' | 'tourist' | null;
  className?: string;
  onClick?: () => void;
}

export const BrandLogo: React.FC<BrandLogoProps> = ({
  size = 'md',
  variant = 'full',
  modeBadge = null,
  className = '',
  onClick,
}) => {
  const logoSizes = {
    sm: { width: 92, badge: 'text-[9px] px-1.5 py-0.5' },
    md: { width: 112, badge: 'text-[10px] px-1.5 py-0.5' },
    lg: { width: 148, badge: 'text-[11px] px-2 py-0.5' },
    xl: { width: 190, badge: 'text-xs px-2.5 py-1' },
  }[size];

  // The same official wordmark is used for every variant. Cropping or recreating
  // the symbol would alter the supplied brand asset.
  const logo = (
    <>
      <img
        src="/assets/brand/cebu-logo-main.png"
        alt="세부어때"
        width={logoSizes.width}
        height={Math.round(logoSizes.width * 197 / 912)}
        className="block h-auto max-w-full shrink-0"
        style={{ width: logoSizes.width }}
        draggable={false}
      />
      {modeBadge && variant !== 'symbol' && (
        <span
          className={`${logoSizes.badge} shrink-0 rounded-md border border-[#B9E5FC] bg-[#EAF8FF] font-bold leading-none text-[#075A9D]`}
        >
          {modeBadge === 'resident' ? '교민' : '관광'}
        </span>
      )}
    </>
  );

  if (onClick) {
    return (
      <button
        type="button"
        onClick={onClick}
        aria-label="세부어때 홈으로 이동"
        className={`inline-flex items-center gap-2 select-none cursor-pointer active:opacity-75 transition-opacity ${className}`}
      >
        {logo}
      </button>
    );
  }

  return (
    <span className={`inline-flex items-center gap-2 select-none ${className}`}>
      {logo}
    </span>
  );
};
