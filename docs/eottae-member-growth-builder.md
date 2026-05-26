# 회원 등급/뱃지 — 빌더·크론 가이드

## 메인 빌더에 스포트라이트 넣기

onoff-builder 메인(`thecebu-main`)을 쓰는 경우 PHP `index.php` 섹션이 자동 적용되지 않습니다.

### 위젯 include (권장)

```php
<?php include_once G5_PATH.'/widgets/member-growth-spotlight.php'; ?>
```

또는:

```php
<?php
include_once G5_PATH.'/components/eottae/member-growth-home.php';
echo eottae_member_growth_home_spotlight_html();
?>
```

### index.php 폴백

`_site.config.php`에서 `home_builder_bridge_id`를 비우면 `member-growth-spotlight` 섹션이 챌린지 아래에 표시됩니다.

---

## 주간 크론 (자동화)

### 작업 내용

1. **지난 주** 활동 랭킹 스냅샷 저장 (`sebu_ranking_snapshots`)
2. **이번 주** 우수회원 자동 선정 (주간 TOP 3, 이미 등록 시 건너뜀)

### CLI

```bash
# 서버 crontab 예시 (매주 월요일 00:10)
10 0 * * 1 cd /path/to/thecebu && php cron/sebu_member_growth_weekly.php >> /var/log/sebu_member_growth_weekly.log 2>&1
```

```bash
# 테스트
php cron/sebu_member_growth_weekly.php --dry-run
php cron/sebu_member_growth_weekly.php --force-featured --limit=3
```

### 웹 호출

`_site.config.local.php` 등에 키 설정:

```php
'member_growth_cron_key' => 'your-secret-key',
// 또는 기존 talkroom_ai_cron_key 재사용 가능
```

```
GET /cron/sebu_member_growth_weekly.php?key=your-secret-key
```

### 관리자 수동 실행

관리자 → 회원 등급/뱃지 → **「주간 크론 실행 (스냅샷+우수회원)」**

---

## 기타 운영

| 기능 | 위치 |
|------|------|
| 우수회원 수동 등록 | 관리자 → 회원 등급/뱃지 |
| 전체 등급 재계산 | 관리자 → **전체 등급 재계산** |
| 지난 주 랭킹 보기 | `/ranking/` 하단 탭 |
| 활동 랭킹 | `/ranking/` |
| 뱃지 도감 | `/badges/` |

---

## 크론 중복 방지

`sebu_member_growth_cron_runs` 테이블에 주차별 실행 이력이 저장됩니다. 같은 주에 두 번 실행해도 스냅샷·우수회원 선정은 한 번만 처리됩니다 (`--force-featured` 또는 관리자 force 옵션으로 재실행 가능).
