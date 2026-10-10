import React, { useState } from 'react';
import { AppMode, ServiceId } from '../types';
import { SERVICES_META } from '../data/mockData';
import { getDefaultShortcuts } from '../utils/storage';
import { ServiceIcon } from '../components/common/ServiceIcon';
import {
  RotateCcw,
  Check,
  ArrowUp,
  ArrowDown,
  Sparkles,
  AlertCircle,
  Plus,
  GripVertical,
} from 'lucide-react';

interface HomeCustomizeScreenProps {
  currentMode: AppMode;
  currentShortcuts: ServiceId[];
  onSaveShortcuts: (newShortcuts: ServiceId[]) => void;
  onClose: () => void;
  showToast: (msg: string, type?: 'success' | 'info' | 'warning') => void;
}

export const HomeCustomizeScreen: React.FC<HomeCustomizeScreenProps> = ({
  currentMode,
  currentShortcuts,
  onSaveShortcuts,
  onClose,
  showToast,
}) => {
  const [selectedIds, setSelectedIds] = useState<ServiceId[]>([...currentShortcuts]);
  const [warningMsg, setWarningMsg] = useState<string | null>(null);
  const [draggedId, setDraggedId] = useState<ServiceId | null>(null);

  const handleToggle = (id: ServiceId) => {
    if (selectedIds.includes(id)) {
      setSelectedIds(selectedIds.filter((item) => item !== id));
      setWarningMsg(null);
    } else {
      if (selectedIds.length >= 4) {
        const msg = '홈 바로가기는 최대 4개까지 선택할 수 있습니다. 변경하려면 기존 메뉴를 먼저 해제해주세요.';
        setWarningMsg(msg);
        showToast(msg, 'warning');
        return;
      }
      setSelectedIds([...selectedIds, id]);
      setWarningMsg(null);
    }
  };

  const handleMoveUp = (index: number) => {
    if (index === 0) return;
    const copy = [...selectedIds];
    const temp = copy[index - 1];
    copy[index - 1] = copy[index];
    copy[index] = temp;
    setSelectedIds(copy);
  };

  const handleMoveDown = (index: number) => {
    if (index === selectedIds.length - 1) return;
    const copy = [...selectedIds];
    const temp = copy[index + 1];
    copy[index + 1] = copy[index];
    copy[index] = temp;
    setSelectedIds(copy);
  };

  const handleApply = () => {
    onSaveShortcuts(selectedIds);
    onClose();
  };

  const handleReset = () => {
    setSelectedIds(getDefaultShortcuts(currentMode));
    setWarningMsg(null);
    showToast('기본 구성을 미리보기에 불러왔습니다. 적용하기를 눌러 저장하세요.', 'info');
  };

  const handleDrop = (targetId: ServiceId) => {
    if (!draggedId || draggedId === targetId) return;
    setSelectedIds((current) => {
      const next = [...current];
      const fromIndex = next.indexOf(draggedId);
      const toIndex = next.indexOf(targetId);
      if (fromIndex < 0 || toIndex < 0) return current;
      next.splice(fromIndex, 1);
      next.splice(toIndex, 0, draggedId);
      return next;
    });
    setDraggedId(null);
  };

  const selectedMetaList = selectedIds
    .map((id) => SERVICES_META.find((s) => s.id === id)!)
    .filter(Boolean);

  return (
    <div className="space-y-6 pb-28 text-left">
      {/* 1. Header Information */}
      <div className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs">
        <div className="flex items-center gap-2 mb-1">
          <Sparkles size={18} className="text-[#079BE8]" />
          <h1 className="text-base font-black text-[#183247] tracking-tight">
            나만의 세부어때
          </h1>
          <span
            className={`text-[10px] font-bold px-2.5 py-0.5 rounded-full border ${
              currentMode === 'resident'
                ? 'bg-[#EAF8FF] text-[#075A9D] border-[#B9E5FC]'
                : 'bg-[#F1F9FE] text-[#0879E7] border-[#C7E9FB]'
            }`}
          >
            {currentMode === 'resident' ? '교민 홈 설정' : '관광객 홈 설정'}
          </span>
        </div>
        <p className="text-xs text-[#617789] leading-relaxed">
          자주 사용하는 메뉴를 선택해 홈 화면을 편리하게 꾸며보세요. (최대 4개)
        </p>
      </div>

      {/* 2. Section A: 홈 미리보기 (Live Preview) */}
      <section className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs space-y-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-1.5">
            <h2 className="text-xs font-bold text-[#183247]">
              홈 화면 미리보기
            </h2>
            <span className="text-[11px] text-[#8799A8]">
              (현재 선택된 바로가기)
            </span>
          </div>

          <div className="text-xs font-mono font-bold">
            <span
              className={
                selectedIds.length === 4
                  ? 'text-[#079BE8]'
                  : selectedIds.length === 0
                  ? 'text-[#0879E7]'
                  : 'text-[#075A9D]'
              }
            >
              4개 중 {selectedIds.length}개 선택
            </span>
            {selectedIds.length === 4 && (
              <span className="ml-1 text-[10px] text-[#079BE8]">완료</span>
            )}
          </div>
        </div>

        {/* 4 Preview Slots */}
        <div className="grid grid-cols-4 gap-2">
          {[0, 1, 2, 3].map((slotIdx) => {
            const item = selectedMetaList[slotIdx];
            if (item) {
              return (
                <div
                  key={item.id}
                  className="bg-[#F7FBFE] border border-[#B9E5FC] rounded-2xl p-2.5 flex flex-col items-center justify-center text-center relative group min-h-[84px] shadow-2xs"
                >
                  <span className="absolute top-1 left-1.5 text-[9px] font-bold text-[#075A9D] font-mono">
                    {slotIdx + 1}
                  </span>
                  <div
                    className="w-9 h-9 rounded-xl flex items-center justify-center mb-1 shadow-2xs"
                    style={{ backgroundColor: `${item.color}15`, color: item.color }}
                  >
                    <ServiceIcon iconName={item.icon} size={18} color={item.color} />
                  </div>
                  <span className="text-[11px] font-bold text-[#183247] truncate w-full">
                    {item.name}
                  </span>
                </div>
              );
            }

            return (
              <div
                key={`empty-${slotIdx}`}
                className="border-2 border-dashed border-[#E1ECF3] rounded-2xl p-2.5 flex flex-col items-center justify-center text-center min-h-[84px] bg-[#F7FBFE]"
              >
                <span className="text-[10px] text-[#8799A8] font-mono font-bold mb-0.5">
                  슬롯 {slotIdx + 1}
                </span>
                <span className="text-[10px] text-[#8799A8] font-medium leading-tight">
                  메뉴 선택
                </span>
              </div>
            );
          })}
        </div>

        {warningMsg && (
          <div className="p-2.5 bg-[#EAF8FF] border border-[#079BE8] rounded-xl flex items-center gap-2 text-[11px] text-[#075A9D] animate-in fade-in">
            <AlertCircle size={14} className="shrink-0 text-[#079BE8]" />
            <span>{warningMsg}</span>
          </div>
        )}
      </section>

      {/* 3. Section D: 선택된 메뉴 순서 변경 */}
      {selectedMetaList.length > 1 && (
        <section className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs space-y-2.5">
          <div className="flex items-center justify-between pb-2 border-b border-[#ECF2F6]">
            <h2 className="text-xs font-bold text-[#183247]">
              선택한 메뉴 순서 변경
            </h2>
            <span className="text-[11px] text-[#8799A8]">
              드래그 또는 화살표로 조정하세요
            </span>
          </div>

          <div className="space-y-1.5" role="list" aria-label="선택한 메뉴 순서">
            {selectedMetaList.map((item, idx) => (
              <div
                key={item.id}
                role="listitem"
                aria-label={`${idx + 1}번째 ${item.name}, 드래그하여 순서 변경`}
                draggable
                onDragStart={() => setDraggedId(item.id)}
                onDragEnd={() => setDraggedId(null)}
                onDragOver={(event) => event.preventDefault()}
                onDrop={() => handleDrop(item.id)}
                className={`flex items-center justify-between p-2.5 bg-[#F7FBFE] border rounded-xl transition-all ${
                  draggedId === item.id
                    ? 'opacity-50 border-[#079BE8]'
                    : 'border-[#E1ECF3]'
                }`}
              >
                <div className="flex items-center gap-2.5 min-w-0">
                  <GripVertical
                    size={16}
                    className="text-[#8799A8] shrink-0 cursor-grab"
                    aria-hidden="true"
                  />
                  <span className="w-5 h-5 rounded-full bg-[#075A9D] text-white text-[10px] font-bold font-mono flex items-center justify-center shrink-0">
                    {idx + 1}
                  </span>
                  <div
                    className="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                    style={{ backgroundColor: `${item.color}15`, color: item.color }}
                  >
                    <ServiceIcon iconName={item.icon} size={15} color={item.color} />
                  </div>
                  <span className="text-xs font-bold text-[#183247] truncate">
                    {item.name}
                  </span>
                </div>

                <div className="flex items-center gap-1 shrink-0">
                  <button
                    type="button"
                    disabled={idx === 0}
                    onClick={() => handleMoveUp(idx)}
                    className="p-1 rounded-md text-[#617789] hover:text-[#079BE8] hover:bg-white disabled:opacity-30 disabled:hover:bg-transparent min-h-[32px] min-w-[32px] flex items-center justify-center"
                    aria-label={`${item.name} 위로 이동`}
                  >
                    <ArrowUp size={14} />
                  </button>
                  <button
                    type="button"
                    disabled={idx === selectedMetaList.length - 1}
                    onClick={() => handleMoveDown(idx)}
                    className="p-1 rounded-md text-[#617789] hover:text-[#079BE8] hover:bg-white disabled:opacity-30 disabled:hover:bg-transparent min-h-[32px] min-w-[32px] flex items-center justify-center"
                    aria-label={`${item.name} 아래로 이동`}
                  >
                    <ArrowDown size={14} />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </section>
      )}

      {/* 4. Section B: 전체 10개 메뉴 선택 목록 */}
      <section className="bg-white border border-[#E1ECF3] rounded-2xl p-4 shadow-2xs space-y-3">
        <div className="flex items-center justify-between pb-2 border-b border-[#ECF2F6]">
          <div>
            <h2 className="text-xs font-bold text-[#183247]">
              전체 메뉴 선택 (10개)
            </h2>
            <p className="text-[11px] text-[#8799A8] mt-0.5">
              원하는 메뉴를 탭하여 추가하거나 해제하세요.
            </p>
          </div>
          <span className="text-[11px] font-bold text-[#079BE8] font-mono">
            {selectedIds.length}/4
          </span>
        </div>

        <div className="space-y-2">
          {SERVICES_META.map((service) => {
            const isSelected = selectedIds.includes(service.id);
            const selectionOrder = selectedIds.indexOf(service.id);

            return (
              <button
                type="button"
                key={service.id}
                onClick={() => handleToggle(service.id)}
                aria-pressed={isSelected}
                aria-label={`${service.name} ${isSelected ? '선택 해제' : '선택'}`}
                className={`flex items-center justify-between p-3 rounded-xl border-2 transition-all cursor-pointer min-h-[56px] ${
                  isSelected
                    ? 'border-[#079BE8] bg-[#EAF8FF] shadow-2xs'
                    : 'border-[#E1ECF3] bg-white hover:border-[#B9E5FC]'
                }`}
              >
                <div className="flex items-center gap-3 min-w-0">
                  <div
                    className="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                    style={{ backgroundColor: `${service.color}15`, color: service.color }}
                  >
                    <ServiceIcon iconName={service.icon} size={18} color={service.color} />
                  </div>
                  <div className="min-w-0">
                    <div className="flex items-center gap-1.5">
                      <span className="text-xs font-bold text-[#183247]">
                        {service.name}
                      </span>
                      <span className="text-[9px] text-[#8799A8] font-medium">
                        {service.enName}
                      </span>
                    </div>
                    <p className="text-[11px] text-[#617789] truncate mt-0.5">
                      {service.description}
                    </p>
                  </div>
                </div>

                <div className="shrink-0 ml-2">
                  {isSelected ? (
                    <div className="w-6 h-6 rounded-lg bg-[#079BE8] text-white flex items-center justify-center font-mono text-xs font-bold shadow-2xs">
                      {selectionOrder + 1}
                    </div>
                  ) : (
                    <div className="w-6 h-6 rounded-lg border-2 border-[#E1ECF3] bg-white hover:border-[#B9E5FC] flex items-center justify-center text-[#8799A8]">
                      <Plus size={12} />
                    </div>
                  )}
                </div>
              </button>
            );
          })}
        </div>
      </section>

      {/* 5. 안내 문구 */}
      <div className="bg-[#EAF8FF] border border-[#B9E5FC] rounded-2xl p-3.5 flex items-start gap-2.5 text-left">
        <Sparkles size={16} className="text-[#079BE8] shrink-0 mt-0.5" />
        <div className="text-xs text-[#183247] leading-relaxed">
          <p className="font-bold text-[#075A9D]">
            모든 서비스는 전체메뉴에서 언제든 이용할 수 있어요.
          </p>
          <p className="text-[11px] text-[#617789] mt-0.5">
            홈 바로가기는 자주 쓰는 메뉴의 빠른 접근을 위한 설정이며, 선택하지 않은 메뉴도 하단 &apos;전체메뉴&apos; 탭에서 자유롭게 이용하실 수 있습니다.
          </p>
        </div>
      </div>

      {/* 6. 하단 고정 액션 버튼 */}
      <div className="sticky bottom-0 bg-white/95 backdrop-blur-md pt-3 pb-safe border-t border-[#E1ECF3] -mx-4 px-4 shadow-lg z-30 flex items-center gap-2">
        <button
          type="button"
          onClick={onClose}
          className="min-h-[46px] px-4 rounded-xl border border-[#E1ECF3] bg-white hover:bg-[#F1F9FE] text-xs font-bold text-[#617789] transition-colors"
        >
          취소
        </button>

        <button
          type="button"
          onClick={handleReset}
          className="min-h-[46px] px-3.5 rounded-xl border border-[#E1ECF3] bg-white hover:bg-[#F1F9FE] text-xs font-semibold text-[#617789] flex items-center gap-1 transition-colors"
        >
          <RotateCcw size={13} />
          <span>기본 복원</span>
        </button>

        <button
          type="button"
          onClick={handleApply}
          className="flex-1 min-h-[46px] px-4 rounded-xl bg-[#079BE8] hover:bg-[#0879E7] text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-md shadow-[#079BE8]/20 transition-colors active:scale-[0.99]"
        >
          <Check size={16} />
          <span>홈에 적용하기</span>
        </button>
      </div>
    </div>
  );
};
