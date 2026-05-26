# 업체 등록 · 지도 썸네일 규칙 (현재 구현 기준)

> **목적**  
> 세부어때(eottae) 업체(shop) 등록 시 이미지가 어디에 저장되고, 목록·상세·지도에서 어떤 URL이 선택되는지 한곳에 정의합니다.  
> **기준일**: 2026-05-26 · **코드 기준**: `lib/eottae.lib.php`, `extend/eottae.extend.php`, `skin/board/eottae-shop/write.skin.php`

---

## 1. 핵심 원칙 (반드시 구분)

| 구분 | 저장 위치 | 용도 |
|------|-----------|------|
| **업체 이미지** (GNUBoard 첨부) | `data/file/{bo_table}/` · `g5_board_file` | 상세 갤러리, 대표 이미지, GNUBoard 리스트 썸네일 생성 |
| **지도 표시 썸네일** (전용) | `data/eottae-shop-map-thumb/` · `g5_eottae_shop_map_thumb` | 지도 마커·InfoWindow, 목록 fallback 일부 |

두 시스템은 **독립**입니다. 지도 썸네일을 올려도 `bf_file[]`에 들어가지 않으며, 반대로 대표 이미지만 올려도 DB에 지도 전용 레코드는 생기지 않습니다.

**UI 안내 문구** (`write.skin.php`):  
> “비워 두면 **대표 이미지**가 사용됩니다.”  
→ 지도 전용 파일이 없을 때 `eottae_shop_listing_thumb_url()` 등의 fallback이 GNUBoard 대표(`bf_no` 오름차순 첫 파일)까지 내려갑니다.

---

## 2. 업체 등록 플로우

### 2.1 화면

- 스킨: `skin/board/eottae-shop/write.skin.php` (모바일 동일 구조)
- 7단계 마법사: 기본정보 → 위치 → 연락/SNS → 영업정보 → **이미지·메뉴** → SEO → 확인

### 2.2 이미지·메뉴 단계 (data-step="4")

| 필드 | POST/파일 | 설명 |
|------|-----------|------|
| 업체 이미지 | `bf_file[]` | `$file_count`개(GNUBoard 게시판 설정). **인덱스 0 = 대표** |
| 지도 표시 썸네일 | `eottae_map_thumb` | 단일 파일, jpg/png/gif/webp |
| 지도 썸네일 삭제 | `eottae_map_thumb_del=1` | 저장 시 기존 지도 썸네일 제거 |
| AI 임시 파일 | `eottae_map_thumb_ai_tmp` | AI 생성 후 저장 전까지 `data/eottae-shop-map-thumb/tmp/` |

권장: 지도 썸네일 **정사각 1024×1024px 이상** (마커는 약 46px 원형으로 렌더).

### 2.3 저장 훅 (`extend/eottae.extend.php`)

| 이벤트 | 함수 | 동작 |
|--------|------|------|
| `write_update_before` | `eottae_on_shop_write_before` | 카테고리·지역·좌표 보정, 슈퍼관리자 사업자 지정 검증 |
| `write_update_after` | `eottae_on_shop_write_after` | SEO 저장, **`eottae_shop_map_thumb_save_from_upload()`**, 문의코드 등 |
| `bbs_delete` | `eottae_on_shop_delete` | SEO·**지도 썸네일 파일·DB**·광고 비활성 |

지도 썸네일 저장 우선순위 (`eottae_shop_map_thumb_save_from_upload`):

1. `eottae_map_thumb_ai_tmp`가 있으면 → `eottae_shop_map_thumb_save_from_tmp()` (tmp → 본 디렉터리, PNG)
2. `$_FILES['eottae_map_thumb']` 업로드
3. 업로드 없음 + `eottae_map_thumb_del` → 삭제
4. 그 외 → 기존 지도 썸네일 유지

### 2.4 AI 지도 썸네일

- API: `proc/eottae-shop-map-thumb-ai.php`
- OpenAI Images (`eottae_ai_generate` 설정의 `image_model`), **1024×1024**, base64 → `tmp/ai_*.png`
- 폼 제출 시 `eottae_map_thumb_ai_tmp`로 본 저장소에 커밋 (`source_name`: `AI 지도 썸네일`)
- 사전 조건: 로그인, 업체명 입력, `eottae_is_shop_board` 게시판

---

## 3. 대표 이미지 정의

```text
eottae_shop_representative_image_url(bo_table, wr_id)
  → g5_board_file WHERE bo_table = storage_bo AND wr_id
     ORDER BY bf_no ASC LIMIT 1
```

- 등록 UI의 **첫 번째 슬롯(`bf_file[]` index 0)** 과 동일 개념.
- GNUBoard가 생성한 `_thumb` 파일이 아니라 **원본 `bf_file`** URL을 반환.

---

## 4. 썸네일 URL 선택 규칙 (함수별)

모든 공개 URL은 가능하면 `eottae_map_public_url()`로 절대 URL화합니다.

### 4.1 `eottae_shop_listing_thumb_url` — 공통·지도 fallback

**우선순위**

1. 지도 전용 썸네일 (`eottae_shop_map_thumb_get`)
2. GNUBoard `get_list_thumbnail(storage_bo, wr_id, 200×200)`
3. 목록 row에 이미 로드된 `row['file'][0]`
4. `eottae_shop_representative_image_url`

### 4.2 `eottae_shop_map_marker_thumb_url` — **Google Maps 마커**

**우선순위**

1. 지도 전용 썸네일
2. `eottae_shop_card_thumb(row)` (아래)
3. `eottae_shop_listing_thumb_url` (1번이 없을 때 1번을 다시 시도하는 효과)

### 4.3 `eottae_shop_card_thumb` — **목록 카드** (`components/eottae/shop-card.php`)

**우선순위**

1. GNUBoard `get_list_thumbnail` (400×400)
2. `row['file'][0]` (첨부 경로)
3. `eottae_shop_listing_thumb_url` → 여기서 **지도 전용** 또는 대표 이미지

> **주의**: 목록 카드는 **GNUBoard 썸네일을 지도 전용보다 먼저** 씁니다.  
> 지도 마커는 **지도 전용을 최우선**합니다.  
> 같은 업체가 “카드=첨부 썸네일, 지도=지도 전용”으로 다르게 보일 수 있습니다. 의도된 현재 동작입니다.

### 4.4 `eottae_shop_gallery_images` — **상세 히어로 갤러리**

**우선순위**

1. 게시판 첨부 전체 (`view['file']` 또는 `get_file(storage_bo|view_bo)`)
2. 첨부가 **하나도 없을 때만** 지도 전용 썸네일
3. 대표 이미지 (`eottae_shop_representative_image_url`)
4. `get_list_thumbnail` (1200×675)
5. 카테고리 기본 커버 (`eottae_shop_default_cover_url`)

> 지도 전용 이미지는 **첨부가 있으면 갤러리에 나오지 않습니다.**

---

## 5. 지도 마커 파이프라인

```text
업체 목록 rows
  → eottae_shop_map_markers($list, $bo_table)
       · 좌표: wr_4/wr_5 또는 주소·지역 guess
       · thumbnail: eottae_shop_map_marker_thumb_url()
  → eottae_shop_map_locations_json($markers)
  → js/eottae-shop-map.js
       · MARKER_SIZE = 46px, canvas 원형 아이콘
       · InfoWindow에 thumbnail 표시
```

- AJAX 목록: `proc/eottae-shop-list.php` 동일 마커 생성.
- 좌표 없는 업체는 마커에서 **제외**.

---

## 6. `bo_table` · 세그먼트 게시판

### 6.1 `eottae_shop_storage_bo_table($bo_table)`

- `food`, `massage` 등 **세그먼트 게시판**에서 글을 써도 파일·지도 썸네일·대표 이미지 조회는 **`shop` 테이블 키**로 통일.
- `eottae_shop_map_thumb_get`은 요청 `bo_table`, `storage_bo`, `shop`, `eottae_shop_board_tables()` 순으로 DB를 찾아 **레거시 키 호환**.

### 6.2 DB 키

- 지도 썸네일 INSERT 시 `bo_table` = **`storage_bo`** (예: `shop`).
- 삭제 훅은 폼의 `$board['bo_table']`을 넘김 → 세그먼트에서 등록한 글은 `get`이 `shop` 행을 찾아 파일 삭제 가능. DB `delete`는 전달된 `bo_table`만 대상 (운영 시 orphan 가능성 인지).

---

## 7. 노출 위치 요약표

| 화면 | 사용 함수 | 지도 전용 최우선? |
|------|-----------|-------------------|
| 지도 마커·InfoWindow | `eottae_shop_map_marker_thumb_url` | **예** |
| 업체 목록 카드 | `eottae_shop_card_thumb` | 아니오 (GNUBoard thumb 우선) |
| 기타 listing fallback | `eottae_shop_listing_thumb_url` | 예 (1순위) |
| 상세 갤러리 | `eottae_shop_gallery_images` | 첨부 없을 때만 |
| 대표 이미지 단독 | `eottae_shop_representative_image_url` | 해당 없음 |

---

## 8. 운영·개발 체크리스트

- [ ] 지도에 잘 보이게 하려면 **「지도 표시 썸네일」** 또는 AI 생성 사용 (대표만으로는 마커가 첨부 썸네일과 다를 수 있음).
- [ ] 상세 페이지 대문은 **업체 이미지 첨부**가 우선; 지도 전용만 올리면 갤러리 1장으로 보임.
- [ ] 세그먼트 게시판(`food` 등) 등록 후에도 파일은 `data/file/shop/` 아래에 저장되는지 확인.
- [ ] 업체 삭제 시 `data/eottae-shop-map-thumb/{file}` 및 DB 행이 함께 제거되는지 확인.
- [ ] AI 지도 썸네일은 `eottae_ai_generate` API 키·`image_model` 필요.

---

## 9. 관련 파일

| 역할 | 경로 |
|------|------|
| 지도 썸네일 CRUD·URL 헬퍼 | `lib/eottae.lib.php` (~1518–1875, 3778–3842, 4281–4397) |
| 등록 UI | `skin/board/eottae-shop/write.skin.php` |
| 저장/삭제 훅 | `extend/eottae.extend.php` |
| AI 생성 | `proc/eottae-shop-map-thumb-ai.php` |
| 목록 카드 썸네일 | `components/eottae/shop-card.php` |
| 지도 JS | `js/eottae-shop-map.js` |
| 목록 API 마커 | `proc/eottae-shop-list.php` |
| 상세 갤러리 | `skin/board/eottae-shop/view.skin.php` |

---

## 10. 변경 시 가이드

- **지도와 카드 이미지를 항상 동일하게** 맞추려면 `eottae_shop_card_thumb` 우선순위를 `map_thumb` 우선으로 조정하는 별도 작업이 필요합니다 (현재는 의도적으로 다름).
- **갤러리에 지도 썸네일을 항상 포함**하려면 `eottae_shop_gallery_images`의 “첨부 없을 때만” 조건을 변경해야 합니다.
- 새 노출면을 추가할 때는 위 표에서 **어느 함수를 호출할지** 먼저 정한 뒤 구현하세요. `get_list_thumbnail`만 직접 쓰면 지도 전용 이미지가 빠질 수 있습니다.
