import React, { useState } from 'react';
import { Star, ChevronLeft, ChevronRight, ExternalLink, Clock, UtensilsCrossed, Heart, Users } from 'lucide-react';
import { REVIEWS, RESTAURANT_INFO } from '../data/restaurantData';

export const ReviewsSection: React.FC = () => {
  const [currentIndex, setCurrentIndex] = useState(0);

  const prevReview = () => {
    setCurrentIndex((prev) => (prev === 0 ? REVIEWS.length - 1 : prev - 1));
  };

  const nextReview = () => {
    setCurrentIndex((prev) => (prev === REVIEWS.length - 1 ? 0 : prev + 1));
  };

  // Google Reviews URL for Mactan Yonggungri
  const googleReviewSearchUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
    `${RESTAURANT_INFO.brandNameEn} Mactan Cebu`
  )}`;

  return (
    <section id="reviews" className="py-16 sm:py-24 bg-[#FCFAF7] border-b border-[#EAE3D9]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* SECTION 03 — TITLE & SUBTITLE */}
        <div className="text-center max-w-3xl mx-auto mb-10 sm:mb-14">
          <p className="text-xs sm:text-sm font-bold tracking-widest text-[#8B2E1E] uppercase mb-2">
            CUSTOMER REVIEWS
          </p>
          <h2 className="text-2xl sm:text-4xl font-extrabold text-[#2C1E18] tracking-tight">
            다녀오신 분들의 이야기
          </h2>
          <p className="mt-2 text-sm sm:text-base text-[#5E4D43]">
            세부 여행객과 현지 교민들이 직접 경험하고 남겨주신 솔직한 방문 후기입니다.
          </p>
        </div>

        {/* REVIEWS CAROUSEL */}
        {/* Desktop: 3 items simultaneously in a grid / Mobile: 1 item with pagination */}
        
        {/* Desktop View (3 Cards) */}
        <div className="hidden md:grid md:grid-cols-3 gap-6">
          {REVIEWS.slice(0, 3).map((review) => (
            <div
              key={review.id}
              className="bg-white p-6 sm:p-7 rounded-2xl border border-[#EAE3D9] shadow-2xs flex flex-col justify-between hover:border-[#D0C2B0] transition-all"
            >
              <div>
                {/* 5 Stars */}
                <div className="flex items-center gap-1 text-amber-500 mb-4">
                  {[...Array(review.rating)].map((_, i) => (
                    <Star key={i} className="w-4 h-4 fill-amber-400 text-amber-400" />
                  ))}
                </div>

                {/* Review Content */}
                <p className="text-sm text-[#4A3B32] leading-relaxed mb-6">
                  "{review.comment}"
                </p>
              </div>

              {/* Author & Visitor Type */}
              <div className="pt-4 border-t border-[#F0EBE3]">
                <p className="text-sm font-bold text-[#2C1E18]">{review.author}</p>
                <p className="text-xs text-[#7A695F] mt-0.5">{review.travelType}</p>
              </div>
            </div>
          ))}
        </div>

        {/* Mobile View: 1 Card with Touch/Arrows Swipe */}
        <div className="md:hidden">
          <div className="relative">
            <div className="bg-white p-6 rounded-2xl border border-[#EAE3D9] shadow-2xs min-h-[260px] flex flex-col justify-between">
              <div>
                {/* 5 Stars */}
                <div className="flex items-center gap-1 text-amber-500 mb-3">
                  {[...Array(REVIEWS[currentIndex].rating)].map((_, i) => (
                    <Star key={i} className="w-4 h-4 fill-amber-400 text-amber-400" />
                  ))}
                </div>

                {/* Review Content */}
                <p className="text-sm text-[#4A3B32] leading-relaxed mb-6">
                  "{REVIEWS[currentIndex].comment}"
                </p>
              </div>

              {/* Author & Visitor Type */}
              <div className="pt-3 border-t border-[#F0EBE3] flex items-center justify-between">
                <div>
                  <p className="text-sm font-bold text-[#2C1E18]">
                    {REVIEWS[currentIndex].author}
                  </p>
                  <p className="text-xs text-[#7A695F]">{REVIEWS[currentIndex].travelType}</p>
                </div>
                <span className="text-xs text-[#8C7A6F] tabular-nums font-mono">
                  {currentIndex + 1} / {REVIEWS.length}
                </span>
              </div>
            </div>

            {/* Mobile Carousel Controls */}
            <div className="flex items-center justify-center gap-4 mt-5">
              <button
                onClick={prevReview}
                className="w-10 h-10 rounded-full bg-white border border-[#EAE3D9] text-[#2C1E18] flex items-center justify-center active:bg-[#FAF4ED] shadow-2xs cursor-pointer"
                aria-label="이전 후기"
              >
                <ChevronLeft className="w-5 h-5" />
              </button>
              <div className="flex items-center gap-1.5">
                {REVIEWS.map((_, idx) => (
                  <button
                    key={idx}
                    onClick={() => setCurrentIndex(idx)}
                    className={`h-2 rounded-full transition-all cursor-pointer ${
                      currentIndex === idx ? 'w-6 bg-[#2C1E18]' : 'w-2 bg-[#D9CFBF]'
                    }`}
                    aria-label={`${idx + 1}번 후기로 이동`}
                  />
                ))}
              </div>
              <button
                onClick={nextReview}
                className="w-10 h-10 rounded-full bg-white border border-[#EAE3D9] text-[#2C1E18] flex items-center justify-center active:bg-[#FAF4ED] shadow-2xs cursor-pointer"
                aria-label="다음 후기"
              >
                <ChevronRight className="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        {/* CTA: [Google 리뷰 더보기] */}
        <div className="mt-10 sm:mt-12 text-center">
          <a
            href={googleReviewSearchUrl}
            target="_blank"
            rel="noreferrer"
            className="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white hover:bg-[#FAF4ED] text-[#2C1E18] border border-[#D9CFBF] text-xs sm:text-sm font-bold hover:border-[#2C1E18] transition-colors shadow-2xs cursor-pointer"
          >
            <span>Google 리뷰 더보기</span>
            <ExternalLink className="w-3.5 h-3.5 text-[#8B2E1E]" />
          </a>
        </div>

        {/* SECTION 04 — TRUST INFORMATION BAR */}
        <div className="mt-14 sm:mt-18 pt-10 border-t border-[#EAE3D9]">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 max-w-4xl mx-auto">
            <div className="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl bg-[#FAF5ED] border border-[#EAE3D9]">
              <div className="w-9 h-9 rounded-lg bg-white text-[#8B2E1E] flex items-center justify-center shrink-0 shadow-2xs">
                <Clock className="w-4 h-4" />
              </div>
              <div>
                <span className="text-[10px] font-bold text-[#8B2E1E] uppercase tracking-wider block">
                  24 HOURS
                </span>
                <span className="text-xs sm:text-sm font-bold text-[#2C1E18]">
                  24시간 운영
                </span>
              </div>
            </div>

            <div className="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl bg-[#FAF5ED] border border-[#EAE3D9]">
              <div className="w-9 h-9 rounded-lg bg-white text-[#8B2E1E] flex items-center justify-center shrink-0 shadow-2xs">
                <UtensilsCrossed className="w-4 h-4" />
              </div>
              <div>
                <span className="text-[10px] font-bold text-[#8B2E1E] uppercase tracking-wider block">
                  KOREAN FOOD
                </span>
                <span className="text-xs sm:text-sm font-bold text-[#2C1E18]">
                  한식
                </span>
              </div>
            </div>

            <div className="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl bg-[#FAF5ED] border border-[#EAE3D9]">
              <div className="w-9 h-9 rounded-lg bg-white text-[#8B2E1E] flex items-center justify-center shrink-0 shadow-2xs">
                <Heart className="w-4 h-4" />
              </div>
              <div>
                <span className="text-[10px] font-bold text-[#8B2E1E] uppercase tracking-wider block">
                  FAMILY
                </span>
                <span className="text-xs sm:text-sm font-bold text-[#2C1E18]">
                  가족 식사
                </span>
              </div>
            </div>

            <div className="flex items-center gap-3 p-3.5 sm:p-4 rounded-xl bg-[#FAF5ED] border border-[#EAE3D9]">
              <div className="w-9 h-9 rounded-lg bg-white text-[#8B2E1E] flex items-center justify-center shrink-0 shadow-2xs">
                <Users className="w-4 h-4" />
              </div>
              <div>
                <span className="text-[10px] font-bold text-[#8B2E1E] uppercase tracking-wider block">
                  GROUP
                </span>
                <span className="text-xs sm:text-sm font-bold text-[#2C1E18]">
                  단체 식사
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
