import React from 'react';
import { HelpCircle, RefreshCw } from 'lucide-react';

interface EmptyStateProps {
  title?: string;
  description?: string;
  actionText?: string;
  onAction?: () => void;
  icon?: React.ReactNode;
}

export const EmptyState: React.FC<EmptyStateProps> = ({
  title = '내용이 없습니다',
  description = '등록된 데이터가 없거나 조건에 맞는 결과가 없습니다.',
  actionText,
  onAction,
  icon,
}) => {
  return (
    <div className="flex flex-col items-center justify-center py-12 px-4 text-center bg-white border border-[#E1ECF3] rounded-2xl my-3">
      <div className="w-12 h-12 rounded-2xl bg-[#EAF8FF] text-[#079BE8] flex items-center justify-center mb-3">
        {icon || <HelpCircle size={24} />}
      </div>
      <h3 className="text-sm font-bold text-[#183247] mb-1">{title}</h3>
      <p className="text-xs text-[#617789] max-w-xs leading-relaxed mb-4">
        {description}
      </p>
      {actionText && onAction && (
        <button
          type="button"
          onClick={onAction}
          className="min-h-[44px] px-4 py-2 bg-[#0879E7] text-white text-xs font-semibold rounded-xl hover:bg-[#066BCF] transition-colors flex items-center gap-1.5"
        >
          <RefreshCw size={14} />
          <span>{actionText}</span>
        </button>
      )}
    </div>
  );
};
