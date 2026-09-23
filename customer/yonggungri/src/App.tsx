import { useState } from 'react';
import { Header } from './components/Header';
import { Hero } from './components/Hero';
import { AboutSection } from './components/AboutSection';
import { BestMenuSection } from './components/BestMenuSection';
import { MenuSection } from './components/MenuSection';
import { LocationSection } from './components/LocationSection';
import { Footer } from './components/Footer';
import { ReservationModal } from './components/ReservationModal';
import { MenuDetailModal } from './components/MenuDetailModal';
import { MobileQuickBar } from './components/MobileQuickBar';
import { MenuItem } from './types';

export default function App() {
  const [reservationModalOpen, setReservationModalOpen] = useState(false);
  const [selectedMenuItem, setSelectedMenuItem] = useState<MenuItem | null>(null);

  const scrollToSection = (sectionId: string) => {
    const el = document.getElementById(sectionId);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <div className="min-h-screen bg-[#FCFAF7] text-[#2C2420] flex flex-col font-sans selection:bg-[#2C1E18] selection:text-white">
      {/* 1. 고정 헤더 */}
      <Header onOpenReservation={() => setReservationModalOpen(true)} />

      {/* 2. 간결하고 명료한 핵심 메인 흐름: Hero → About → 대표 메뉴 → 전체 메뉴 → 오시는 길 */}
      <main className="flex-1">
        {/* 히어로: 24시간 한식당 안내 및 핵심 행동 버튼 */}
        <Hero
          onOpenReservation={() => setReservationModalOpen(true)}
          onScrollToMenu={() => scrollToSection('best-menu')}
          onScrollToLocation={() => scrollToSection('location')}
        />

        {/* 식당 소개: 세부에서 한국 음식이 생각날 때 */}
        <AboutSection />

        {/* 대표 메뉴: 김치찌개, 예산장터국밥, 뼈다귀해장국, 닭볶음탕 */}
        <BestMenuSection
          onSelectMenuItem={(item) => setSelectedMenuItem(item)}
          onScrollToAllMenu={() => scrollToSection('all-menu')}
        />

        {/* 전체 메뉴 안내: 12대 핵심 메뉴 및 가격 */}
        <MenuSection
          onOpenReservation={() => setReservationModalOpen(true)}
          onSelectMenuItem={(item) => setSelectedMenuItem(item)}
        />

        {/* 용궁리 오시는 길: Google Maps 길찾기, 그랩 키워드 복사, 주소/전화/영업시간 안내 */}
        <LocationSection
          onOpenReservation={() => setReservationModalOpen(true)}
          onOpenGroupReservation={() => setReservationModalOpen(true)}
        />
      </main>

      {/* 3. 하단 매장 안내 & 바로가기 푸터 (빨간 동그라미 친 영역) */}
      <Footer
        onOpenReservation={() => setReservationModalOpen(true)}
        onScrollToMenu={() => scrollToSection('best-menu')}
        onScrollToLocation={() => scrollToSection('location')}
      />

      {/* 4. 모바일 하단 빠른 실행 바 (전화 / 길찾기 / 메뉴) */}
      <MobileQuickBar
        onOpenReservation={() => setReservationModalOpen(true)}
        onScrollToMenu={() => scrollToSection('best-menu')}
        onScrollToLocation={() => scrollToSection('location')}
      />

      {/* 5. 예약 모달 */}
      <ReservationModal
        isOpen={reservationModalOpen}
        onClose={() => setReservationModalOpen(false)}
      />

      {/* 6. 메뉴 상세 팝업 모달 */}
      <MenuDetailModal
        item={selectedMenuItem}
        onClose={() => setSelectedMenuItem(null)}
        onReserve={() => {
          setSelectedMenuItem(null);
          setReservationModalOpen(true);
        }}
      />
    </div>
  );
}
