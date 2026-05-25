#!/usr/bin/env bash
# 세부어때(thecebu) 그누보드 적용용 ZIP 생성 (커스텀 파일만, 코어 lib/bbs 제외)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
STAGING="$ROOT/dist/staging-thecebu-apply"
OUT="$ROOT/dist"
TS="$(date +%Y%m%d-%H%M)"

rm -rf "$STAGING"
mkdir -p "$STAGING" "$OUT"

copy_file() {
  local rel="$1"
  if [[ ! -e "$ROOT/$rel" ]]; then
    echo "missing: $rel" >&2
    exit 1
  fi
  mkdir -p "$STAGING/$(dirname "$rel")"
  cp -p "$ROOT/$rel" "$STAGING/$rel"
  [[ -f "$STAGING/$rel" ]] && xattr -c "$STAGING/$rel" 2>/dev/null || true
}

copy_dir() {
  local rel="$1"
  if [[ ! -d "$ROOT/$rel" ]]; then
    echo "missing dir: $rel" >&2
    exit 1
  fi
  mkdir -p "$STAGING/$(dirname "$rel")"
  rsync -a --exclude '.DS_Store' --exclude '._*' --exclude '__MACOSX' "$ROOT/$rel/" "$STAGING/$rel/"
}

ROOT_FILES=(
  head.php
  tail.php
  index.php
  _site.config.php
)

EXTEND_FILES=(
  extend/eottae.extend.php
  extend/eottae.config.php
)

LIB_FILES=(
  lib/eottae.lib.php
  lib/eottae-coupon.lib.php
  lib/eottae-api.lib.php
)

CSS_FILES=(
  css/eottae.css
  css/eottae-media-boards.css
  css/g5b-board.css
  css/custom.css
)

JS_FILES=(
  js/eottae.js
  js/eottae-shop-map.js
  js/eottae-home-map.js
)

PROC_FILES=(
  proc/_eottae_json_bootstrap.php
  proc/eottae-review-reply.php
  proc/eottae-review-submit.php
  proc/eottae-geocode.php
  proc/eottae-coupon-use.php
  proc/eottae-shop-save.php
  proc/eottae-shop-content-update.php
  proc/eottae-shop-ai-generate.php
  proc/eottae-shop-map-thumb-ai.php
  proc/inquiry-submit.php
)

PAGE_FILES=(
  page/_init.php
  page/eottae-mypage.php
  page/eottae-points.php
  page/eottae-coupons.php
  page/eottae-my-reviews.php
  page/eottae-saved-shops.php
  page/eottae-inquiries.php
  page/eottae-events.php
  page/privacy.php
  page/map-locator.php
)

SETUP_FILES=(
  setup/tools/eottae-install.php
  setup/tools/eottae-install.lib.php
  setup/tools/eottae-install-cli.php
  setup/tools/eottae-seed.php
  setup/tools/eottae-seed.lib.php
)

API_FILES=(
  api/eottae.php
)

for f in "${ROOT_FILES[@]}" "${EXTEND_FILES[@]}" "${LIB_FILES[@]}" "${CSS_FILES[@]}" "${JS_FILES[@]}" "${PROC_FILES[@]}" "${PAGE_FILES[@]}" "${SETUP_FILES[@]}" "${API_FILES[@]}"; do
  copy_file "$f"
done

copy_file skin/board/_inc/eottae-shop-view-setup.php

DIRS=(
  components/eottae
  skin/board/eottae-shop
  skin/board/eottae-community
  skin/member/eottae
  mobile/skin/board/eottae-shop
  mobile/skin/board/eottae-community
  mobile/skin/member/eottae
  plugin/onoff-builder-bridge
)

for d in "${DIRS[@]}"; do
  copy_dir "$d"
done

find "$STAGING" -name '.DS_Store' -delete
find "$STAGING" -name '._*' -delete
find "$STAGING" -name '__MACOSX' -type d -prune -exec rm -rf {} + 2>/dev/null || true

php <<'PHP' "$STAGING/_site.config.php"
<?php
$path = $argv[1];
$src = file_get_contents($path);
$replacements = array(
    "'company_name'        => '회사명'" => "'company_name'        => '세부어때'",
    "'ceo_name'            => '대표자명'" => "'ceo_name'            => '세부어때'",
    "'business_no'         => '000-00-00000'" => "'business_no'         => ''",
    "'phone'               => '010-0000-0000'" => "'phone'               => '0917-123-4567'",
    "'email'               => 'help@example.com'" => "'email'               => 'help@thecebu.co.kr'",
    "'address'             => '주소를 입력하세요'" => "'address'             => 'Cebu, Philippines'",
    "'footer_desc'         => '고객의 성장을 돕는 웹사이트 제작 베이스입니다.'" => "'footer_desc'         => '필리핀 세부 교민·관광객 위치기반 커뮤니티'",
    "'inquiry_notify_email'    => 'admin@example.com'" => "'inquiry_notify_email'    => 'help@thecebu.co.kr'",
);
foreach ($replacements as $from => $to) {
    $src = str_replace($from, $to, $src);
}
file_put_contents($path, $src);
PHP

cat > "$STAGING/_site.config.local.php.example" <<'EOF'
<?php
/**
 * 서버에 _site.config.local.php 로 복사 후 값 입력 (Git/FTP에 올리지 마세요)
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

$site_config_override = array(
    'google_maps_api_key' => 'YOUR_GOOGLE_MAPS_API_KEY',
);
EOF

cat > "$STAGING/README-APPLY.txt" <<'EOF'
세부어때(thecebu) — 그누보드 적용 ZIP
====================================

【1】 FTP / 파일매니저
  public_html(그누보드 루트)에 ZIP 압축 해제 (덮어쓰기)
  ※ data/dbconfig.php · data/dbconfig.local.php 는 덮어쓰지 마세요

【2】 Google Maps (필수)
  _site.config.local.php.example → _site.config.local.php 복사 후 API 키 입력

【3】 1차 설치 (최초 1회)
  /setup/tools/eottae-install.php 실행 → 게시판·테이블 생성

【4】 샘플 업소 (선택)
  /setup/tools/eottae-seed.php?run=1&shops=1

【5】 홈이 샘플/빈 화면일 때
  thecebu-main.zip → /plugin/onoff-builder-bridge/admin/upload.php 업로드
  프로젝트 ID: thecebu-main
  _site.config.php → home_builder_bridge_id = 'thecebu-main' 확인

【6】 확인
  /  홈(React)
  /bbs/board.php?bo_table=shop  내주변
  /page/eottae-mypage.php  MY
EOF

APPLY_ZIP="$OUT/thecebu-gnuboard-apply-${TS}.zip"
(
  cd "$STAGING"
  COPYFILE_DISABLE=1 zip -r -X -q "$APPLY_ZIP" . -x '*.DS_Store' -x '**/._*' -x '**/__MACOSX/**'
)

MAIN_STAGING="$ROOT/dist/staging-thecebu-main"
MAIN_ZIP="$OUT/thecebu-main-${TS}.zip"
rm -rf "$MAIN_STAGING"
mkdir -p "$MAIN_STAGING/assets"
cp -p "$ROOT/plugin/onoff-builder-bridge/imports/thecebu-main/index.html" "$MAIN_STAGING/"
cp -p "$ROOT/plugin/onoff-builder-bridge/imports/thecebu-main/assets/"* "$MAIN_STAGING/assets/"
(
  cd "$MAIN_STAGING"
  COPYFILE_DISABLE=1 zip -r -X -q "$MAIN_ZIP" . -x '*.DS_Store' -x '**/._*'
)

cp -f "$APPLY_ZIP" "$OUT/thecebu-gnuboard-apply.zip"
cp -f "$MAIN_ZIP" "$OUT/thecebu-main.zip"

echo "Created:"
echo "  $APPLY_ZIP"
echo "  $MAIN_ZIP"
echo "  $OUT/thecebu-gnuboard-apply.zip"
echo "  $OUT/thecebu-main.zip"
du -h "$APPLY_ZIP" "$MAIN_ZIP"
