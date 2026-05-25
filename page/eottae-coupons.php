<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-coupons.php'));
}

include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
eottae_coupon_ensure_ready();
eottae_business_coupon_ensure_schema();

$welcome = eottae_coupon_ensure_welcome($member['mb_id']);

$active_coupons = eottae_coupon_get_member_list($member['mb_id'], 'active');
$used_coupons = eottae_coupon_get_member_list($member['mb_id'], 'used');
$active_count = count($active_coupons);

g5_page_start('쿠폰함');
?>

<main class="mypage-subpage">
    <?php eottae_render_mypage_back(); ?>
    <a href="<?php echo G5_URL; ?>/page/eottae-coupon-guide.php" class="mypage-subpage__guide-link">쿠폰 사용 방법 안내 →</a>
    <h1 class="mypage-subpage__title">쿠폰함</h1>

    <?php
    $callout_type = 'member';
    include G5_PATH.'/components/eottae/coupon-guide-callout.php';
    ?>

    <?php if (!empty($welcome['ok']) && empty($welcome['duplicate'])) { ?>
    <p class="mypage-subpage__notice" role="status">신규 가입 웰컴 쿠폰이 발급되었습니다.</p>
    <?php } ?>

    <section class="mypage-coupon-summary" style="margin-bottom:20px">
        <div class="mypage-coupon-summary__box" style="grid-column:1/-1">
            <p class="mypage-coupon-summary__label">사용 가능 쿠폰</p>
            <p class="mypage-coupon-summary__value"><?php echo number_format($active_count); ?></p>
        </div>
    </section>

    <?php if (empty($active_coupons) && empty($used_coupons)) { ?>
    <div class="empty-state">
        <p class="empty-state__title">보유 쿠폰이 없습니다</p>
        <p>회원 가입 시 웰컴 쿠폰이 자동 발급됩니다. 업체에서 발행한 쿠폰도 이곳에 표시됩니다.</p>
    </div>
    <?php } else { ?>

    <?php if (!empty($active_coupons)) { ?>
    <h2 class="mypage-subpage__subtitle" style="font-size:1rem;margin:0 0 12px">사용 가능</h2>
    <div class="mypage-coupon-list" style="display:grid;gap:12px;margin-bottom:24px">
        <?php foreach ($active_coupons as $coupon) {
            $is_business = isset($coupon['cp_type']) && $coupon['cp_type'] === 'business';
            $benefit = $is_business && function_exists('eottae_business_coupon_format_benefit')
                ? eottae_business_coupon_format_benefit($coupon)
                : get_text($coupon['cp_desc']);
            ?>
        <article class="coupon-card<?php echo $is_business ? ' coupon-card--business' : ''; ?>" data-coupon-id="<?php echo (int) $coupon['ci_id']; ?>">
            <?php if ($is_business) { ?><p class="coupon-card__badge">업체 쿠폰</p><?php } ?>
            <h3 class="coupon-card__title"><?php echo get_text($coupon['cp_title']); ?></h3>
            <p class="coupon-card__desc"><?php echo $benefit; ?></p>
            <?php if ($is_business && !empty($coupon['ci_code'])) { ?>
            <p class="coupon-card__code">쿠폰번호 <strong><?php echo get_text($coupon['ci_code']); ?></strong></p>
            <?php } ?>
            <p class="coupon-card__meta">발급일 <?php echo substr($coupon['ci_datetime'], 0, 10); ?></p>
            <?php if ($is_business) { ?>
            <button type="button" class="eottae-btn-write coupon-card__show-btn" data-coupon-show="<?php echo (int) $coupon['ci_id']; ?>"
                data-coupon-ci-id="<?php echo (int) $coupon['ci_id']; ?>"
                data-coupon-title="<?php echo htmlspecialchars(get_text($coupon['cp_title']), ENT_QUOTES, 'UTF-8'); ?>"
                data-coupon-benefit="<?php echo htmlspecialchars($benefit, ENT_QUOTES, 'UTF-8'); ?>"
                data-coupon-code="<?php echo htmlspecialchars(get_text($coupon['ci_code']), ENT_QUOTES, 'UTF-8'); ?>"
                data-coupon-member="<?php echo htmlspecialchars(get_text($member['mb_nick']).' ('.$member['mb_id'].')', ENT_QUOTES, 'UTF-8'); ?>">매장에서 보여주기</button>
            <?php } else { ?>
            <button type="button" class="eottae-btn-write coupon-card__use-btn" data-coupon-use="<?php echo (int) $coupon['ci_id']; ?>">사용 완료 처리</button>
            <?php } ?>
        </article>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if (!empty($used_coupons)) { ?>
    <h2 class="mypage-subpage__subtitle" style="font-size:1rem;margin:0 0 12px">사용 완료</h2>
    <div class="mypage-coupon-list" style="display:grid;gap:12px">
        <?php foreach ($used_coupons as $coupon) { ?>
        <article class="coupon-card" style="opacity:0.65">
            <h3 class="coupon-card__title"><?php echo get_text($coupon['cp_title']); ?></h3>
            <p class="coupon-card__desc"><?php echo get_text($coupon['cp_desc']); ?></p>
            <p class="coupon-card__meta">사용일 <?php echo substr($coupon['ci_used_datetime'], 0, 10); ?></p>
        </article>
        <?php } ?>
    </div>
    <?php } ?>

    <?php } ?>
</main>

<div class="coupon-show-modal" id="couponShowModal" hidden aria-hidden="true">
    <div class="coupon-show-modal__backdrop" data-coupon-show-close></div>
    <div class="coupon-show-modal__panel" role="dialog" aria-labelledby="couponShowTitle">
        <button type="button" class="coupon-show-modal__close" data-coupon-show-close aria-label="닫기">×</button>
        <p class="coupon-show-modal__label">매장에서 보여주세요</p>
        <h2 id="couponShowTitle" class="coupon-show-modal__title"></h2>
        <p class="coupon-show-modal__benefit"></p>
        <p class="coupon-show-modal__member"></p>
        <p class="coupon-show-modal__code"></p>
        <p class="coupon-show-modal__hint">혜택 적용 후 아래 <strong>사용 완료</strong>를 눌러 주세요. 회원·직원 누구든 눌러도 됩니다.</p>
        <button type="button" class="coupon-show-modal__use-btn" id="couponShowUseBtn" data-coupon-use="">사용 완료</button>
    </div>
</div>

<script>
(function () {
  document.querySelectorAll('[data-coupon-use]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var ciId = btn.getAttribute('data-coupon-use');
      if (!ciId) return;
      if (!confirm('쿠폰을 사용 완료 처리할까요? 한 번 처리하면 다시 사용할 수 없습니다.')) return;
      btn.disabled = true;
      var body = new FormData();
      body.append('ci_id', btn.getAttribute('data-coupon-use'));
      fetch('/proc/eottae-coupon-use.php', { method: 'POST', body: body, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          alert(data.message || (data.success ? '처리되었습니다.' : '오류'));
          if (data.success) location.reload();
          else btn.disabled = false;
        });
    });
  });

  var modal = document.getElementById('couponShowModal');
  if (!modal) return;
  var titleEl = modal.querySelector('.coupon-show-modal__title');
  var benefitEl = modal.querySelector('.coupon-show-modal__benefit');
  var memberEl = modal.querySelector('.coupon-show-modal__member');
  var codeEl = modal.querySelector('.coupon-show-modal__code');

  document.querySelectorAll('[data-coupon-show]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (titleEl) titleEl.textContent = btn.getAttribute('data-coupon-title') || '';
      if (benefitEl) benefitEl.textContent = btn.getAttribute('data-coupon-benefit') || '';
      if (memberEl) memberEl.textContent = btn.getAttribute('data-coupon-member') || '';
      if (codeEl) codeEl.textContent = '쿠폰번호 ' + (btn.getAttribute('data-coupon-code') || '');
      var useBtn = document.getElementById('couponShowUseBtn');
      if (useBtn) {
        useBtn.setAttribute('data-coupon-use', btn.getAttribute('data-coupon-ci-id') || '');
        useBtn.disabled = false;
      }
      modal.hidden = false;
      modal.setAttribute('aria-hidden', 'false');
    });
  });

  modal.querySelectorAll('[data-coupon-show-close]').forEach(function (el) {
    el.addEventListener('click', function () {
      modal.hidden = true;
      modal.setAttribute('aria-hidden', 'true');
    });
  });
})();
</script>

<?php
g5_page_end();
