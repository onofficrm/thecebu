import React, { useEffect } from 'react';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-react';

export interface ToastData {
  id: string;
  message: string;
  type?: 'success' | 'info' | 'warning';
  duration?: number;
}

interface ToastMessageProps {
  toast: ToastData | null;
  onClose: () => void;
}

export const ToastMessage: React.FC<ToastMessageProps> = ({
  toast,
  onClose,
}) => {
  useEffect(() => {
    if (!toast) return;
    const timer = setTimeout(() => {
      onClose();
    }, toast.duration || 3000);
    return () => clearTimeout(timer);
  }, [toast, onClose]);

  if (!toast) return null;

  const icons = {
    success: <CheckCircle2 size={18} className="text-[#00C5E8]" />,
    info: <Info size={18} className="text-[#079BE8]" />,
    warning: <AlertCircle size={18} className="text-[#0879E7]" />,
  };

  return (
    <div className="fixed bottom-20 left-4 right-4 z-50 flex justify-center pointer-events-none animate-in fade-in slide-in-from-bottom-3 duration-200">
      <div className="bg-[#183247] text-white px-4 py-3 rounded-2xl shadow-xl flex items-center gap-3 max-w-sm w-full pointer-events-auto border border-[#079BE8]/30">
        <div className="shrink-0">{icons[toast.type || 'info']}</div>
        <p className="text-xs font-medium leading-snug flex-1">
          {toast.message}
        </p>
        <button
          type="button"
          onClick={onClose}
          className="text-white/60 hover:text-white p-1 rounded-full transition-colors shrink-0"
          aria-label="닫기"
        >
          <X size={14} />
        </button>
      </div>
    </div>
  );
};
