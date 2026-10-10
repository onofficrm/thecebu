# 세부어때 모바일 UX/UI 프로토타입 인수인계

## 1. 화면 및 라우팅 목록

이 프로젝트는 React 로컬 상태 기반의 단일 페이지 프로토타입입니다. `src/App.tsx`가 화면 상태와 라우팅을 관리합니다.

- 첫 실행: `OnboardingModeSelectScreen`
- 홈: `ResidentHomeScreen`, `TouristHomeScreen`
- 홈 개인화: `HomeCustomizeScreen`
- 전체 메뉴: `AllMenuScreen`
- 공통 검색: `UniversalSearchScreen`
- 알림: `NotificationScreen`
- MY: `MyPageScreen`
- 관광 혜택: `TouristBenefitsScreen`
- 10개 서비스 목록: `ServiceListView`
  - `news`, `life_info`, `marketplace`, `jobs`, `real_estate`, `chatrooms`
  - `restaurants`, `businesses`, `events`, `coupons`
- 공통 상세: `DetailModal`
- 공유 상세 주소: `#service={serviceId}&id={itemId}`

하단 내비게이션은 모드별 메뉴 구성을 사용하지만, 두 모드 모두 전체 메뉴를 통해 10개 서비스에 접근할 수 있습니다.

## 2. 공통 컴포넌트 목록

- 브랜드·레이아웃: `BrandLogo`, `AppHeader`, `BottomNavigation`
- 모드 전환: `ModeSwitcher`, `ModeSwitchSheet`
- 콘텐츠: `ServiceCard`, `ContentCard`, `SectionHeader`, `CategoryChip`
- 입력·액션: `SearchBar`, `Button`
- 피드백: `ToastMessage`, `ConfirmDialog`, `EmptyState`, `LoadingSkeleton`
- 오버레이: `BottomSheet`, `ModalDialog`, `DetailModal`
- 기능별: `MarketplaceCreateModal`, `EmergencyContactsSheet`, `CebuHelperWidget`
- 인증 연결 준비: `AuthModal`

색상과 타이포그래피 토큰은 `src/theme/tokens.ts`에 있습니다.

## 3. 운영 데이터 연결 구조

타입은 `src/types/index.ts`, 서비스 메타데이터와 운영 데이터 컬렉션은 `src/data/mockData.ts`에서 분리 관리합니다. `src/services/operationalData.ts`가 앱 시작 시 `/api/eottae.php?action=app_services`를 호출해 기존 그누보드 운영 DB의 실제 데이터로 컬렉션을 채웁니다.

- `ServiceMeta`: 10개 서비스 ID, 이름, 아이콘, 모드, 설명
- `NewsItem`, `LifeInfoItem`
- `MarketplaceItem`, `JobItem`, `RealEstateItem`, `ChatroomItem`
- `RestaurantItem`, `BusinessItem`
- `EventItem`, `CouponItem`
- `NotificationItem`, `HomeSectionConfig`

가상 업체·게시글·연락처·운영시간·쿠폰·이벤트 레코드는 없습니다. API는 필리핀뉴스, 생활정보, 중고거래, 구인구직, 부동산, 단톡방, 맛집, 업체, 이벤트, 쿠폰의 실제 운영 데이터를 반환합니다. 로딩 및 API 오류는 앱 공통 상태 화면에서 처리합니다.

## 4. 사용자 모드와 홈 개인화 설정

저장 모듈: `src/utils/storage.ts`

- 현재 모드: `thecebu_user_mode`
- 온보딩 완료: `thecebu_onboarding_completed`
- 교민 바로가기: `thecebu_shortcuts_resident`
- 관광객 바로가기: `thecebu_shortcuts_tourist`

바로가기는 서비스 ID 배열이며 모드별로 독립 저장됩니다. 최대 4개이고 빈 배열도 유효합니다. 잘못된 값, 중복 ID, 4개 초과 값은 저장 모듈에서 정리합니다.

## 5. 운영 API 연결 현황

연결 완료:

- 10개 서비스 목록과 상세 표시
- 운영 회원 세션 요약 및 로그인/MY 링크
- 업체 연락처·영업시간·주소와 OpenStreetMap 기반 앱 내부 지도 연결
- 운영 글쓰기·문의·단톡방·쿠폰함 페이지 연결
- 게시글 번역 토큰과 기존 번역 API 연결

서버 쓰기 정책을 우회하지 않기 위해 글 등록, 판매자 문의, 채용 문의, 단톡방 참여, 쿠폰 발급·사용은 기존 운영 페이지에서 로그인 및 CSRF 검증 후 처리합니다.

### 게시글 번역 연결

상세 화면은 기존 운영 엔드포인트 `/proc/eottae-post-translate.php`를 재사용합니다. 실제 콘텐츠 응답에 아래 메타데이터를 포함하면 `PostTranslationPanel`이 자동으로 표시됩니다.

- `translation.boTable`: 게시판 ID
- `translation.wrId`: 게시글 ID
- `translation.token`: 서버 세션에서 발급한 번역 토큰
- `translation.endpoint`: 선택 항목, 미지정 시 기존 엔드포인트 사용
- `translation.sourceLanguage`: 선택 항목, 기본값 `ko`

지원 언어는 기존 서버와 동일한 영어(`en`), 일본어(`ja`), 중국어(`zh`)입니다. 댓글은 현재 운영 번역 API가 일반 게시글만 허용하므로 연결하지 않았습니다.

홈 개인화와 번역 언어 선택은 비회원도 사용할 수 있도록 현재 브라우저 저장소에 유지됩니다.

## 6. 아직 구현되지 않은 기능

- React 화면 내부에서 직접 처리하는 회원 인증 및 서버 쓰기(기존 운영 페이지로 연결됨)
- React 화면 내부 채팅 송수신·신고·차단(운영 단톡방으로 연결됨)
- 앱 내부 결제·예약·지원서·문의 전송
- GPS 현재 위치 연동(등록 업체 지도는 앱 내부에서 렌더링하며 현재 위치는 후속 연동)
- 서버 검증 QR·바코드·쿠폰 코드
- 실제 이미지 업로드
- 운영용 에러 로깅과 네트워크 재시도
- 푸시 알림 권한과 디바이스 토큰
- 웹 URL 기반 전체 페이지 라우터

## 프로토타입 검증 기능

- 앱 시작 시 운영 API 로딩·오류·재시도 상태 표시
- 필터 결과가 없으면 공통 빈 상태 표시
- 중고거래 글쓰기는 기존 운영 글쓰기 페이지에서 처리
- 단톡방 참여와 쿠폰 발급·사용은 기존 운영 화면에서 처리
- 상세 하트 버튼은 MY 저장 목록과 연결
- 교민·관광객 모드 및 홈 바로가기는 브라우저 저장소에 유지

## 실행 및 검증

```bash
npm run dev
npm run lint
npm run build
```

우선 검증 너비: 360px, 390px, 430px 및 태블릿. 운영 DB와 기존 세부어때 서버에는 영향을 주지 않습니다.
