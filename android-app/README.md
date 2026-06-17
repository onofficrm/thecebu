# Android TWA 프로젝트

세부어때 Play Store 앱(TWA) 생성용 작업 폴더입니다.

## 빠른 시작

```bash
npm install -g @bubblewrap/cli
bubblewrap init --manifest=https://thecebu.co.kr/proc/eottae-pwa-manifest.php
bubblewrap build
```

상세 단계: [docs/ANDROID-APP-GUIDE.md](../docs/ANDROID-APP-GUIDE.md)

## 파일

| 파일 | 설명 |
|------|------|
| `twa/` | Bubblewrap TWA 프로젝트 (`kr.co.thecebu.app`) |
| `twa-config.example.json` | Bubblewrap 초기화 참고값 |
| `play-store/listing-ko.txt` | Play 스토어 설명·프로모션 문구 |
| `play-store/data-safety-ko.md` | 데이터 보안 설문 참고 |

## 빌드 산출물 (로컬, Git 제외)

| 파일 | 경로 |
|------|------|
| AAB | `~/thecebu-android-twa/app-release-bundle.aab` |
| APK | `~/Downloads/thecebu-1.0.0.apk` |

Play Console 절차: [docs/PLAY-CONSOLE-GUIDE.md](../docs/PLAY-CONSOLE-GUIDE.md)

> `android.keystore`, `app-release.aab` 등 서명·빌드 산출물은 **Git에 커밋하지 마세요.**
