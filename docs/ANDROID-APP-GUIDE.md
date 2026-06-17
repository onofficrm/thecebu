# Android 앱 가이드 (세부어때)

세부어때(thecebu.co.kr)를 **Android 앱**으로 출시하기 위한 단계별 로드맵입니다.

## 권장 방식: TWA (Trusted Web Activity)

현재 사이트는 PHP/그누보드 기반 **모바일 웹**이 이미 잘 갖춰져 있습니다(하단 탭, 내주변 GPS, 톡방, MY).  
전체를 Kotlin/Flutter로 다시 만들기보다, **Play Store용 TWA 껍데기**로 `https://thecebu.co.kr`을 감싸는 방식이 가장 빠르고 유지보수도 쉽습니다.

| 방식 | 장점 | 단점 |
|------|------|------|
| **TWA (권장)** | 서버 배포만으로 앱 내용 갱신, 개발 기간 짧음 | 푸시·일부 네이티브 기능 제한 |
| Capacitor WebView | 네이티브 플러그인 확장 용이 | 브릿지·빌드 파이프라인 추가 |
| 네이티브 재개발 | 최고 UX | 수개월 규모, API 전면 정비 필요 |

---

## 전체 단계

| 단계 | 내용 | 상태 |
|------|------|------|
| **1** | PWA 기반 (manifest, theme-color, 메타) | ✅ 배포 완료 |
| **2** | Android 실기기 웹 QA (로그인·GPS·톡방) | ⬜ APK 설치 후 테스트 |
| **3** | TWA 프로젝트 생성 (Bubblewrap) | ✅ `android-app/twa/` |
| **4** | Digital Asset Links (`assetlinks.json`) | ✅ 배포 완료 |
| **5** | Play Console 등록·내부 테스트 | ⬜ [PLAY-CONSOLE-GUIDE.md](PLAY-CONSOLE-GUIDE.md) |
| **6** | (선택) 푸시·딥링크·스플래시 고도화 | ⬜ |

---

## 1단계 — PWA 기반 (완료)

### 추가된 파일

| 파일 | 역할 |
|------|------|
| `lib/eottae-pwa.lib.php` | manifest 데이터·head 태그 |
| `proc/eottae-pwa-manifest.php` | Web App Manifest JSON |
| `.well-known/assetlinks.json.example` | TWA 4단계용 샘플 |
| `_site.config.php` | PWA/Android 설정 키 |

### 확인 URL (배포 후)

```
https://thecebu.co.kr/proc/eottae-pwa-manifest.php
```

Chrome DevTools → **Application → Manifest** 에서 name, icons, theme_color 확인.

### 아이콘 준비 (Play Store / TWA)

512×512 PNG를 서버에 업로드:

```
/img/logo/android-chrome-512x512.png
```

`_site.config.php`의 `pwa_icon_512_path`로 지정되어 있습니다.  
없으면 `apple-touch-icon.png`·`logo_path`가 fallback으로 사용됩니다.

---

## 2단계 — Android 실기기 웹 QA

**Play Store 패키징 전** 실제 Android Chrome에서 아래를 테스트합니다.

### 체크리스트

- [ ] 홈(`/`) React 빌더 + 하단 탭 정상
- [ ] 로그인 / 회원가입 / Google·소셜 OAuth
- [ ] 로그인 세션 유지 (앱 재실행 후)
- [ ] **내주변** GPS 권한 → 업체 목록·지도
- [ ] 커뮤니티·칼럼·톡방 채팅
- [ ] MY 페이지·쪽지·알림
- [ ] 결제(PG) 사용 시 — TWA 내 결제창 동작 (해당 기능 출시 시)

### 알려진 주의점

- TWA 앱에서 GPS가 안 되면: `DelegationService`가 켜져 있어야 함 (`enableLocationDelegation: true`, `build.gradle`의 `enableDelegation`). 앱 **1.0.1** 이상으로 재설치 후 위치 권한 허용.
- 홈 빌더 React `assets/` 번들이 서버에 없으면 홈이 비어 보일 수 있음 → 빌더 ZIP 재업로드
- OAuth redirect URI는 **운영 도메인**만 등록 (`https://thecebu.co.kr/...`)
- `G5_COOKIE_DOMAIN`, HTTPS 설정 확인

---

## 3단계 — TWA 프로젝트 (Bubblewrap)

로컬 PC에 [Node.js 18+](https://nodejs.org/)와 [JDK 17](https://adoptium.net/) 설치.

```bash
npm install -g @bubblewrap/cli
mkdir -p android-app && cd android-app
bubblewrap init --manifest=https://thecebu.co.kr/proc/eottae-pwa-manifest.php
```

초기화 시 입력 예:

| 항목 | 값 |
|------|-----|
| Domain | `thecebu.co.kr` |
| URL path | `/` |
| Application name | `세부어때` |
| Package name | `kr.co.thecebu.app` |
| Signing key | 새 keystore 생성 (Play 업로드용 보관) |

빌드:

```bash
bubblewrap build
```

산출물: `app-release-signed.apk` / `.aab`

샘플 설정: [`android-app/twa-config.example.json`](twa-config.example.json)

---

## 4단계 — Digital Asset Links

TWA가 전체 화면(주소창 없음)으로 동작하려면 서버에 아래 파일 필요:

```
https://thecebu.co.kr/.well-known/assetlinks.json
```

1. Bubblewrap keystore SHA256 확인:

```bash
keytool -list -v -keystore android.keystore -alias android
```

2. `.well-known/assetlinks.json.example` 복사 → `assetlinks.json`
3. `package_name`, `sha256_cert_fingerprints` 교체
4. FTP/배포 후 검증:

```
https://digitalassetlinks.googleapis.com/v1/statements:list?source.web.site=https://thecebu.co.kr&relation=delegate_permission/common.handle_all_urls
```

---

## 5단계 — Google Play Console

**상세 절차:** [PLAY-CONSOLE-GUIDE.md](PLAY-CONSOLE-GUIDE.md)  
**스토어 문구:** [android-app/play-store/listing-ko.txt](../android-app/play-store/listing-ko.txt)

1. [Google Play Console](https://play.google.com/console) 개발자 계정 ($25 일회)
2. **앱 만들기** → `kr.co.thecebu.app`
3. **AAB 업로드:** `~/thecebu-android-twa/app-release-bundle.aab` (내부 테스트)
4. 스토어 등록정보 + 스크린샷 + `img/logo/play-store-icon-512.png`
5. 앱 콘텐츠(데이터 보안, 콘텐츠 등급, 앱 액세스) 완료
6. 내부 테스트 → 오픈 테스트 → 프로덕션

---

## 6단계 — (선택) 고도화

| 기능 | 방법 |
|------|------|
| 푸시 알림 | Firebase FCM + Capacitor 또는 Play-only 웹 푸시 |
| 딥링크 | `assetlinks.json` + `/shop/123` 등 intent filter |
| 스플래시 | Bubblewrap `splashScreenFadeOutDuration` |
| 오프라인 | Service Worker (현재 미구현, v2 검토) |

---

## 설정 참고 (`_site.config.php`)

```php
'pwa_enabled'           => true,
'pwa_short_name'        => '세부어때',
'pwa_start_url'         => '/',
'pwa_display'           => 'standalone',
'pwa_icon_512_path'     => '/img/logo/android-chrome-512x512.png',
'android_app_package'   => 'kr.co.thecebu.app',
'android_app_name'      => '세부어때',
```

---

## 관련 문서

- [SITEMAP-ROBOTS-GUIDE.md](SITEMAP-ROBOTS-GUIDE.md)
- [Bubblewrap 공식](https://github.com/GoogleChromeLabs/bubblewrap)
- [TWA 문서](https://developer.chrome.com/docs/android/trusted-web-activity)

---

**다음 작업:** 실기기 QA → [PLAY-CONSOLE-GUIDE.md](PLAY-CONSOLE-GUIDE.md) 따라 AAB 업로드·내부 테스트.
