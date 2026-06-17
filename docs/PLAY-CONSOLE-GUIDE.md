# Google Play Console 등록 가이드 (세부어때)

Play Console은 **본인 Google 계정**으로만 등록·업로드할 수 있습니다.  
아래 값은 이미 빌드·배포된 TWA 기준으로 채워 두었습니다. 그대로 복사해 사용하세요.

---

## 업로드 파일 위치

| 파일 | 경로 | 용도 |
|------|------|------|
| **AAB** (Play Store) | `~/thecebu-android-twa/app-release-bundle.aab` | 내부 테스트·프로덕션 업로드 |
| **APK** (직접 설치) | `~/Downloads/thecebu-1.0.0.apk` | 실기기 QA |
| **스토어 아이콘** | `img/logo/play-store-icon-512.png` | 512×512 PNG |
| **개인정보처리방침** | https://thecebu.co.kr/page/privacy.php | 스토어·데이터 안전 필수 URL |

> keystore 백업: `android-app/twa/android.keystore` (또는 빌드 시 사용한 복사본)  
> 비밀번호·alias는 `SIGNING.local.md` 또는 안전한 곳에 보관. **분실 시 앱 업데이트 불가.**

---

## 사전 확인 (완료됨)

- [x] Digital Asset Links — Google API 검증 OK  
  `https://digitalassetlinks.googleapis.com/v1/statements:list?source.web.site=https://thecebu.co.kr&relation=delegate_permission/common.handle_all_urls`
- [x] `assetlinks.json` — 200 OK
- [x] PWA manifest — `https://thecebu.co.kr/proc/eottae-pwa-manifest.php`
- [x] AAB 서명 — v1/v2/v3

---

## 1. 개발자 계정

1. https://play.google.com/console 접속
2. **계정 만들기** → 개발자 등록비 **$25** (일회성, 카드 결제)
3. 개발자 프로필·연락처·개인정보처리방침 URL 입력

---

## 2. 앱 만들기

| 항목 | 값 |
|------|-----|
| 앱 이름 | 세부어때 |
| 기본 언어 | 한국어(대한민국) |
| 앱 / 게임 | 앱 |
| 무료 / 유료 | 무료 |

---

## 3. 앱 무결성 (App integrity)

**Play App Signing** 사용 권장 (기본값).

- 첫 AAB 업로드 시 Google이 앱 서명 키를 관리
- 업로드 키(keystore)는 로컬에 반드시 보관

---

## 4. 내부 테스트 트랙에 AAB 업로드

1. **테스트 및 출시** → **내부 테스트** → **새 버전 만들기**
2. `app-release-bundle.aab` 업로드
3. 출시 이름 예: `1.0.0 (2)` — versionName `1.0.0`, versionCode `2`
4. **검토** → **내부 테스트 시작**

### 테스터 추가

- **테스터** 탭 → 이메일 목록 추가 (최대 100명)
- 공유 링크로 설치 URL 받기

### 실기기 확인 (TWA 전체 화면)

1. 기존 APK가 있으면 **삭제** 후 `thecebu-1.0.0.apk` 또는 Play 내부 테스트 링크로 설치
2. 앱 실행 시 **Chrome 주소창이 보이지 않아야** 정상 (Asset Links 연동됨)
3. 주소창이 보이면: `assetlinks.json` 배포·캐시·패키지명 불일치 점검

---

## 5. 스토어 등록정보

**성장** → **스토어 등록정보** → **기본 스토어 등록정보**

### 앱 세부정보

| 항목 | 값 |
|------|-----|
| 앱 이름 | 세부어때 |
| 간단한 설명 (80자) | `android-app/play-store/listing-ko.txt` 참고 |
| 자세한 설명 (4000자) | 동일 파일 참고 |

### 그래픽

| 자산 | 규격 | 파일 |
|------|------|------|
| 앱 아이콘 | 512×512 PNG | `img/logo/play-store-icon-512.png` |
| 스크린샷 (휴대폰) | 2장 이상, 16:9 또는 9:16 | 실기기 캡처 (아래 체크리스트) |
| 기능 그래픽 | 1024×500 JPG/PNG | 선택(권장) — 브랜드 배너 제작 |

### 스크린샷 권장 장면 (최소 4장)

1. 홈 + 하단 탭
2. 내주변(GPS) 업체 목록·지도
3. 커뮤니티 게시글 목록
4. 로그인 / MY 페이지

캡처: Android **전원+볼륨다운** 또는 개발자 옵션 스크린샷.

### 앱 카테고리

| 항목 | 권장값 |
|------|--------|
| 앱 | 예: **소셜** 또는 **여행 및 지역정보** |
| 태그 | 커뮤니티, 세부, 필리핀, 교민, 위치기반 |

### 연락처

| 항목 | 값 |
|------|-----|
| 이메일 | help@thecebu.co.kr |
| 웹사이트 | https://thecebu.co.kr |
| 개인정보처리방침 | https://thecebu.co.kr/page/privacy.php |

---

## 6. 앱 콘텐츠 (필수 설문)

Play Console **정책** → **앱 콘텐츠**에서 각 항목 완료해야 출시 가능.

### 개인정보처리방침

- URL: `https://thecebu.co.kr/page/privacy.php`

### 광고

- 현재 앱에 **광고 없음** → "앱에 광고 포함 안 함"

### 앱 액세스

- 로그인 없이 일부 콘텐츠 열람 가능
- 회원 전용 기능(글쓰기, MY, 톡방 등) 있음  
→ **일부 기능 제한** 선택 후 테스트 계정 제공:

```
테스트 계정: (운영용 테스트 ID 1개 생성 후 기입)
비밀번호: (해당 계정 비밀번호)
로그인 방법: 이메일 또는 Google 소셜 로그인
```

> 실제 심사용 계정을 미리 만들어 두세요.

### 콘텐츠 등급

- IARC 설문 진행 (커뮤니티·UGC·위치 정보 포함)
- 폭력·성적 콘텐츠 없음, 사용자 생성 콘텐츠 있음 → 해당 항목 **예** 선택 후 신고·차단 기능 설명

### 타겟츠 및 콘텐츠

- **만 13세 이상** (또는 실제 정책에 맞게)
- 어린이 대상 아님

### 데이터 보안 (Data safety)

`android-app/play-store/data-safety-ko.md` 참고.

요약:

- 수집: 계정 정보(이메일, 닉네임), 위치(내주변), 게시·메시지(사용자 입력)
- 목적: 앱 기능, 계정 관리
- 전송 중 암호화: **예** (HTTPS)
- 삭제 요청: 이메일 또는 MY에서 탈퇴

### 정부 앱 / 금융 등

- 해당 없음 → 모두 **아니오**

---

## 7. 출시 순서

```
내부 테스트 (본인·팀 QA)
    ↓
오픈 테스트 (선택, 더 넓은 베타)
    ↓
프로덕션 (전체 공개)
```

프로덕션 첫 출시 시 **Google 검토** 1~7일 소요될 수 있습니다.

---

## 8. 출시 후

| 작업 | 방법 |
|------|------|
| 웹만 수정 | 서버 배포 → 앱 재설치 없이 반영 |
| 앱 버전 올리기 | `twa-manifest.json`의 `appVersionCode` +1 → `bubblewrap build` → 새 AAB 업로드 |
| 키스토어 분실 방지 | keystore + 비밀번호 오프라인 백업 |

### 버전 업데이트 빌드

```bash
export BUBBLEWRAP_KEYSTORE_PASSWORD='(비밀번호)'
export BUBBLEWRAP_KEY_PASSWORD='(비밀번호)'
cd ~/thecebu-android-twa
# twa-manifest.json 에서 appVersionCode 를 3, 4, ... 로 증가
bubblewrap build
```

---

## 9. 실기기 QA 체크리스트 (업로드 전 권장)

- [ ] 홈(/) + 하단 탭
- [ ] 로그인 / 회원가입 / Google OAuth
- [ ] 세션 유지 (앱 종료 후 재실행)
- [ ] 내주변 GPS → 업체 목록
- [ ] 커뮤니티·칼럼·톡방
- [ ] MY·쪽지
- [ ] TWA 전체 화면 (주소창 없음)

---

## 관련 문서

- [ANDROID-APP-GUIDE.md](ANDROID-APP-GUIDE.md)
- [android-app/play-store/listing-ko.txt](../android-app/play-store/listing-ko.txt)
- [android-app/play-store/data-safety-ko.md](../android-app/play-store/data-safety-ko.md)
