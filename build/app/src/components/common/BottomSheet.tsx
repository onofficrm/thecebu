import React, { useEffect } from 'react';
import { X } from 'lucide-react';

interface BottomSheetProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  children: React.ReactNode;
  maxHeight?: string;
}

export const BottomSheet: React.FC<BottomSheetProps> = ({
  isOpen,
  onClose,
  title,
  children,
  maxHeight = 'max-h-[85vh]',
}) => {
  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => {
      document.body.style.overflow = '';
    };
  }, [isOpen]);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-end justify-center">
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity duration-200"
        onClick={onClose}
        aria-hidden="true"
      />

      {/* Sheet Content */}
      <div
        role="dialog"
        aria-modal="true"
        className={`relative z-10 w-full max-w-md bg-white rounded-t-3xl shadow-2xl flex flex-col ${maxHeight} overflow-hidden animate-in slide-in-from-bottom duration-250`}
      >
        {/* Drag Handle */}
        <div className="pt-3 pb-1 cursor-grab" onClick={onClose}>
          <div className="w-10 h-1.5 bg-[#E1ECF3] rounded-full mx-auto" />
        </div>

        {/* Header */}
        <div className="flex items-center justify-between px-5 py-3 border-b border-[#ECF2F6]">
          <h2 className="text-base font-bold text-[#183247] tracking-tight">
            {title || '상세 정보'}
          </h2>
          <button
            type="button"
            onClick={onClose}
            aria-label="닫기"
            className="min-h-[44px] min-w-[44px] -mr-2 flex items-center justify-center text-[#617789] hover:text-[#183247] rounded-full transition-colors"
          >
            <X size={20} />
          </button>
        </div>

        {/* Scrollable Body */}
        <div className="flex-1 overflow-y-auto px-5 py-4 pb-safe no-scrollbar">
          {children}
        </div>
      </div>
    </div>
  );
};
