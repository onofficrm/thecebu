<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';
eottae_ad_platform_ensure_schema();

$slots = eottae_ad_platform_get_slots(false);
$dashboard = eottae_ad_platform_admin_dashboard_stats();
$status_filter = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
$campaigns = eottae_ad_platform_admin_campaigns($status_filter, 80);
$proc_url = G5_URL.'/proc/eottae-ad-platform-admin.php';

g5_page_start('광고 플랫폼 관리');
?>

<main class="promo-admin-page ad-platform-admin">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo G5_URL; ?>/page/eottae-events.php" class="promo-admin-page__back">← 이벤트·프로모션</a>
        </div>
        <h1 class="promo-admin-page__title">광고 플랫폼 관리</h1>
        <p class="promo-admin-page__desc">광고 위치별 포인트 단가·슬롯 수를 설정하고, 사업자 광고 신청을 승인·반려합니다.</p>
    </header>

    <section class="promo-admin-panel ad-platform-admin__dashboard" aria-labelledby="ad-platform-dashboard-title">
        <h2 id="ad-platform-dashboard-title" class="promo-admin-panel__title">슬롯·성과 요약</h2>
        <div class="ad-platform-admin__summary-grid">
            <div class="ad-platform-admin__summary-card">
                <span class="ad-platform-admin__summary-label">노출 중</span>
                <strong><?php echo number_format((int) $dashboard['totals']['active_count']); ?></strong>
            </div>
            <div class="ad-platform-admin__summary-card">
                <span class="ad-platform-admin__summary-label">승인 대기</span>
                <strong><?php echo number_format((int) $dashboard['totals']['pending_count']); ?></strong>
            </div>
            <div class="ad-platform-admin__summary-card">
                <span class="ad-platform-admin__summary-label">대기등록</span>
                <strong><?php echo number_format((int) $dashboard['totals']['waitlist_count']); ?></strong>
            </div>
            <div class="ad-platform-admin__summary-card">
                <span class="ad-platform-admin__summary-label">누적 노출</span>
                <strong><?php echo number_format((int) $dashboard['totals']['impressions']); ?></strong>
            </div>
            <div class="ad-platform-admin__summary-card">
                <span class="ad-platform-admin__summary-label">누적 클릭</span>
                <strong><?php echo number_format((int) $dashboard['totals']['clicks']); ?></strong>
            </div>
            <div class="ad-platform-admin__summary-card">
                <span class="ad-platform-admin__summary-label">CTR</span>
                <strong><?php echo number_format((float) $dashboard['totals']['ctr'], 2); ?>%</strong>
            </div>
            <div class="ad-platform-admin__summary-card">
                <span class="ad-platform-admin__summary-label">집행 포인트</span>
                <strong><?php echo number_format((int) $dashboard['totals']['points_charged']); ?>P</strong>
            </div>
        </div>
        <div class="shop-spot-admin__table-wrap">
            <table class="shop-spot-admin__table ad-platform-admin__table ad-platform-admin__table--stats">
                <thead>
                    <tr>
                        <th>위치</th>
                        <th>노출 중</th>
                        <th>슬롯 점유</th>
                        <th>승인 대기</th>
                        <th>대기등록</th>
                        <th>노출</th>
                        <th>클릭</th>
                        <th>CTR</th>
                        <th>집행 P</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dashboard['slots'] as $slot_stat) { ?>
                    <tr>
                        <td><?php echo get_text($slot_stat['slot_name']); ?><?php if (empty($slot_stat['is_active'])) { ?> <span class="ad-platform-admin__sub">(비활성)</span><?php } ?></td>
                        <td><?php echo number_format((int) $slot_stat['active_count']); ?> / <?php echo (int) $slot_stat['max_active_ads']; ?></td>
                        <td><?php echo number_format((float) $slot_stat['fill_rate'], 1); ?>%</td>
                        <td><?php echo number_format((int) $slot_stat['pending_count']); ?></td>
                        <td><?php echo number_format((int) $slot_stat['waitlist_count']); ?></td>
                        <td><?php echo number_format((int) $slot_stat['impressions']); ?></td>
                        <td><?php echo number_format((int) $slot_stat['clicks']); ?></td>
                        <td><?php echo number_format((float) $slot_stat['ctr'], 2); ?>%</td>
                        <td><?php echo number_format((int) $slot_stat['points_charged']); ?>P</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="promo-admin-panel" aria-labelledby="ad-platform-slots-title">
        <h2 id="ad-platform-slots-title" class="promo-admin-panel__title">광고 위치 설정</h2>
        <div class="ad-platform-admin__slot-grid">
            <?php foreach ($slots as $slot) { ?>
            <form class="promo-admin-form ad-platform-admin__slot-form" method="post" action="<?php echo $proc_url; ?>" data-ad-platform-slot-form>
                <input type="hidden" name="action" value="save_slot">
                <input type="hidden" name="slot_id" value="<?php echo (int) $slot['slot_id']; ?>">
                <fieldset>
                    <legend><?php echo get_text($slot['slot_name']); ?> <code><?php echo get_text($slot['slot_code']); ?></code></legend>
                    <p class="promo-admin-form__hint"><?php echo get_text($slot['slot_desc']); ?><?php if ($slot['bo_table'] !== '') { ?> · <?php echo get_text($slot['bo_table']); ?><?php } ?></p>
                    <div class="promo-admin-form__row">
                        <div class="promo-admin-form__field">
                            <label>하루 포인트</label>
                            <input type="number" name="point_per_day" min="0" step="10" value="<?php echo (int) $slot['point_per_day']; ?>" required>
                        </div>
                        <div class="promo-admin-form__field">
                            <label>최소 일수</label>
                            <input type="number" name="min_days" min="1" max="365" value="<?php echo (int) $slot['min_days']; ?>" required>
                        </div>
                        <div class="promo-admin-form__field">
                            <label>최대 일수</label>
                            <input type="number" name="max_days" min="1" max="365" value="<?php echo (int) $slot['max_days']; ?>" required>
                        </div>
                        <div class="promo-admin-form__field">
                            <label>동시 노출 수</label>
                            <input type="number" name="max_active_ads" min="1" max="10" value="<?php echo (int) $slot['max_active_ads']; ?>" required>
                        </div>
                    </div>
                    <div class="promo-admin-form__row">
                        <label><input type="checkbox" name="requires_review" value="1"<?php echo !empty($slot['requires_review']) ? ' checked' : ''; ?>> 관리자 승인 필요</label>
                        <label><input type="checkbox" name="requires_image" value="1"<?php echo !empty($slot['requires_image']) ? ' checked' : ''; ?>> 이미지 필수</label>
                        <label><input type="checkbox" name="is_active" value="1"<?php echo !empty($slot['is_active']) ? ' checked' : ''; ?>> 사용</label>
                    </div>
                    <p class="promo-admin-form__status" role="status"></p>
                    <button type="submit" class="promo-reward-btn">저장</button>
                </fieldset>
            </form>
            <?php } ?>
        </div>
    </section>

    <section class="promo-admin-panel" aria-labelledby="ad-platform-campaigns-title">
        <div class="ad-platform-admin__campaign-head">
            <h2 id="ad-platform-campaigns-title" class="promo-admin-panel__title">광고 신청 내역</h2>
            <div class="ad-platform-admin__filters">
                <a href="<?php echo G5_URL; ?>/page/eottae-admin-ad-platform.php" class="event-promo-page__admin-link<?php echo $status_filter === '' ? ' is-active' : ''; ?>">전체</a>
                <a href="<?php echo G5_URL; ?>/page/eottae-admin-ad-platform.php?status=pending_review" class="event-promo-page__admin-link<?php echo $status_filter === 'pending_review' ? ' is-active' : ''; ?>">승인 대기</a>
                <a href="<?php echo G5_URL; ?>/page/eottae-admin-ad-platform.php?status=waitlisted" class="event-promo-page__admin-link<?php echo $status_filter === 'waitlisted' ? ' is-active' : ''; ?>">대기등록</a>
                <a href="<?php echo G5_URL; ?>/page/eottae-admin-ad-platform.php?status=active" class="event-promo-page__admin-link<?php echo $status_filter === 'active' ? ' is-active' : ''; ?>">노출 중</a>
            </div>
        </div>

        <?php if (empty($campaigns)) { ?>
        <p class="promo-admin-form__hint">신청 내역이 없습니다.</p>
        <?php } else { ?>
        <div class="shop-spot-admin__table-wrap">
            <table class="shop-spot-admin__table ad-platform-admin__table">
                <thead>
                    <tr>
                        <th>위치</th>
                        <th>제목</th>
                        <th>회원</th>
                        <th>기간</th>
                        <th>포인트</th>
                        <th>타깃</th>
                        <th>보너스</th>
                        <th>상태</th>
                        <th>성과</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campaigns as $ad) { ?>
                    <tr>
                        <td><?php echo get_text($ad['slot_name']); ?></td>
                        <td>
                            <strong><?php echo get_text($ad['title']); ?></strong>
                            <?php if ($ad['description'] !== '') { ?><br><span class="ad-platform-admin__sub"><?php echo get_text(cut_str($ad['description'], 60)); ?></span><?php } ?>
                        </td>
                        <td><?php echo get_text($ad['mb_id']); ?></td>
                        <td><?php echo get_text($ad['start_date']); ?> ~ <?php echo get_text($ad['end_date']); ?></td>
                        <td><?php echo number_format((int) $ad['total_points']); ?>P</td>
                        <td>
                            <?php
                            $target_parts = array();
                            if ($ad['target_category'] !== '') {
                                $target_parts[] = get_text($ad['target_category']);
                            }
                            if ($ad['target_region'] !== '') {
                                $target_parts[] = get_text($ad['target_region']);
                            }
                            echo !empty($target_parts) ? get_text(implode(' · ', $target_parts)) : '전체';
                            ?>
                        </td>
                        <td><?php echo (int) $ad['bid_bonus'] > 0 ? number_format((int) $ad['bid_bonus']).'P' : '—'; ?></td>
                        <td>
                            <?php echo get_text($ad['status_label']); ?>
                            <?php if ($ad['status'] === 'waitlisted' && (int) $ad['waitlist_order'] > 0) { ?><br><span class="ad-platform-admin__sub">대기 <?php echo (int) $ad['waitlist_order']; ?>번</span><?php } ?>
                        </td>
                        <td>
                            <?php echo number_format((int) $ad['impressions']); ?> / <?php echo number_format((int) $ad['clicks']); ?>
                            <?php if ((int) $ad['impressions'] > 0) { ?><br><span class="ad-platform-admin__sub">CTR <?php echo number_format((float) $ad['ctr'], 2); ?>%</span><?php } ?>
                        </td>
                        <td class="ad-platform-admin__actions">
                            <?php if (in_array($ad['status'], array('active', 'expired', 'scheduled'), true)) { ?>
                            <a href="<?php echo eottae_ad_platform_report_url((int) $ad['ad_id']); ?>" class="ad-platform-admin__report-link">리포트</a>
                            <?php } ?>
                            <?php if (in_array($ad['status'], array('pending_review', 'waitlisted'), true)) { ?>
                            <button type="button" class="promo-reward-btn promo-reward-btn--primary" data-ad-platform-action="approve" data-ad-id="<?php echo (int) $ad['ad_id']; ?>">승인</button>
                            <button type="button" class="promo-reward-btn" data-ad-platform-action="reject" data-ad-id="<?php echo (int) $ad['ad_id']; ?>">반려</button>
                            <?php } ?>
                            <?php if (in_array($ad['status'], array('active', 'scheduled', 'approved'), true)) { ?>
                            <button type="button" class="promo-reward-btn" data-ad-platform-action="cancel" data-ad-id="<?php echo (int) $ad['ad_id']; ?>">중지</button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<script>
(function () {
  var procUrl = <?php echo json_encode($proc_url, JSON_UNESCAPED_UNICODE); ?>;

  document.querySelectorAll('[data-ad-platform-slot-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var statusEl = form.querySelector('.promo-admin-form__status');
      if (statusEl) statusEl.textContent = '저장 중…';
      fetch(procUrl, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (statusEl) statusEl.textContent = data.message || (data.ok ? '저장되었습니다.' : '저장 실패');
        })
        .catch(function () {
          if (statusEl) statusEl.textContent = '네트워크 오류';
        });
    });
  });

  document.querySelectorAll('[data-ad-platform-action]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var action = btn.getAttribute('data-ad-platform-action');
      var adId = btn.getAttribute('data-ad-id');
      var message = '';
      if (action === 'reject' || action === 'cancel') {
        message = window.prompt(action === 'reject' ? '반려 사유를 입력해 주세요.' : '중지 사유를 입력해 주세요.', '') || '';
      }
      if (!window.confirm('진행하시겠습니까?')) return;
      var fd = new FormData();
      fd.append('action', action);
      fd.append('ad_id', adId);
      fd.append('message', message);
      fetch(procUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          alert(data.message || (data.ok ? '처리되었습니다.' : '처리 실패'));
          if (data.ok) location.reload();
        });
    });
  });
})();
</script>

<?php
g5_page_end();
