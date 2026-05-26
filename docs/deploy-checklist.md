# 배포 후 체크리스트 (thecebu)

> `main` 브랜치 push → GitHub Actions FTP 배포 후 운영 확인용.  
> API 키·DB 비밀은 FTP `_site.config.local.php` / 서버 환경변수로 관리 (저장소에 올리지 않음).

---

## 1. 배포 전 (로컬·PR)

- [ ] `git diff`로 의도하지 않은 파일·디버그 코드 없음
- [ ] PHP 문법 오류 없음 (수정한 `.php` 위주)
- [ ] DB 스키마 변경 시 `extend/eottae.extend.php` 훅·`ensure_schema` 호출 경로 확인
- [ ] 참고용 이미지·`.env`·`_site.config.local.php` 커밋 제외

---

## 2. 배포 직후 (공통)

- [ ] GitHub Actions `Deploy to Server` 워크플로우 **성공** (Actions 탭)
- [ ] 홈 `/` — PHP fatal 없음, GNB·하단 메뉴 정상
- [ ] 로그인 / 마이페이지 `/page/eottae-mypage.php` 접근

---

## 3. 기능별 스모크 테스트

### 업체·지도

- [ ] 업체 목록 — 카드 썸네일·**공유하기**(4:4:2)·문의·길찾기
- [ ] 지도 마커 썸네일이 **목록 카드와 동일**한지 (지도 전용 등록 업체 1곳)
- [ ] 업체 등록/수정 — 대표 이미지·**지도 표시 썸네일** 저장
- [ ] 업체 상세 — 갤러리·리뷰 작성

### 캘린더

- [ ] `/page/eottae-calendar.php` — 히어로·**일정 등록** 버튼·월/리스트 전환
- [ ] 히어로 우측 일러스트(건기/우기/연말) 깨짐 없음

### 세부공개단톡방 AI (최고관리자)

- [ ] 마이페이지 → **세부공개단톡방 AI** 카드 — 오늘 발행·OpenAI 호출·마지막 생성
- [ ] `/page/eottae-admin-public-ai.php` — 설정 저장
- [ ] 후보 메시지 승인·발행 (테스트 1건)
- [ ] OpenAI 키: env `PUBLIC_AI_OPENAI_API_KEY` 또는 사이트 설정 우선순위 확인

### 세부톡방·기타

- [ ] 톡방 목록·입장·메시지
- [ ] 홈 공개단체톡 — 어때봇 메시지 노출 (AI ON일 때)

---

## 4. 최근 배포 단위 참고 (커밋 메시지 기준)

| 영역 | 확인 포인트 |
|------|-------------|
| 업체 리스트 공유 | Web Share / 클립보드 복사 |
| 썸네일 정책 | `eottae_shop_card_thumb` = 지도 전용 우선 |
| 공개단톡 AI 마이페이지 | `#sebu-public-ai-admin` 지표·바로가기 |
| 캘린더 히어로 | 계절 장식 `dry` / `wet` / `festive` |

상세 썸네일 규칙: [`docs/eottae-shop-thumbnail-rules.md`](eottae-shop-thumbnail-rules.md)

---

## 5. 문제 발생 시

1. Actions 로그에서 FTP 업로드 실패 여부 확인  
2. 서버 `data/log` 또는 PHP error log 확인  
3. 스키마 누락 시 해당 페이지 1회 방문으로 `ensure_schema` 실행 유도  
4. 롤백: 이전 커밋을 `main`에 revert push (force push 지양)

---

## 6. 운영 메모

- 크론·배치 키: `_deploy_secrets` → `data/eottae-maintenance.local.php` (Actions secret)
- OpenAI·지도 AI 등 API 키는 **서버 `_site.config.local.php` 수동 관리**
- 배포 후 5~10분 캐시/CDN 있으면 강력 새로고침으로 재확인
