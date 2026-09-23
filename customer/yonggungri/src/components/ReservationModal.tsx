import React, { useState } from 'react';
import { X, Phone, MessageCircle, Calendar, Clock, Users, CheckCircle, ShieldCheck } from 'lucide-react';
import { RESTAURANT_INFO } from '../data/restaurantData';

interface ReservationModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export const ReservationModal: React.FC<ReservationModalProps> = ({ isOpen, onClose }) => {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [date, setDate] = useState(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
  });
  const [time, setTime] = useState('18:00');
  const [guests, setGuests] = useState('4');
  const [roomType, setRoomType] = useState<'hall' | 'private_room'>('private_room');
  const [requests, setRequests] = useState('');
  const [submitted, setSubmitted] = useState(false);

  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !phone.trim()) return;
    setSubmitted(true);
  };

  const resetForm = () => {
    setSubmitted(false);
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-in fade-in duration-150">
      <div
        className="relative w-full max-w-lg bg-[#FCFAF7] rounded-2xl shadow-2xl border border-[#EAE3D9] overflow-hidden max-h-[92vh] flex flex-col"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Modal Header */}
        <div className="p-5 sm:p-6 bg-[#2C1E18] text-white flex items-center justify-between">
          <div>
            <span className="text-xs font-semibold text-amber-300 block mb-0.5">
              RESERVATION & INQUIRY
            </span>
            <h3 className="text-lg sm:text-xl font-bold">
              {RESTAURANT_INFO.brandNameKo} 예약 신청
            </h3>
          </div>
          <button
            onClick={onClose}
            className="p-2 rounded-full hover:bg-white/10 text-white transition-colors cursor-pointer"
            aria-label="닫기"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Modal Body */}
        <div className="p-5 sm:p-6 overflow-y-auto space-y-6">
          {submitted ? (
            <div className="py-8 text-center space-y-4">
              <div className="w-14 h-14 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto">
                <CheckCircle className="w-8 h-8" />
              </div>
              <div>
                <h4 className="text-xl font-bold text-[#2C1E18]">예약 접수가 완료되었습니다</h4>
                <p className="text-sm text-[#5E4D43] mt-2 max-w-sm mx-auto leading-relaxed">
                  남겨주신 연락처(<span className="font-semibold text-[#2C1E18]">{phone}</span>)로
                  용궁리 매장에서 즉시 확인 후 예약 확정 안내를 드립니다.
                </p>
              </div>

              <div className="p-4 rounded-xl bg-[#FAF5ED] border border-[#EAE3D9] text-left text-xs text-[#5E4D43] space-y-1.5 max-w-md mx-auto">
                <p>
                  <span className="font-bold text-[#2C1E18]">예약자:</span> {name}님
                </p>
                <p>
                  <span className="font-bold text-[#2C1E18]">일시:</span> {date} {time}
                </p>
                <p>
                  <span className="font-bold text-[#2C1E18]">인원 / 좌석:</span> {guests}명 /{' '}
                  {roomType === 'private_room' ? '프라이빗 룸' : '쾌적한 일반 홀석'}
                </p>
                {requests && (
                  <p>
                    <span className="font-bold text-[#2C1E18]">요청사항:</span> {requests}
                  </p>
                )}
              </div>

              <div className="pt-2">
                <button
                  onClick={resetForm}
                  className="w-full py-3 px-4 rounded-xl bg-[#2C1E18] text-white font-medium text-sm hover:bg-[#1E140F] transition-colors cursor-pointer"
                >
                  확인 완료
                </button>
              </div>
            </div>
          ) : (
            <>
              {/* Instant Direct Contact Options */}
              <div className="grid grid-cols-2 gap-3">
                <a
                  href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
                  className="p-3 rounded-xl bg-white border border-[#EAE3D9] hover:border-[#D9CFBF] text-center flex flex-col items-center justify-center gap-1 transition-all cursor-pointer group"
                >
                  <div className="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#8B2E1E] flex items-center justify-center group-hover:scale-105 transition-transform">
                    <Phone className="w-4 h-4" />
                  </div>
                  <span className="text-xs font-bold text-[#2C1E18]">전화 바로 연결</span>
                  <span className="text-[11px] text-[#7A695F] tabular-nums">
                    {RESTAURANT_INFO.phoneDisplay}
                  </span>
                </a>

                <div className="p-3 rounded-xl bg-white border border-[#EAE3D9] hover:border-[#D9CFBF] text-center flex flex-col items-center justify-center gap-1 transition-all">
                  <div className="w-8 h-8 rounded-full bg-[#FEE500]/30 text-[#3C1E1E] flex items-center justify-center">
                    <MessageCircle className="w-4 h-4" />
                  </div>
                  <span className="text-xs font-bold text-[#2C1E18]">카카오톡 ID 문의</span>
                  <span className="text-[11px] text-[#7A695F] font-mono">
                    {RESTAURANT_INFO.kakaoId}
                  </span>
                </div>
              </div>

              <div className="relative flex items-center justify-center">
                <div className="absolute inset-0 flex items-center">
                  <div className="w-full border-t border-[#EAE3D9]" />
                </div>
                <span className="relative bg-[#FCFAF7] px-3 text-xs text-[#8C7A6F]">
                  또는 온라인 예약 신청서 작성
                </span>
              </div>

              {/* Form */}
              <form onSubmit={handleSubmit} className="space-y-4 text-left">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-bold text-[#4A3B32] mb-1">
                      예약자 성함 <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      required
                      placeholder="홍길동"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      className="w-full px-3.5 py-2.5 rounded-lg border border-[#D9CFBF] bg-white text-sm text-[#2C1E18] focus:border-[#2C1E18] focus:outline-hidden"
                    />
                  </div>

                  <div>
                    <label className="block text-xs font-bold text-[#4A3B32] mb-1">
                      연락처(카톡ID / 전화) <span className="text-red-500">*</span>
                    </label>
                    <input
                      type="text"
                      required
                      placeholder="010-0000-0000 또는 카톡ID"
                      value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      className="w-full px-3.5 py-2.5 rounded-lg border border-[#D9CFBF] bg-white text-sm text-[#2C1E18] focus:border-[#2C1E18] focus:outline-hidden"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-3 gap-3">
                  <div className="col-span-1">
                    <label className="block text-xs font-bold text-[#4A3B32] mb-1">
                      방문 날짜
                    </label>
                    <div className="relative">
                      <input
                        type="date"
                        required
                        value={date}
                        onChange={(e) => setDate(e.target.value)}
                        className="w-full px-2.5 py-2 rounded-lg border border-[#D9CFBF] bg-white text-xs sm:text-sm text-[#2C1E18] focus:border-[#2C1E18] focus:outline-hidden"
                      />
                    </div>
                  </div>

                  <div className="col-span-1">
                    <label className="block text-xs font-bold text-[#4A3B32] mb-1">
                      방문 시간
                    </label>
                    <input
                      type="time"
                      required
                      value={time}
                      onChange={(e) => setTime(e.target.value)}
                      className="w-full px-2.5 py-2 rounded-lg border border-[#D9CFBF] bg-white text-xs sm:text-sm text-[#2C1E18] focus:border-[#2C1E18] focus:outline-hidden"
                    />
                  </div>

                  <div className="col-span-1">
                    <label className="block text-xs font-bold text-[#4A3B32] mb-1">
                      인원수
                    </label>
                    <select
                      value={guests}
                      onChange={(e) => setGuests(e.target.value)}
                      className="w-full px-2.5 py-2 rounded-lg border border-[#D9CFBF] bg-white text-xs sm:text-sm text-[#2C1E18] focus:border-[#2C1E18] focus:outline-hidden"
                    >
                      <option value="1">1인 (혼밥)</option>
                      <option value="2">2인</option>
                      <option value="4">3~4인 (가족)</option>
                      <option value="6">5~6인</option>
                      <option value="10">7~10인</option>
                      <option value="15">11~20인 (단체)</option>
                      <option value="25">20인 이상 (대형)</option>
                    </select>
                  </div>
                </div>

                {/* Seat Preference */}
                <div>
                  <label className="block text-xs font-bold text-[#4A3B32] mb-1.5">
                    선호 좌석
                  </label>
                  <div className="grid grid-cols-2 gap-2">
                    <button
                      type="button"
                      onClick={() => setRoomType('private_room')}
                      className={`py-2 px-3 rounded-lg text-xs font-medium border text-center transition-all cursor-pointer ${
                        roomType === 'private_room'
                          ? 'border-[#2C1E18] bg-[#2C1E18] text-white'
                          : 'border-[#D9CFBF] bg-white text-[#5E4D43] hover:bg-[#FAF5ED]'
                      }`}
                    >
                      프라이빗 독립 룸
                    </button>
                    <button
                      type="button"
                      onClick={() => setRoomType('hall')}
                      className={`py-2 px-3 rounded-lg text-xs font-medium border text-center transition-all cursor-pointer ${
                        roomType === 'hall'
                          ? 'border-[#2C1E18] bg-[#2C1E18] text-white'
                          : 'border-[#D9CFBF] bg-white text-[#5E4D43] hover:bg-[#FAF5ED]'
                      }`}
                    >
                      쾌적한 일반 홀석
                    </button>
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold text-[#4A3B32] mb-1">
                    요청사항 (선택)
                  </label>
                  <textarea
                    rows={2}
                    placeholder="유아용 식탁의자 필요, 픽업 문의, 선호 메뉴 등"
                    value={requests}
                    onChange={(e) => setRequests(e.target.value)}
                    className="w-full px-3 py-2 rounded-lg border border-[#D9CFBF] bg-white text-xs sm:text-sm text-[#2C1E18] focus:border-[#2C1E18] focus:outline-hidden"
                  />
                </div>

                <button
                  type="submit"
                  className="w-full py-3.5 px-4 rounded-xl bg-[#2C1E18] hover:bg-[#1E140F] text-white font-bold text-sm transition-transform active:scale-[0.98] shadow-sm cursor-pointer"
                >
                  예약 신청 접수하기
                </button>
              </form>

              <div className="flex items-center gap-1.5 text-[11px] text-[#8C7A6F] justify-center">
                <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
                <span>입력하신 정보는 매장 예약 확인 목적 외에 사용되지 않습니다.</span>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
};
