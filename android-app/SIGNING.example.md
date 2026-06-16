# TWA 서명 키 (Git 제외)

`android-app/twa/android.keystore` 와 비밀번호를 **안전한 곳에 보관**하세요.  
Play Store 업데이트마다 동일 키가 필요합니다.

| 항목 | 값 |
|------|-----|
| Keystore | `android-app/twa/android.keystore` |
| Alias | `thecebu` |
| 비밀번호 | *(로컬에서 설정한 값 — 이 파일에 실제 비밀번호를 적지 마세요)* |

## 빌드

```bash
export BUBBLEWRAP_KEYSTORE_PASSWORD='your-password'
export BUBBLEWRAP_KEY_PASSWORD='your-password'
./android-app/build-twa.sh
```

## SHA256 (assetlinks.json)

```bash
keytool -list -v -keystore android-app/twa/android.keystore -alias thecebu
```
