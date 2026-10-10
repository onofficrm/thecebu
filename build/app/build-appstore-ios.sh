#!/bin/zsh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
WORK="${HOME}/thecebu-ios-build"
ARCHIVE="${WORK}/TheCebu.xcarchive"
EXPORT="${WORK}/export"
DERIVED_DATA="${WORK}/DerivedData-Archive"

if [[ ! -d /Applications/Xcode.app ]]; then
  echo "Xcode.app is not installed."
  exit 1
fi

export DEVELOPER_DIR=/Applications/Xcode.app/Contents/Developer

if ! xcodebuild -version >/dev/null 2>&1; then
  echo "Launch Xcode once and accept its license before building."
  exit 1
fi

cd "${ROOT}"
npm run ios:sync

rm -rf "${ARCHIVE}" "${EXPORT}" "${DERIVED_DATA}"
mkdir -p "${WORK}" "${EXPORT}"

xcodebuild \
  -project "${ROOT}/ios/App/App.xcodeproj" \
  -scheme App \
  -configuration Release \
  -destination "generic/platform=iOS" \
  -archivePath "${ARCHIVE}" \
  -derivedDataPath "${DERIVED_DATA}" \
  CODE_SIGNING_ALLOWED=NO \
  archive

xcodebuild \
  -exportArchive \
  -archivePath "${ARCHIVE}" \
  -exportPath "${EXPORT}" \
  -exportOptionsPlist "${ROOT}/ios/AppStoreExportOptions.plist" \
  -allowProvisioningUpdates

echo "App Store Connect IPA: ${EXPORT}/App.ipa"
