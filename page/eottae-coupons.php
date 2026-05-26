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
    <div class="mypage-coupon-list mypage-coupon-list--visual">
        <?php
        $member_label = get_text($member['mb_nick']).' ('.$member['mb_id'].')';
        foreach ($active_coupons as $coupon) {
            eottae_render_coupon_visual_card($coupon, array(
                'member_label' => $member_label,
                'meta_line'    => '발급일 '.substr($coupon['ci_datetime'], 0, 10),
            ));
        }
        ?>
    </div>
    <?php } ?>

    <?php if (!empty($used_coupons)) { ?>
    <h2 class="mypage-subpage__subtitle" style="font-size:1rem;margin:0 0 12px">사용 완료</h2>
    <div class="mypage-coupon-list mypage-coupon-list--visual">
        <?php foreach ($used_coupons as $coupon) {
            eottae_render_coupon_visual_card($coupon, array(
                'used'          => true,
                'show_actions'  => false,
                'meta_line'     => '사용일 '.substr($coupon['ci_used_datetime'], 0, 16),
            ));
        } ?>
    </div>
    <?php } ?>

    <?php } ?>
</main>

<div class="coupon-show-modal" id="couponShowModal" hidden aria-hidden="true">
    <div class="coupon-show-modal__backdrop" data-coupon-show-close></div>
    <div class="coupon-show-modal__panel" role="dialog" aria-labelledby="couponShowTitle">
        <button type="button" class="coupon-show-modal__close" data-coupon-show-close aria-label="닫기">×</button>
        <p class="coupon-show-modal__label">매장에서 보여주세요</p>
        <div class="coupon-show-modal__ticket" id="couponShowTicket" aria-live="polite"></div>
        <p class="coupon-show-modal__member"></p>
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
  var ticketSlot = document.getElementById('couponShowTicket');
  var memberEl = modal.querySelector('.coupon-show-modal__member');

  document.querySelectorAll('[data-coupon-show]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var card = btn.closest('.coupon-ticket');
      if (ticketSlot && card) {
        var visual = card.querySelector('.coupon-ticket__visual');
        ticketSlot.innerHTML = '';
        if (visual) {
          var clone = visual.cloneNode(true);
          clone.classList.add('coupon-ticket__visual--modal');
          ticketSlot.appendChild(clone);
        }
      }
      if (memberEl) {
        memberEl.textContent = card
          ? (card.getAttribute('data-coupon-member') || '')
          : (btn.getAttribute('data-coupon-member') || '');
      }
      var useBtn = document.getElementById('couponShowUseBtn');
      if (useBtn) {
        useBtn.setAttribute('data-coupon-use', btn.getAttribute('data-coupon-ci-id') || btn.getAttribute('data-coupon-show') || '');
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
