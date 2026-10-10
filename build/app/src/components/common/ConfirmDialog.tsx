import React, { useEffect } from 'react';
import { AlertTriangle, HelpCircle } from 'lucide-react';

interface ConfirmDialogProps {
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title: string;
  message: string;
  confirmText?: string;
  cancelText?: string;
  variant?: 'danger' | 'primary';
}

export const ConfirmDialog: React.FC<ConfirmDialogProps> = ({
  isOpen,
  onClose,
  onConfirm,
  title,
  message,
  confirmText = '확인',
  cancelText = '취소',
  variant = 'primary',
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
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"
        onClick={onClose}
        aria-hidden="true"
      />

      {/* Dialog box */}
      <div
        role="dialog"
        aria-modal="true"
        className="relative z-10 w-full max-w-sm bg-white rounded-2xl shadow-xl p-5 border border-[#E1ECF3] animate-in zoom-in-95 duration-150"
      >
        <div className="flex items-start gap-3.5 mb-3">
          <div
            className={`w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${
              variant === 'danger'
                ? 'bg-[#FFF2EE] text-[#E74C3C]'
                : 'bg-[#EAF8FF] text-[#079BE8]'
            }`}
          >
            {variant === 'danger' ? (
              <AlertTriangle size={20} />
            ) : (
              <HelpCircle size={20} />
            )}
          </div>
          <div>
            <h3 className="text-base font-bold text-[#183247]">{title}</h3>
            <p className="text-xs text-[#617789] mt-1 leading-relaxed">
              {message}
            </p>
          </div>
        </div>

        <div className="flex items-center justify-end gap-2 mt-5">
          <button
            type="button"
            onClick={onClose}
            className="min-h-[44px] px-4 py-2 text-xs font-semibold text-[#617789] hover:text-[#183247] hover:bg-[#F1F9FE] rounded-xl transition-colors"
          >
            {cancelText}
          </button>
          <button
            type="button"
            onClick={() => {
              onConfirm();
              onClose();
            }}
            className={`min-h-[44px] px-4 py-2 text-xs font-semibold text-white rounded-xl transition-colors shadow-xs ${
              variant === 'danger'
                ? 'bg-[#E74C3C] hover:bg-[#D43F30]'
                : 'bg-[#079BE8] hover:bg-[#0879E7]'
            }`}
          >
            {confirmText}
          </button>
        </div>
      </div>
    </div>
  );
};
