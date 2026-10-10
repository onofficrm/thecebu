import React, { useState } from 'react';
import { BottomSheet } from '../common/BottomSheet';
import { MarketplaceItem } from '../../types';
import { Plus } from 'lucide-react';

interface MarketplaceCreateModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (item: Partial<MarketplaceItem>) => void;
}

const CATEGORIES = ['가전/디지털', '가구/인테리어', '오토바이/차량', '스포츠/레저', '생활용품', '기타'];
const LOCATIONS = ['세부 IT파크', '세부 비즈니스 파크 (아얄라)', '만다우에', '막탄 마리바고', '막탄 푼타 엥가뇨', '바닐라드/마리아루이사', '탈람반'];

export const MarketplaceCreateModal: React.FC<MarketplaceCreateModalProps> = ({
  isOpen,
  onClose,
  onSubmit,
}) => {
  const [title, setTitle] = useState('');
  const [category, setCategory] = useState(CATEGORIES[0]);
  const [pricePHP, setPricePHP] = useState('');
  const [location, setLocation] = useState(LOCATIONS[0]);
  const [description, setDescription] = useState('');
  const [validationError, setValidationError] = useState<string | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim() || !pricePHP) {
      setValidationError('제목과 판매 가격을 모두 입력해주세요.');
      return;
    }

    const phpNum = parseInt(pricePHP.replace(/[^0-9]/g, ''), 10) || 0;
    const newItem: Partial<MarketplaceItem> = {
      id: `market-local-${Date.now()}`,
      title: title.trim(),
      pricePHP: phpNum,
      location,
      status: '판매중',
      postedAt: '방금 전',
      category,
      seller: '나 (게스트)',
      likes: 0,
      chatCount: 0,
      description: description.trim() || '등록자가 상세 설명을 입력하지 않았습니다.',
    };

    onSubmit(newItem);
    setTitle('');
    setPricePHP('');
    setDescription('');
    setValidationError(null);
    onClose();
  };

  return (
    <BottomSheet isOpen={isOpen} onClose={onClose} title="중고물품 직거래 등록">
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="bg-[#EAF8FF] text-[#075A9D] border border-[#B9E5FC] px-3 py-2 rounded-xl text-xs">
          💡 <span className="font-semibold text-[#075A9D]">로컬 테스트 등록 모드</span>: 등록된 상품은 현재 세션 동안 중고거래 목록 최상단에 즉시 반영됩니다.
        </div>

        {validationError && (
          <div className="p-2.5 bg-[#FFF2EE] text-[#E74C3C] text-xs font-bold rounded-xl border border-[#FFCCD0]">
            {validationError}
          </div>
        )}

        {/* Title */}
        <div>
          <label className="text-xs font-bold text-[#183247] block mb-1">
            상품 제목 <span className="text-[#0879E7]">*</span>
          </label>
          <input
            type="text"
            required
            value={title}
            onChange={(e) => {
              setTitle(e.target.value);
              if (validationError) setValidationError(null);
            }}
            placeholder="예: 귀국 정리 삼성 무풍 에어컨 6평형"
            className="w-full h-11 px-3.5 bg-[#F7FBFE] border border-[#E1ECF3] rounded-xl text-xs text-[#183247] focus:outline-none focus:border-[#079BE8] transition-colors"
          />
        </div>

        {/* Category & Location Grid */}
        <div className="grid grid-cols-2 gap-2.5">
          <div>
            <label className="text-xs font-bold text-[#183247] block mb-1">
              카테고리
            </label>
            <select
              value={category}
              onChange={(e) => setCategory(e.target.value)}
              className="w-full h-11 px-3 bg-[#F7FBFE] border border-[#E1ECF3] rounded-xl text-xs text-[#183247] focus:outline-none focus:border-[#079BE8]"
            >
              {CATEGORIES.map((cat) => (
                <option key={cat} value={cat}>
                  {cat}
                </option>
              ))}
            </select>
          </div>

          <div>
            <label className="text-xs font-bold text-[#183247] block mb-1">
              거래 희망 지역
            </label>
            <select
              value={location}
              onChange={(e) => setLocation(e.target.value)}
              className="w-full h-11 px-3 bg-[#F7FBFE] border border-[#E1ECF3] rounded-xl text-xs text-[#183247] focus:outline-none focus:border-[#079BE8]"
            >
              {LOCATIONS.map((loc) => (
                <option key={loc} value={loc}>
                  {loc}
                </option>
              ))}
            </select>
          </div>
        </div>

        {/* Price PHP */}
        <div>
          <label className="text-xs font-bold text-[#183247] block mb-1">
            판매 희망 가격 (페소, PHP) <span className="text-[#0879E7]">*</span>
          </label>
          <div className="relative">
            <span className="absolute left-3.5 top-3 text-xs font-bold text-[#617789]">₱</span>
            <input
              type="number"
              required
              value={pricePHP}
              onChange={(e) => {
                setPricePHP(e.target.value);
                if (validationError) setValidationError(null);
              }}
              placeholder="예: 12000"
              className="w-full h-11 pl-8 pr-20 bg-[#F7FBFE] border border-[#E1ECF3] rounded-xl text-xs font-bold text-[#183247] focus:outline-none focus:border-[#079BE8]"
            />
          </div>
        </div>

        {/* Description */}
        <div>
          <label className="text-xs font-bold text-[#183247] block mb-1">
            상품 설명 및 거래 방식
          </label>
          <textarea
            rows={3}
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            placeholder="구입 시기, 사용감, 픽업 및 배송 가능 여부를 적어주시면 빠른 거래에 도움이 됩니다."
            className="w-full p-3 bg-[#F7FBFE] border border-[#E1ECF3] rounded-xl text-xs text-[#183247] focus:outline-none focus:border-[#079BE8] resize-none"
          />
        </div>

        {/* Submit Button */}
        <div className="pt-2 flex gap-2">
          <button
            type="button"
            onClick={onClose}
            className="w-1/3 min-h-[44px] rounded-xl border border-[#E1ECF3] text-xs font-bold text-[#617789] hover:bg-[#F1F9FE]"
          >
            취소
          </button>
          <button
            type="submit"
            className="flex-1 min-h-[44px] rounded-xl bg-[#079BE8] hover:bg-[#0879E7] text-white text-xs font-bold shadow-xs transition-colors flex items-center justify-center gap-1.5"
          >
            <Plus size={16} />
            <span>상품 등록 완료</span>
          </button>
        </div>
      </form>
    </BottomSheet>
  );
};
