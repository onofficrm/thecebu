import React from 'react';
import { ChevronRight } from 'lucide-react';

interface SectionHeaderProps {
  title: string;
  subtitle?: string;
  actionText?: string;
  onAction?: () => void;
  badge?: string;
  className?: string;
}

export const SectionHeader: React.FC<SectionHeaderProps> = ({
  title,
  subtitle,
  actionText,
  onAction,
  badge,
  className = '',
}) => {
  return (
    <div className={`flex items-end justify-between mb-3 px-1 ${className}`}>
      <div>
        <div className="flex items-center gap-2">
          <h2 className="text-base font-bold text-[#183247] tracking-tight">
            {title}
          </h2>
          {badge && (
            <span className="text-[10px] font-bold text-[#075A9D] bg-[#EAF8FF] px-1.5 py-0.5 rounded border border-[#B9E5FC]">
              {badge}
            </span>
          )}
        </div>
        {subtitle && (
          <p className="text-xs text-[#617789] mt-0.5 leading-normal">
            {subtitle}
          </p>
        )}
      </div>

      {actionText && onAction && (
        <button
          type="button"
          onClick={onAction}
          className="min-h-[36px] -mr-1 px-1 flex items-center gap-0.5 text-xs font-semibold text-[#079BE8] hover:text-[#0879E7] transition-colors"
        >
          <span>{actionText}</span>
          <ChevronRight size={14} />
        </button>
      )}
    </div>
  );
};
