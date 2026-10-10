import React, { useEffect } from 'react';
import { X } from 'lucide-react';

interface ModalDialogProps {
  isOpen: boolean;
  title: string;
  description?: string;
  onClose: () => void;
  children: React.ReactNode;
  footer?: React.ReactNode;
}

export const ModalDialog: React.FC<ModalDialogProps> = ({
  isOpen,
  title,
  description,
  onClose,
  children,
  footer,
}) => {
  useEffect(() => {
    if (!isOpen) return;
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') onClose();
    };
    document.addEventListener('keydown', onKeyDown);
    return () => document.removeEventListener('keydown', onKeyDown);
  }, [isOpen, onClose]);

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
      <button
        type="button"
        className="absolute inset-0 bg-[#183247]/35 backdrop-blur-[2px]"
        onClick={onClose}
        aria-label="대화상자 닫기"
      />
      <section
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-dialog-title"
        className="relative z-10 w-full max-w-sm overflow-hidden rounded-2xl border border-[#E1ECF3] bg-white shadow-2xl"
      >
        <header className="flex items-start justify-between gap-3 border-b border-[#ECF2F6] px-5 py-4">
          <div>
            <h2 id="modal-dialog-title" className="text-base font-bold text-[#183247]">
              {title}
            </h2>
            {description && <p className="mt-1 text-xs leading-relaxed text-[#617789]">{description}</p>}
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="닫기"
            className="-mr-2 -mt-2 flex min-h-11 min-w-11 items-center justify-center rounded-full text-[#617789] hover:bg-[#F1F9FE] hover:text-[#183247]"
          >
            <X size={19} />
          </button>
        </header>
        <div className="max-h-[70vh] overflow-y-auto px-5 py-4">{children}</div>
        {footer && <footer className="border-t border-[#ECF2F6] px-5 py-4">{footer}</footer>}
      </section>
    </div>
  );
};
