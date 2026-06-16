#!/usr/bin/env bash
# 세부어때 TWA 빌드 스크립트
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
TWA_DIR="$ROOT/twa"
SDK_DIR="$ROOT/android-sdk"
JDK_PATH="/opt/homebrew/opt/openjdk@17/libexec/openjdk.jdk"

mkdir -p "$HOME/.bubblewrap"
cat > "$HOME/.bubblewrap/config.json" <<EOF
{
  "jdkPath": "$JDK_PATH",
  "androidSdkPath": "$SDK_DIR"
}
EOF

if [ ! -d "$SDK_DIR/build-tools" ]; then
  echo "Android SDK가 없습니다. 먼저 docs/ANDROID-APP-GUIDE.md 3단계 SDK 설치를 진행하세요."
  exit 1
fi

if [ ! -e "$SDK_DIR/bin/sdkmanager" ]; then
  ln -sfn cmdline-tools/latest/bin "$SDK_DIR/bin"
fi

if [ ! -f "$TWA_DIR/android.keystore" ]; then
  echo "서명 키 생성 중..."
  read -r -s -p "Keystore 비밀번호 (6자 이상): " STORE_PASS
  echo
  read -r -s -p "동일 비밀번호 확인: " STORE_PASS2
  echo
  if [ "$STORE_PASS" != "$STORE_PASS2" ]; then
    echo "비밀번호가 일치하지 않습니다."
    exit 1
  fi
  keytool -genkeypair -v \
    -keystore "$TWA_DIR/android.keystore" \
    -alias thecebu \
    -keyalg RSA -keysize 2048 -validity 10000 \
    -storepass "$STORE_PASS" \
    -keypass "$STORE_PASS" \
    -dname "CN=세부어때, OU=Mobile, O=thecebu.co.kr, L=Cebu, ST=Cebu, C=PH"
  echo "비밀번호는 android-app/SIGNING.local.md 에 저장해 두세요 (Git 제외)."
fi

cd "$TWA_DIR"

if [ ! -d "$TWA_DIR/app" ]; then
  echo "Bubblewrap 프로젝트 생성 중..."
  bubblewrap update --appVersionName=1.0.0 --appVersionCode=1
fi

if [ -z "${BUBBLEWRAP_KEYSTORE_PASSWORD:-}" ] || [ -z "${BUBBLEWRAP_KEY_PASSWORD:-}" ]; then
  echo "환경변수를 설정하세요:"
  echo "  export BUBBLEWRAP_KEYSTORE_PASSWORD='your-password'"
  echo "  export BUBBLEWRAP_KEY_PASSWORD='your-password'"
  exit 1
fi

bubblewrap update --appVersionName=1.0.0 --appVersionCode=1
bubblewrap build

echo ""
echo "빌드 완료:"
ls -la "$TWA_DIR"/*.aab "$TWA_DIR"/*.apk 2>/dev/null || ls -la "$TWA_DIR/app/build/outputs" 2>/dev/null || true

echo ""
echo "SHA256 (assetlinks.json용):"
keytool -list -v -keystore "$TWA_DIR/android.keystore" -alias thecebu -storepass "$BUBBLEWRAP_KEYSTORE_PASSWORD" 2>/dev/null | awk '/SHA256:/{print $2}'
