import React, { useEffect, useState } from 'react';
import { CloudRain, Sun, Calculator, ChevronRight } from 'lucide-react';
import { AppMode } from '../../types';
import { TodayInfo } from '../../services/operationalData';

interface CebuHelperWidgetProps {
  mode: AppMode;
  onOpenEmergencySheet?: () => void;
  todayInfo?: TodayInfo | null;
}

export const CebuHelperWidget: React.FC<CebuHelperWidgetProps> = ({
  mode,
  onOpenEmergencySheet,
  todayInfo,
}) => {
  const [phpInput, setPhpInput] = useState<string>('100');
  const [krwRateInput, setKrwRateInput] = useState<string>('');
  const [showCalculator, setShowCalculator] = useState(false);
  const weather = todayInfo?.weather;
  const exchange = todayInfo?.exchange;

  useEffect(() => {
    if (exchange?.krwPerPhp) {
      setKrwRateInput(String(exchange.krwPerPhp));
    }
  }, [exchange?.krwPerPhp]);

  const numPhp = parseFloat(phpInput) || 0;
  const krwRate = parseFloat(krwRateInput) || 0;
  const calculatedKrw = Math.round(numPhp * krwRate);

  return (
    <div className="bg-white border border-[#E1ECF3] rounded-2xl p-3.5 mb-4 shadow-2xs">
      {/* Top row: Weather & Exchange Rate strip */}
      <div className="flex items-center justify-between pb-3 border-b border-[#ECF2F6]">
        {/* Weather */}
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 rounded-xl bg-[#EAF8FF] text-[#079BE8] flex items-center justify-center">
            {weather && weather.rainChance >= 40 ? <CloudRain size={18} /> : <Sun size={18} />}
          </div>
          <div>
            <div className="flex items-baseline gap-1.5 font-mono">
              <span className="text-sm font-extrabold text-[#183247]">
                {weather?.currentTemp !== null && weather?.currentTemp !== undefined
                  ? `${weather.currentTemp}°C`
                  : '--°C'}
              </span>
              {weather?.summary && (
                <span className="max-w-16 truncate text-[10px] font-bold text-[#079BE8]">
                  {weather.summary}
                </span>
              )}
            </div>
            <span className="text-[10px] text-[#617789] font-medium block">
              {weather
                ? `${weather.region} · 비 ${weather.rainChance}%`
                : '날씨 정보를 불러오는 중'}
            </span>
          </div>
        </div>

        {/* Vertical divider */}
        <div className="h-6 w-[1px] bg-[#ECF2F6]" />

        {/* Currency summary */}
        <div className="text-right">
          <div className="flex items-center justify-end gap-1">
            <span className="text-[11px] text-[#183247] font-bold">
              {exchange
                ? `₱1 = ₩${exchange.krwPerPhp.toLocaleString('ko-KR', {
                    maximumFractionDigits: 2,
                  })}`
                : '환율 정보를 불러오는 중'}
            </span>
          </div>
          <button
            type="button"
            onClick={() => setShowCalculator(!showCalculator)}
            className="text-[10px] font-bold text-[#079BE8] hover:underline inline-flex items-center gap-0.5 mt-0.5"
          >
            <Calculator size={11} />
            <span>{showCalculator ? '계산기 접기' : '페소 간편 계산기'}</span>
          </button>
        </div>
      </div>

      {/* Expandable Quick Peso Calculator */}
      {showCalculator && (
        <div className="pt-3 pb-1 border-b border-[#ECF2F6] animate-in fade-in duration-200">
          <div className="space-y-2 bg-[#F7FBFE] p-2.5 rounded-xl border border-[#E1ECF3]">
            <label className="flex items-center gap-2 text-[11px] font-semibold text-[#617789]">
              <span className="w-20 shrink-0">1 PHP 환율</span>
              <input
                type="number"
                min="0"
                step="0.01"
                value={krwRateInput}
                onChange={(e) => setKrwRateInput(e.target.value)}
                placeholder="현재 원화 환율"
                className="h-9 min-w-0 flex-1 rounded-lg border border-[#E1ECF3] bg-white px-2.5 text-xs font-mono font-bold text-[#183247] focus:outline-none"
              />
            </label>
            <div className="flex items-center gap-2">
            <div className="flex-1 flex items-center bg-white border border-[#E1ECF3] rounded-lg px-2.5 h-9">
              <span className="text-xs font-bold text-[#075A9D] mr-1">₱</span>
              <input
                type="number"
                min="0"
                value={phpInput}
                onChange={(e) => setPhpInput(e.target.value)}
                placeholder="100"
                className="w-full text-xs font-mono font-bold text-[#183247] focus:outline-none"
              />
            </div>
            <span className="text-xs text-[#8799A8] font-bold">=</span>
            <div className="flex-1 bg-white border border-[#E1ECF3] rounded-lg px-2.5 h-9 flex items-center justify-end font-mono">
              <span className="text-xs font-bold text-[#079BE8] tabular-nums">
                {krwRate > 0 ? `약 ${calculatedKrw.toLocaleString()} 원` : '환율 입력 필요'}
              </span>
            </div>
            </div>
          </div>
          <p className="text-[10px] text-[#8799A8] mt-1.5 text-center">
            기준환율을 자동 적용하며 실제 환전 금액과 수수료는 다를 수 있습니다.
          </p>
        </div>
      )}

      {/* Bottom Emergency / Contact Quick Bar */}
      <div className="pt-2.5 flex items-center justify-between">
        {mode === 'resident' ? (
          <div className="flex items-center gap-1.5 text-[11px] text-[#617789]">
            <span className="w-1.5 h-1.5 rounded-full bg-[#079BE8]" />
            <span>긴급 연락처는 비상연락망에서 확인하세요.</span>
          </div>
        ) : (
          <div className="flex items-center gap-1.5 text-[11px] text-[#617789]">
            <span className="w-1.5 h-1.5 rounded-full bg-[#00C5E8]" />
            <span>세부 여행 팁: 그랩(Grab) 택시 이용 시 잔돈 준비 권장</span>
          </div>
        )}

        {onOpenEmergencySheet && (
          <button
            type="button"
            onClick={onOpenEmergencySheet}
            className="text-[11px] font-bold text-[#075A9D] hover:text-[#079BE8] inline-flex items-center shrink-0"
          >
            <span>비상연락망</span>
            <ChevronRight size={12} />
          </button>
        )}
      </div>
    </div>
  );
};
