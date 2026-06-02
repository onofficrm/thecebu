#!/usr/bin/env bash
# Google OAuth 로컬 파일을 운영 서버 data/ 로 FTP 업로드 (GitHub Secrets 없을 때)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="${ROOT}/data/eottae-google-oauth.local.php"
if [[ ! -f "$SRC" ]]; then
  echo "Missing: $SRC" >&2
  exit 1
fi
: "${FTP_SERVER:?Set FTP_SERVER}"
: "${FTP_USERNAME:?Set FTP_USERNAME}"
: "${FTP_PASSWORD:?Set FTP_PASSWORD}"
FTP_PORT="${FTP_PORT:-21}"
REMOTE_DIR="${FTP_REMOTE_DIR:-/public_html/data}"
echo "Uploading to ftp://${FTP_SERVER}${REMOTE_DIR}/eottae-google-oauth.local.php"
curl -sS --ftp-create-dirs -T "$SRC" \
  "ftp://${FTP_USERNAME}:${FTP_PASSWORD}@${FTP_SERVER}:${FTP_PORT}${REMOTE_DIR}/eottae-google-oauth.local.php"
echo "Done."
