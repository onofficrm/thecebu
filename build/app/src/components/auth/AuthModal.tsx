import React, { useState } from 'react';
import { BottomSheet } from '../common/BottomSheet';
import { MessageCircle, Mail } from 'lucide-react';
import { BrandLogo } from '../common/BrandLogo';

interface AuthModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
  title?: string;
}

export const AuthModal: React.FC<AuthModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
  title = '세부어때 로그인 / 회원가입',
}) => {
  const [loginMsg, setLoginMsg] = useState<string | null>(null);

  const handleMockLogin = (provider: string) => {
    setLoginMsg(`${provider} 로그인은 추후 연동될 예정입니다. 현재는 모든 기능을 자유롭게 이용하실 수 있습니다.`);
    setTimeout(() => {
      if (onSuccess) onSuccess();
      onClose();
    }, 1500);
  };

  return (
    <BottomSheet isOpen={isOpen} onClose={onClose} title={title}>
      <div className="space-y-4 text-center pb-2">
        {/* Intro */}
        <div className="py-2 flex flex-col items-center">
          <BrandLogo size="lg" variant="symbol" className="mb-2" />
          <h3 className="text-base font-bold text-[#183247]">
            세부어때 회원 서비스
          </h3>
          <p className="text-xs text-[#617789] mt-1 leading-relaxed">
            관심 글 찜하기, 중고거래 채팅, 단독 쿠폰함 관리를 위해 로그인할 수 있습니다.
          </p>
        </div>

        {loginMsg && (
          <div className="p-2.5 bg-[#EAF8FF] border border-[#079BE8] text-[#075A9D] rounded-xl text-xs font-bold animate-fadeIn">
            {loginMsg}
          </div>
        )}

        {/* Login Buttons */}
        <div className="space-y-2.5">
          {/* Kakao */}
          <button
            type="button"
            onClick={() => handleMockLogin('카카오')}
            className="w-full min-h-[48px] bg-[#FEE500] hover:bg-[#FADA0A] text-[#191919] font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-colors shadow-2xs active:scale-[0.99]"
          >
            <MessageCircle size={18} fill="#191919" />
            <span>카카오로 1초 간편 로그인</span>
          </button>

          {/* Naver */}
          <button
            type="button"
            onClick={() => handleMockLogin('네이버')}
            className="w-full min-h-[48px] bg-[#03C75A] hover:bg-[#02B351] text-white font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-colors shadow-2xs active:scale-[0.99]"
          >
            <span className="font-black text-sm">N</span>
            <span>네이버로 로그인</span>
          </button>

          {/* Email */}
          <button
            type="button"
            onClick={() => handleMockLogin('이메일')}
            className="w-full min-h-[48px] bg-white border border-[#E1ECF3] hover:bg-[#F7FBFE] text-[#183247] font-bold text-xs rounded-xl flex items-center justify-center gap-2 transition-colors shadow-2xs active:scale-[0.99]"
          >
            <Mail size={16} className="text-[#617789]" />
            <span>이메일로 시작하기</span>
          </button>
        </div>

        {/* Guest continue & note */}
        <div className="pt-2 border-t border-[#ECF2F6] space-y-2">
          <button
            type="button"
            onClick={onClose}
            className="text-xs font-semibold text-[#079BE8] hover:underline py-1"
          >
            로그인 없이 계속 둘러보기
          </button>

          <p className="text-[10px] text-[#8799A8]">
            ※ 세부어때는 모드 선택 및 일반 콘텐츠 조회를 위해 로그인을 강제하지 않습니다.
          </p>
        </div>
      </div>
    </BottomSheet>
  );
};
