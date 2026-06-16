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
| `twa-config.example.json` | Bubblewrap 초기화 참고값 |

> `android.keystore`, `app-release.aab` 등 서명·빌드 산출물은 **Git에 커밋하지 마세요.**
