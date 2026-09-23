import React, { useState } from 'react';
import { Menu as MenuIcon, X, Phone } from 'lucide-react';
import { RESTAURANT_INFO } from '../data/restaurantData';

interface HeaderProps {
  onOpenReservation: () => void;
}

export const Header: React.FC<HeaderProps> = ({ onOpenReservation }) => {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const navLinks = [
    { label: '홈', href: '#home' },
    { label: '소개', href: '#about' },
    { label: '대표메뉴', href: '#best-menu' },
    { label: '전체메뉴', href: '#all-menu' },
    { label: '오시는 길', href: '#location' },
  ];

  const handleNavClick = (e: React.MouseEvent<HTMLAnchorElement>, href: string) => {
    e.preventDefault();
    setMobileMenuOpen(false);
    const target = document.querySelector(href);
    if (target) {
      target.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <header className="fixed top-0 left-0 right-0 z-50 bg-[#FCFAF7]/95 backdrop-blur-md shadow-xs border-b border-[#EAE3D9]">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16 sm:h-18">
          {/* Brand Logo - 간판과 동일한 디자인 적용 */}
          <a
            href="#home"
            className="flex flex-col group cursor-pointer focus-visible:outline-hidden"
            onClick={(e) => handleNavClick(e, '#home')}
          >
            <span className="font-black text-lg sm:text-xl tracking-tight text-[#1A2D54]">
              용궁리 김치마을
            </span>
            <span className="text-[10px] sm:text-[11px] text-[#E02828] font-black tracking-wide uppercase -mt-0.5">
              Korean restaurant (세부)
            </span>
          </a>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center gap-7 text-sm font-semibold text-[#54433A]">
            {navLinks.map((link) => (
              <a
                key={link.label}
                href={link.href}
                onClick={(e) => handleNavClick(e, link.href)}
                className="hover:text-[#2C1E18] transition-colors"
              >
                {link.label}
              </a>
            ))}
          </nav>

          {/* Desktop Right Phone CTA */}
          <div className="hidden md:flex items-center gap-3">
            <a
              href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
              className="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-[#8B2E1E] hover:bg-[#722518] transition-colors shadow-xs cursor-pointer"
            >
              <Phone className="w-4 h-4" />
              <span>전화 예약하기</span>
            </a>
          </div>

          {/* Mobile Right: Phone Icon & Hamburger */}
          <div className="flex items-center gap-2 md:hidden">
            <a
              href={`tel:${RESTAURANT_INFO.phone.replace(/[^0-9+]/g, '')}`}
              className="p-2.5 rounded-xl text-white bg-[#8B2E1E] hover:bg-[#722518] transition-colors cursor-pointer shadow-xs"
              aria-label="전화 걸기"
            >
              <Phone className="w-4 h-4" />
            </a>
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="p-2.5 rounded-xl text-[#2C1E18] bg-white border border-[#EAE3D9] hover:bg-[#FAF5ED] transition-colors cursor-pointer"
              aria-label={mobileMenuOpen ? '메뉴 닫기' : '메뉴 열기'}
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <MenuIcon className="w-5 h-5" />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Drawer Menu */}
      {mobileMenuOpen && (
        <div className="md:hidden bg-[#FCFAF7] border-b border-[#EAE3D9] px-5 py-3 shadow-lg">
          <nav className="flex flex-col gap-1">
            {navLinks.map((link) => (
              <a
                key={link.label}
                href={link.href}
                onClick={(e) => handleNavClick(e, link.href)}
                className="px-3 py-2.5 rounded-lg text-sm font-bold text-[#3D2F27] hover:bg-[#F2ECE2] transition-colors"
              >
                {link.label}
              </a>
            ))}
          </nav>
        </div>
      )}
    </header>
  );
};
