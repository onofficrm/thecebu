#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TMP_DIR="$(mktemp -d "${TMPDIR:-/tmp}/thecebu-twa-build.XXXXXX")"
OUT_DIR="${ROOT_DIR}/release"
TASK="${1:-bundleRelease}"

cleanup() {
  rm -rf "${TMP_DIR}"
}
trap cleanup EXIT

mkdir -p "${OUT_DIR}"
shopt -s nullglob

echo "Preparing clean local build copy..."
rsync -a \
  --exclude='._*' \
  --exclude='.DS_Store' \
  --exclude='build' \
  --exclude='app/build' \
  "${ROOT_DIR}/" "${TMP_DIR}/"

echo "Running Gradle task: ${TASK}"
"${TMP_DIR}/gradlew" -p "${TMP_DIR}" ":app:${TASK}"

if [[ "${TASK}" == "bundleRelease" ]]; then
  OUTPUT_FILES=("${TMP_DIR}"/app/build/outputs/bundle/release/*.aab)
  if [[ ${#OUTPUT_FILES[@]} -lt 1 ]]; then
    echo "Could not find generated AAB output." >&2
    exit 1
  fi
  cp "${OUTPUT_FILES[0]}" "${OUT_DIR}/app-release.aab"
  echo "Created ${OUT_DIR}/app-release.aab"
elif [[ "${TASK}" == "assembleRelease" ]]; then
  OUTPUT_FILES=("${TMP_DIR}"/app/build/outputs/apk/release/*.apk)
  if [[ ${#OUTPUT_FILES[@]} -lt 1 ]]; then
    echo "Could not find generated APK output." >&2
    exit 1
  fi
  cp "${OUTPUT_FILES[0]}" "${OUT_DIR}/app-release.apk"
  echo "Created ${OUT_DIR}/app-release.apk"
else
  echo "Gradle task completed. Outputs remain in temporary build copy during the task only."
fi
