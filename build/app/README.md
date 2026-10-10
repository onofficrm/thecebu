# 세부어때 모바일 앱 UX/UI — 1단계

운영 서버 및 DB와 분리된 React + TypeScript 모바일 UI 프로토타입입니다.

## 실행

```bash
npm install
npm run dev
```

브라우저에서 `http://localhost:3000`을 열면 360px, 390px, 430px 미리보기와 교민/관광객 모드를 확인할 수 있습니다.

## 구조

- `src/components/common`: 헤더, 내비게이션, 버튼, 카드, 모달, 시트 등 공통 UI
- `src/screens`: 교민 홈, 관광객 홈, 전체메뉴, 검색, MY 기본 화면
- `src/data/mockData.ts`: 서비스 메타데이터와 운영 API 연결 전 빈 데이터 컬렉션
- `src/theme/tokens.ts`: 브랜드 컬러, 라운드, 터치 영역 토큰
- `public/assets/brand/cebu-logo-main.png`: 운영 사이트에서 가져온 공식 로고 원본

실제 API, 운영 DB 및 별도 회원 시스템은 연결하지 않습니다. 가상 업체·게시글·쿠폰 데이터는 포함하지 않으며, API 연결 전에는 빈 상태로 표시됩니다.
