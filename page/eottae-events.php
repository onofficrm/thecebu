<?php
include_once(dirname(__FILE__).'/_init.php');

include_once G5_LIB_PATH.'/eottae-promo-coupon.lib.php';
eottae_promo_coupon_ensure_schema();

$events = eottae_get_events(20);
$active = eottae_get_events(10, '진행중');
$mb_id = !empty($is_member) && !empty($member['mb_id']) ? $member['mb_id'] : '';
$promos = eottae_promo_member_visible_list($mb_id);
$attendance_streak = $mb_id !== '' ? eottae_attendance_get_streak($mb_id) : 0;
$attendance_today = $mb_id !== '' ? eottae_attendance_checked_today($mb_id) : false;
$has_attendance_promo = false;
foreach ($promos as $p) {
    if ($p['trigger_type'] === 'attendance_streak') {
        $has_attendance_promo = true;
        break;
    }
}

g5_page_start('이벤트·프로모션');
?>

<main class="mypage-subpage event-promo-page">
    <?php eottae_render_mypage_back(); ?>
    <header class="event-promo-page__header">
        <h1 class="mypage-subpage__title">이벤트·프로모션</h1>
        <?php if ($is_admin === 'super') { ?>
        <div class="event-promo-page__admin-links">
            <a href="<?php echo G5_URL; ?>/page/eottae-admin-promo-coupons.php" class="event-promo-page__admin-link">프로모션 쿠폰 관리</a>
            <?php
            include_once G5_LIB_PATH.'/eottae-review-delete.lib.php';
            $review_delete_pending = eottae_review_delete_pending_count();
            ?>
            <a href="<?php echo G5_URL; ?>/page/eottae-admin-review-deletes.php" class="event-promo-page__admin-link">
                리뷰 삭제 요청<?php if ($review_delete_pending > 0) { ?> (<?php echo number_format($review_delete_pending); ?>)<?php } ?>
            </a>
        </div>
        <?php } ?>
    </header>

    <?php if (!empty($promos) || $has_attendance_promo) { ?>
    <section class="event-section promo-rewards-section">
        <h2 class="event-section__title">쿠폰 미션 · 참여하기</h2>
        <p class="promo-rewards-section__intro">미션을 완료하거나 이벤트에 참여하면 쿠폰이 지갑에 자동 저장됩니다. <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php">내 쿠폰함</a></p>

        <?php if ($has_attendance_promo) { ?>
        <div class="promo-attendance-card">
            <div class="promo-attendance-card__main">
                <strong>매일 출석 체크</strong>
                <p>연속 <?php echo $attendance_streak; ?>일<?php if (!$is_member) { ?> · 로그인 후 참여<?php } ?></p>
            </div>
            <?php if ($is_member) { ?>
            <button type="button" class="promo-reward-btn promo-reward-btn--primary" id="promoAttendanceBtn" <?php echo $attendance_today ? 'disabled' : ''; ?>>
                <?php echo $attendance_today ? '오늘 출석 완료' : '출석하기'; ?>
            </button>
            <?php } else { ?>
            <a href="<?php echo function_exists('eottae_login_url') ? eottae_login_url(G5_URL.'/page/eottae-events.php') : G5_BBS_URL.'/login.php'; ?>" class="promo-reward-btn">로그인</a>
            <?php } ?>
            <p class="promo-attendance-card__status" data-promo-attendance-status role="status"></p>
        </div>
        <?php } ?>

        <div class="promo-reward-grid">
            <?php foreach ($promos as $promo) {
                $cfg = isset($promo['config']) ? $promo['config'] : eottae_promo_parse_config($promo);
                $type = (string) $promo['trigger_type'];
                if ($type === 'attendance_streak') {
                    continue;
                }
                $done = !empty($promo['member_awarded']);
                $can = !empty($promo['can_participate']) && !$done;
                ?>
            <article class="promo-reward-card<?php echo $done ? ' promo-reward-card--done' : ''; ?>">
                <span class="promo-reward-card__badge"><?php echo eottae_promo_trigger_type_label($type); ?></span>
                <h3 class="promo-reward-card__title"><?php echo get_text($promo['promo_title']); ?></h3>
                <?php if (!empty($promo['promo_desc'])) { ?>
                <p class="promo-reward-card__desc"><?php echo get_text($promo['promo_desc']); ?></p>
                <?php } ?>

                <?php if ($type === 'quiz' && !empty($cfg['question'])) { ?>
                <p class="promo-reward-card__quiz-q"><strong>Q.</strong> <?php echo get_text($cfg['question']); ?></p>
                <?php if (!empty($cfg['hint'])) { ?>
                <p class="promo-reward-card__hint">힌트: <?php echo get_text($cfg['hint']); ?></p>
                <?php } ?>
                <?php if ($is_member && $can) { ?>
                <form class="promo-quiz-form" data-promo-quiz="<?php echo (int) $promo['promo_id']; ?>">
                    <input type="text" name="answer" placeholder="정답 입력" required maxlength="200">
                    <button type="submit" class="promo-reward-btn promo-reward-btn--primary">제출</button>
                </form>
                <?php } ?>
                <?php } elseif ($type === 'claim') { ?>
                <?php if ((int) $promo['promo_max_total'] > 0) { ?>
                <p class="promo-reward-card__meta">남은 수량 <?php echo max(0, (int) $promo['promo_max_total'] - (int) $promo['awarded_count']); ?>장</p>
                <?php } ?>
                <?php if ($is_member && $can) { ?>
                <button type="button" class="promo-reward-btn promo-reward-btn--primary" data-promo-claim="<?php echo (int) $promo['promo_id']; ?>">쿠폰 받기</button>
                <?php } ?>
                <?php } ?>

                <?php if (!$is_member) { ?>
                <p class="promo-reward-card__login"><a href="<?php echo function_exists('eottae_login_url') ? eottae_login_url(G5_URL.'/page/eottae-events.php') : G5_BBS_URL.'/login.php'; ?>">로그인</a> 후 참여</p>
                <?php } elseif ($done) { ?>
                <p class="promo-reward-card__status is-done">참여 완료 · 쿠폰함에서 확인</p>
                <?php } elseif (!$can && !empty($promo['status_message'])) { ?>
                <p class="promo-reward-card__status is-muted"><?php echo get_text($promo['status_message']); ?></p>
                <?php } ?>

                <p class="promo-reward-card__feedback" data-promo-feedback="<?php echo (int) $promo['promo_id']; ?>" role="status"></p>
            </article>
            <?php } ?>
        </div>
    </section>
    <?php } ?>

    <?php if (empty($events) && empty($promos)) { ?>
    <div class="empty-state">
        <p class="empty-state__title">진행 중인 이벤트가 없습니다</p>
        <p>세부어때 업체·커뮤니티 이벤트가 등록되면 이곳에 표시됩니다.</p>
    </div>
    <?php } else { ?>
        <?php if (!empty($active)) { ?>
        <section class="event-section">
            <h2 class="event-section__title">진행 중</h2>
            <div class="event-card-grid">
                <?php foreach ($active as $event) { ?>
                <a href="<?php echo $event['href']; ?>" class="event-card event-card--active">
                    <span class="event-card__badge"><?php echo $event['category'] ?: '진행중'; ?></span>
                    <h3 class="event-card__title"><?php echo $event['subject']; ?></h3>
                    <p class="event-card__desc"><?php echo $event['content']; ?></p>
                    <time class="event-card__date"><?php echo substr($event['datetime'], 0, 10); ?></time>
                </a>
                <?php } ?>
            </div>
        </section>
        <?php } ?>

        <?php if (!empty($events)) { ?>
        <section class="event-section">
            <h2 class="event-section__title">전체 이벤트</h2>
            <div class="event-card-grid">
                <?php foreach ($events as $event) { ?>
                <a href="<?php echo $event['href']; ?>" class="event-card">
                    <?php if ($event['category']) { ?>
                    <span class="event-card__badge"><?php echo $event['category']; ?></span>
                    <?php } ?>
                    <h3 class="event-card__title"><?php echo $event['subject']; ?></h3>
                    <p class="event-card__desc"><?php echo $event['content']; ?></p>
                    <time class="event-card__date"><?php echo substr($event['datetime'], 0, 10); ?></time>
                </a>
                <?php } ?>
            </div>
        </section>
        <?php } ?>
    <?php } ?>
</main>

<script>
(function () {
  function postPromo(action, body) {
    var fd = body instanceof FormData ? body : new FormData();
    fd.append('action', action);
    return fetch('/proc/eottae-promo-coupon.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  var attendBtn = document.getElementById('promoAttendanceBtn');
  if (attendBtn) {
    attendBtn.addEventListener('click', function () {
      attendBtn.disabled = true;
      postPromo('attendance', new FormData())
        .then(function (data) {
          var el = document.querySelector('[data-promo-attendance-status]');
          if (el) {
            el.textContent = data.message || '';
            el.classList.toggle('is-error', !data.success);
          }
          if (data.success) {
            attendBtn.textContent = '오늘 출석 완료';
            setTimeout(function () { location.reload(); }, 800);
          } else {
            attendBtn.disabled = false;
          }
        });
    });
  }

  document.querySelectorAll('[data-promo-claim]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var promoId = btn.getAttribute('data-promo-claim');
      var fd = new FormData();
      fd.append('promo_id', promoId);
      btn.disabled = true;
      postPromo('claim', fd).then(function (data) {
        var fb = document.querySelector('[data-promo-feedback="' + promoId + '"]');
        if (fb) {
          fb.textContent = data.message || '';
          fb.classList.toggle('is-error', !data.success);
        }
        if (data.success) setTimeout(function () { location.reload(); }, 800);
        else btn.disabled = false;
      });
    });
  });

  document.querySelectorAll('[data-promo-quiz]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var promoId = form.getAttribute('data-promo-quiz');
      var fd = new FormData(form);
      fd.append('promo_id', promoId);
      var btn = form.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      postPromo('quiz', fd).then(function (data) {
        var fb = document.querySelector('[data-promo-feedback="' + promoId + '"]');
        if (fb) {
          fb.textContent = data.message || '';
          fb.classList.toggle('is-error', !data.success);
        }
        if (data.success) setTimeout(function () { location.reload(); }, 800);
        else if (btn) btn.disabled = false;
      });
    });
  });
})();
</script>

<?php
g5_page_end();
