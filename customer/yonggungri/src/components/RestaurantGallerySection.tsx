import React, { useState } from 'react';
import { X, ZoomIn } from 'lucide-react';

interface GalleryItem {
  id: string;
  src: string;
  alt: string;
  isMain?: boolean;
}

export const RestaurantGallerySection: React.FC = () => {
  const [activePhoto, setActivePhoto] = useState<GalleryItem | null>(null);

  const galleryItems: GalleryItem[] = [
    {
      id: 'main-interior',
      src: '/assets/images/gallery_large_main_1790145353334.jpg',
      alt: '용궁리 김치마을 식당 내부 메인 홀 전경',
      isMain: true,
    },
    {
      id: 'family-dining',
      src: '/assets/images/gallery_family_dining_1790145372365.jpg',
      alt: '용궁리 가족 식사 분위기',
    },
    {
      id: 'friends-table',
      src: '/assets/images/gallery_friends_table_1790145387479.jpg',
      alt: '친구들과 함께하는 한식 식사 테이블',
    },
    {
      id: 'group-room',
      src: '/assets/images/gallery_group_room_1790145400488.jpg',
      alt: '단체 손님을 위한 쾌적한 프라이빗 룸',
    },
    {
      id: 'late-night',
      src: '/assets/images/gallery_late_night_1790145410859.jpg',
      alt: '늦은 시간에도 편안하게 즐기는 식사 분위기',
    },
    {
      id: 'side-banchan',
      src: '/assets/images/gallery_side_banchan_1790145423948.jpg',
      alt: '정갈하게 차려진 기본 상차림과 수제 밑반찬',
    },
  ];

  return (
    <section id="experience" className="py-16 sm:py-24 bg-[#FCFAF7] border-b border-[#EAE3D9]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* SECTION 01 — RESTAURANT GALLERY HEADER */}
        <div className="text-center max-w-3xl mx-auto mb-10 sm:mb-14">
          <p className="text-xs sm:text-sm font-bold tracking-widest text-[#8B2E1E] uppercase mb-2">
            YONGGUNGRI EXPERIENCE
          </p>
          <h2 className="text-2xl sm:text-4xl font-extrabold text-[#2C1E18] tracking-tight">
            직접 만나보세요
          </h2>
          <p className="mt-2 text-sm sm:text-base text-[#5E4D43]">
            넓고 쾌적한 홀, 프라이빗 독립 룸, 그리고 따뜻한 식사 분위기
          </p>
        </div>

        {/* EDITORIAL GALLERY LAYOUT: 1 Large Main + 5 Sub Images (No text overlays on photos) */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-4">
          {/* 1 Large Main Image (lg:col-span-7) */}
          <div
            onClick={() => setActivePhoto(galleryItems[0])}
            className="lg:col-span-7 aspect-16/10 rounded-2xl overflow-hidden bg-stone-200 cursor-pointer group relative shadow-xs border border-[#EAE3D9]"
          >
            <img
              src={galleryItems[0].src}
              alt={galleryItems[0].alt}
              className="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500 ease-out"
              loading="lazy"
            />
            <div className="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors flex items-center justify-center">
              <div className="w-10 h-10 rounded-full bg-white/90 text-[#2C1E18] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow-md">
                <ZoomIn className="w-5 h-5" />
              </div>
            </div>
          </div>

          {/* 5 Smaller Sub Images (lg:col-span-5 with responsive grid) */}
          <div className="lg:col-span-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 gap-3 sm:gap-4">
            {galleryItems.slice(1).map((item) => (
              <div
                key={item.id}
                onClick={() => setActivePhoto(item)}
                className="aspect-4/3 rounded-xl overflow-hidden bg-stone-200 cursor-pointer group relative shadow-2xs border border-[#EAE3D9]"
              >
                <img
                  src={item.src}
                  alt={item.alt}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                  loading="lazy"
                />
                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/25 transition-colors flex items-center justify-center">
                  <div className="w-8 h-8 rounded-full bg-white/90 text-[#2C1E18] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center shadow-sm">
                    <ZoomIn className="w-4 h-4" />
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* LIGHTBOX MODAL */}
        {activePhoto && (
          <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-in fade-in duration-200"
            onClick={() => setActivePhoto(null)}
          >
            <div
              className="relative max-w-4xl w-full max-h-[90vh] flex flex-col items-center justify-center"
              onClick={(e) => e.stopPropagation()}
            >
              <button
                onClick={() => setActivePhoto(null)}
                className="absolute -top-12 right-0 sm:right-0 w-10 h-10 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center transition-colors cursor-pointer"
                aria-label="닫기"
              >
                <X className="w-6 h-6" />
              </button>
              <img
                src={activePhoto.src}
                alt={activePhoto.alt}
                className="w-full h-auto max-h-[82vh] object-contain rounded-2xl shadow-2xl"
              />
              <p className="text-white/80 text-xs sm:text-sm mt-3 font-medium">
                {activePhoto.alt}
              </p>
            </div>
          </div>
        )}
      </div>
    </section>
  );
};
