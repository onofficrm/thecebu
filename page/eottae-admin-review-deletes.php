<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-review-delete.lib.php';
eottae_review_delete_ensure_schema();

$pending = eottae_review_delete_request_list('pending', 100);
$recent = eottae_review_delete_request_list('all', 30);
$pending_count = eottae_review_delete_pending_count();

g5_page_start('리뷰 삭제 요청 관리');
?>

<main class="promo-admin-page review-delete-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo G5_URL; ?>/page/eottae-events.php" class="promo-admin-page__back">← 이벤트·프로모션</a>
        </div>
        <h1 class="promo-admin-page__title">리뷰 삭제 요청 관리</h1>
        <p class="promo-admin-page__desc">
            업체 관리자가 요청한 리뷰 삭제를 검토합니다. 승인 시 리뷰가 즉시 삭제되고 평점·리뷰 수가 갱신됩니다.
            <?php if ($pending_count > 0) { ?>
            <strong class="review-delete-admin-page__pending">대기 <?php echo number_format($pending_count); ?>건</strong>
            <?php } ?>
        </p>
    </header>

    <section class="promo-admin-panel" aria-labelledby="review-delete-pending-title">
        <h2 id="review-delete-pending-title" class="promo-admin-panel__title">대기 중인 삭제 요청</h2>
        <?php if (empty($pending)) { ?>
        <p class="promo-admin-panel__empty">대기 중인 삭제 요청이 없습니다.</p>
        <?php } else { ?>
        <ul class="promo-admin-list review-delete-admin-list">
            <?php foreach ($pending as $req) {
                $shop_url = G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.(int) $req['shop_wr_id'].'#review-'.(int) $req['review_wr_id'];
                $rating = isset($req['review_rating']) ? (float) $req['review_rating'] : 0;
                $preview = isset($req['review_content']) ? get_text(strip_tags($req['review_content'])) : '';
                if (function_exists('mb_strlen') && mb_strlen($preview, 'UTF-8') > 120) {
                    $preview = mb_substr($preview, 0, 120, 'UTF-8').'…';
                } elseif (strlen($preview) > 120) {
                    $preview = substr($preview, 0, 120).'…';
                }
                ?>
            <li class="promo-admin-list__item review-delete-admin-list__item">
                <div class="promo-admin-list__main">
                    <strong><?php echo get_text($req['shop_name'] ?: '업체 #'.(int) $req['shop_wr_id']); ?></strong>
                    <span class="promo-admin-list__badge promo-admin-list__badge--paused">검토 대기</span>
                    <p class="promo-admin-list__meta">
                        요청: <?php echo get_text($req['request_mb_id']); ?>
                        · <?php echo $req['request_datetime'] !== '0000-00-00 00:00:00' ? substr($req['request_datetime'], 0, 16) : ''; ?>
                    </p>
                    <p class="review-delete-admin-list__review">
                        <span class="review-delete-admin-list__rating"><?php echo number_format($rating, 1); ?>점</span>
                        <?php echo get_text($req['review_author']); ?> —
                        <?php echo $preview !== '' ? $preview : '(내용 없음)'; ?>
                    </p>
                    <?php if (!empty($req['request_reason'])) { ?>
                    <p class="review-delete-admin-list__reason">사유: <?php echo get_text($req['request_reason']); ?></p>
                    <?php } ?>
                    <p class="promo-admin-list__meta">
                        <a href="<?php echo $shop_url; ?>" target="_blank" rel="noopener noreferrer">업체 페이지에서 리뷰 보기</a>
                    </p>
                </div>
                <div class="promo-admin-list__actions review-delete-admin-list__actions">
                    <button type="button"
                        class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary"
                        data-review-delete-approve="<?php echo (int) $req['rdr_id']; ?>">승인 (삭제)</button>
                    <button type="button"
                        class="promo-admin-btn promo-admin-btn--sm"
                        data-review-delete-reject="<?php echo (int) $req['rdr_id']; ?>">반려</button>
                </div>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <?php if (!empty($recent)) { ?>
    <section class="promo-admin-panel" aria-labelledby="review-delete-history-title">
        <h2 id="review-delete-history-title" class="promo-admin-panel__title">최근 처리 내역</h2>
        <ul class="promo-admin-list review-delete-admin-list review-delete-admin-list--history">
            <?php foreach ($recent as $req) {
                if ($req['request_status'] === 'pending') {
                    continue;
                }
                $status_label = $req['request_status'] === 'approved' ? '승인됨' : '반려됨';
                $badge_class = $req['request_status'] === 'approved' ? 'active' : 'paused';
                ?>
            <li class="promo-admin-list__item review-delete-admin-list__item review-delete-admin-list__item--history">
                <div class="promo-admin-list__main">
                    <strong><?php echo get_text($req['shop_name'] ?: '업체 #'.(int) $req['shop_wr_id']); ?></strong>
                    <span class="promo-admin-list__badge promo-admin-list__badge--<?php echo $badge_class; ?>"><?php echo $status_label; ?></span>
                    <p class="promo-admin-list__meta">
                        요청 <?php echo get_text($req['request_mb_id']); ?>
                        · 처리 <?php echo get_text($req['processed_by']); ?>
                        · <?php echo $req['processed_datetime'] !== '0000-00-00 00:00:00' ? substr($req['processed_datetime'], 0, 16) : ''; ?>
                    </p>
                    <?php if (!empty($req['process_note'])) { ?>
                    <p class="review-delete-admin-list__reason">메모: <?php echo get_text($req['process_note']); ?></p>
                    <?php } ?>
                </div>
            </li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>
</main>

<script>
(function () {
  function postReviewDeleteAction(action, rdrId, note) {
    var fd = new FormData();
    fd.append('action', action);
    fd.append('rdr_id', String(rdrId));
    if (note) fd.append('process_note', note);
    return fetch('/proc/eottae-review-delete.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  document.querySelectorAll('[data-review-delete-approve]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('이 리뷰 삭제 요청을 승인하시겠습니까? 리뷰가 즉시 삭제됩니다.')) return;
      btn.disabled = true;
      postReviewDeleteAction('approve', btn.getAttribute('data-review-delete-approve'))
        .then(function (data) {
          if (data.success) location.reload();
          else alert(data.message || '처리에 실패했습니다.');
          btn.disabled = false;
        })
        .catch(function () {
          alert('네트워크 오류가 발생했습니다.');
          btn.disabled = false;
        });
    });
  });

  document.querySelectorAll('[data-review-delete-reject]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var note = window.prompt('반려 사유 (선택):', '');
      if (note === null) return;
      btn.disabled = true;
      postReviewDeleteAction('reject', btn.getAttribute('data-review-delete-reject'), note)
        .then(function (data) {
          if (data.success) location.reload();
          else alert(data.message || '처리에 실패했습니다.');
          btn.disabled = false;
        })
        .catch(function () {
          alert('네트워크 오류가 발생했습니다.');
          btn.disabled = false;
        });
    });
  });
})();
</script>

<?php
g5_page_end();
