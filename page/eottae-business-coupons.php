<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-business-coupons.php'));
}

if (!function_exists('eottae_is_business_member') || !eottae_is_business_member($member)) {
    alert('사업자 회원만 이용할 수 있습니다.', G5_URL.'/page/eottae-mypage.php');
}

include_once G5_LIB_PATH.'/eottae-business-coupon.lib.php';
eottae_business_coupon_ensure_schema();

$campaigns = eottae_business_coupon_campaigns($member['mb_id']);
$active_issues = eottae_business_coupon_issues($member['mb_id'], 'active', 100);
$used_issues = eottae_business_coupon_issues($member['mb_id'], 'used', 100);
$benefit_types = eottae_business_coupon_benefit_types();

g5_page_start('쿠폰 발행 관리');
?>

<main class="biz-coupon-page">
    <header class="biz-coupon-page__header">
        <div class="biz-coupon-page__header-top">
            <a href="<?php echo G5_URL; ?>/page/eottae-mypage.php" class="biz-coupon-page__back">← 마이페이지</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-business-coupon-guide.php" class="biz-coupon-page__guide-link">발행·사용 안내</a>
        </div>
        <h1 class="biz-coupon-page__title">쿠폰 발행 관리</h1>
        <p class="biz-coupon-page__desc">할인·무료 혜택 쿠폰을 만들고 회원에게 발행하세요. 손님이 쿠폰을 보여주면 화면에서 <strong>사용 완료</strong>를 누르거나, 이 페이지에서도 처리할 수 있습니다.</p>
    </header>

    <?php
    $callout_type = 'business';
    include G5_PATH.'/components/eottae/coupon-guide-callout.php';
    ?>

    <section class="biz-coupon-panel" aria-labelledby="biz-coupon-create-title">
        <h2 id="biz-coupon-create-title" class="biz-coupon-panel__title">1. 쿠폰 만들기</h2>
        <form class="biz-coupon-form" id="bizCouponCreateForm">
            <div class="biz-coupon-form__field">
                <label for="cp_benefit_type">쿠폰 유형</label>
                <select name="cp_benefit_type" id="cp_benefit_type" required>
                    <?php foreach ($benefit_types as $key => $label) { ?>
                    <option value="<?php echo $key; ?>"><?php echo get_text($label); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="biz-coupon-form__group" data-benefit-fields="percent order_discount_percent">
                <div class="biz-coupon-form__field">
                    <label for="cp_percent">할인율 (%)</label>
                    <input type="number" name="cp_percent" id="cp_percent" min="1" max="100" placeholder="10">
                </div>
            </div>

            <div class="biz-coupon-form__group" data-benefit-fields="visit_free order_discount_free" hidden>
                <div class="biz-coupon-form__field">
                    <label for="cp_free_item">무료 제공 항목</label>
                    <input type="text" name="cp_free_item" id="cp_free_item" maxlength="255" placeholder="예: 망고쉐이크 1잔">
                </div>
            </div>

            <div class="biz-coupon-form__group" data-benefit-fields="order_discount" hidden>
                <div class="biz-coupon-form__field">
                    <label for="cp_min_amount">주문 금액 조건</label>
                    <input type="text" name="cp_min_amount" id="cp_min_amount" maxlength="100" placeholder="예: 500페소">
                </div>
                <div class="biz-coupon-form__field">
                    <label for="cp_condition_menu">메뉴 조건</label>
                    <input type="text" name="cp_condition_menu" id="cp_condition_menu" maxlength="255" placeholder="예: 삼겹살 세트">
                </div>
                <div class="biz-coupon-form__field">
                    <label for="cp_order_benefit">주문 조건 혜택</label>
                    <select name="cp_order_benefit" id="cp_order_benefit">
                        <option value="percent">할인율 적용</option>
                        <option value="free">무료 제공</option>
                    </select>
                </div>
            </div>

            <div class="biz-coupon-form__row">
                <div class="biz-coupon-form__field">
                    <label for="cp_max_issue">발행 가능 수량</label>
                    <input type="number" name="cp_max_issue" id="cp_max_issue" min="1" max="10000" value="100" required>
                </div>
                <div class="biz-coupon-form__field">
                    <label for="cp_expires_at">만료일 (선택)</label>
                    <input type="date" name="cp_expires_at" id="cp_expires_at">
                </div>
            </div>

            <div class="biz-coupon-form__field">
                <label for="cp_title">쿠폰 이름 (선택)</label>
                <input type="text" name="cp_title" id="cp_title" maxlength="255" placeholder="비우면 자동 생성">
            </div>

            <button type="submit" class="biz-coupon-btn biz-coupon-btn--primary">쿠폰 생성</button>
            <p class="biz-coupon-form__status" data-biz-coupon-create-status aria-live="polite"></p>
        </form>
    </section>

    <section class="biz-coupon-panel" aria-labelledby="biz-coupon-issue-title">
        <h2 id="biz-coupon-issue-title" class="biz-coupon-panel__title">2. 회원에게 발행</h2>
        <?php if (empty($campaigns)) { ?>
        <p class="biz-coupon-empty">먼저 쿠폰을 생성해 주세요.</p>
        <?php } else { ?>
        <form class="biz-coupon-form" id="bizCouponIssueForm">
            <div class="biz-coupon-form__field">
                <label for="issue_cp_id">쿠폰 선택</label>
                <select name="cp_id" id="issue_cp_id" required>
                    <?php foreach ($campaigns as $campaign) {
                        $issued = (int) $campaign['issued_count']; ?>
                    <option value="<?php echo (int) $campaign['cp_id']; ?>">
                        <?php echo get_text($campaign['cp_title']); ?>
                        (발행 <?php echo number_format($issued); ?><?php echo (int) $campaign['cp_max_issue'] > 0 ? ' / '.number_format((int) $campaign['cp_max_issue']) : ''; ?>)
                    </option>
                    <?php } ?>
                </select>
            </div>
            <div class="biz-coupon-form__row">
                <div class="biz-coupon-form__field">
                    <label for="target_mb_id">회원 아이디</label>
                    <input type="text" name="target_mb_id" id="target_mb_id" maxlength="20" required placeholder="발급받을 회원 mb_id">
                </div>
                <div class="biz-coupon-form__field">
                    <label for="issue_quantity">발행 장수</label>
                    <input type="number" name="quantity" id="issue_quantity" min="1" max="100" value="1" required>
                </div>
            </div>
            <button type="submit" class="biz-coupon-btn biz-coupon-btn--primary">회원에게 발행</button>
            <p class="biz-coupon-form__status" data-biz-coupon-issue-status aria-live="polite"></p>
        </form>
        <?php } ?>
    </section>

    <section class="biz-coupon-panel" aria-labelledby="biz-coupon-redeem-title">
        <h2 id="biz-coupon-redeem-title" class="biz-coupon-panel__title">3. 매장 사용 처리</h2>
        <p class="biz-coupon-panel__hint">고객이 쿠폰함에서 쿠폰을 보여주면 화면의 사용 완료를 누르거나, 여기서 쿠폰번호·회원아이디로 검색 후 처리할 수 있습니다.</p>
        <form class="biz-coupon-form biz-coupon-form--inline" id="bizCouponRedeemForm">
            <div class="biz-coupon-form__field">
                <label for="redeem_lookup">쿠폰번호 / 회원아이디</label>
                <input type="text" name="lookup" id="redeem_lookup" maxlength="20" placeholder="8자리 쿠폰번호 또는 mb_id" required>
            </div>
            <button type="submit" class="biz-coupon-btn biz-coupon-btn--primary">사용 완료 처리</button>
            <p class="biz-coupon-form__status" data-biz-coupon-redeem-status aria-live="polite"></p>
        </form>
    </section>

    <section class="biz-coupon-panel">
        <h2 class="biz-coupon-panel__title">사용 대기 (<?php echo number_format(count($active_issues)); ?>)</h2>
        <?php if (empty($active_issues)) { ?>
        <p class="biz-coupon-empty">사용 대기 중인 쿠폰이 없습니다.</p>
        <?php } else { ?>
        <ul class="biz-coupon-issue-list">
            <?php foreach ($active_issues as $issue) { ?>
            <li class="biz-coupon-issue-list__item">
                <div class="biz-coupon-issue-list__main">
                    <strong><?php echo get_text($issue['cp_title']); ?></strong>
                    <span><?php echo get_text($issue['mb_nick']); ?> (<?php echo get_text($issue['mb_id']); ?>)</span>
                    <span class="biz-coupon-issue-list__code">쿠폰번호 <?php echo get_text($issue['ci_code']); ?></span>
                    <span class="biz-coupon-issue-list__date">발행 <?php echo substr($issue['ci_datetime'], 0, 16); ?></span>
                </div>
                <button type="button" class="biz-coupon-btn" data-biz-coupon-redeem-id="<?php echo (int) $issue['ci_id']; ?>">사용 완료</button>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <section class="biz-coupon-panel">
        <h2 class="biz-coupon-panel__title">사용 완료 내역 (<?php echo number_format(count($used_issues)); ?>)</h2>
        <?php if (empty($used_issues)) { ?>
        <p class="biz-coupon-empty">아직 사용 완료된 쿠폰이 없습니다.</p>
        <?php } else { ?>
        <ul class="biz-coupon-issue-list biz-coupon-issue-list--used">
            <?php foreach ($used_issues as $issue) { ?>
            <li class="biz-coupon-issue-list__item">
                <div class="biz-coupon-issue-list__main">
                    <strong><?php echo get_text($issue['cp_title']); ?></strong>
                    <span><?php echo get_text($issue['mb_nick']); ?> (<?php echo get_text($issue['mb_id']); ?>)</span>
                    <span class="biz-coupon-issue-list__code">쿠폰번호 <?php echo get_text($issue['ci_code']); ?></span>
                    <span class="biz-coupon-issue-list__date">사용 <?php echo substr($issue['ci_used_datetime'], 0, 16); ?></span>
                </div>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>
</main>

<script>
(function () {
  var typeSelect = document.getElementById('cp_benefit_type');
  var orderBenefit = document.getElementById('cp_order_benefit');

  function syncBenefitFields() {
    if (!typeSelect) return;
    var type = typeSelect.value;
    document.querySelectorAll('[data-benefit-fields]').forEach(function (group) {
      var types = (group.getAttribute('data-benefit-fields') || '').split(/\s+/);
      var show = types.indexOf(type) !== -1;
      if (type === 'order_discount') {
        if (types.indexOf('order_discount_percent') !== -1) {
          show = orderBenefit && orderBenefit.value === 'percent';
        } else if (types.indexOf('order_discount_free') !== -1) {
          show = orderBenefit && orderBenefit.value === 'free';
        }
      }
      group.hidden = !show;
    });
  }

  if (typeSelect) {
    typeSelect.addEventListener('change', syncBenefitFields);
    syncBenefitFields();
  }
  if (orderBenefit) orderBenefit.addEventListener('change', syncBenefitFields);

  var createForm = document.getElementById('bizCouponCreateForm');
  if (createForm) {
    createForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(createForm);
      fd.append('action', 'create');
      var statusEl = document.querySelector('[data-biz-coupon-create-status]');
      var btn = createForm.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      fetch('/proc/eottae-business-coupon.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (statusEl) {
            statusEl.textContent = data.message || '';
            statusEl.classList.toggle('is-error', !data.success);
          }
          if (data.success) setTimeout(function () { location.reload(); }, 600);
        })
        .finally(function () { if (btn) btn.disabled = false; });
    });
  }

  var issueForm = document.getElementById('bizCouponIssueForm');
  if (issueForm) {
    issueForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(issueForm);
      fd.append('action', 'issue');
      var statusEl = document.querySelector('[data-biz-coupon-issue-status]');
      var btn = issueForm.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      fetch('/proc/eottae-business-coupon.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (statusEl) {
            statusEl.textContent = data.message || '';
            statusEl.classList.toggle('is-error', !data.success);
          }
          if (data.success) setTimeout(function () { location.reload(); }, 600);
        })
        .finally(function () { if (btn) btn.disabled = false; });
    });
  }

  var redeemForm = document.getElementById('bizCouponRedeemForm');
  if (redeemForm) {
    redeemForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(redeemForm);
      fd.append('action', 'redeem');
      var statusEl = document.querySelector('[data-biz-coupon-redeem-status]');
      var btn = redeemForm.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      fetch('/proc/eottae-business-coupon.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (statusEl) {
            statusEl.textContent = data.message || '';
            statusEl.classList.toggle('is-error', !data.success);
          }
          if (data.success) setTimeout(function () { location.reload(); }, 600);
        })
        .finally(function () { if (btn) btn.disabled = false; });
    });
  }

  document.querySelectorAll('[data-biz-coupon-redeem-id]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!confirm('이 쿠폰을 사용 완료 처리할까요?')) return;
      var fd = new FormData();
      fd.append('action', 'redeem');
      fd.append('ci_id', btn.getAttribute('data-biz-coupon-redeem-id'));
      btn.disabled = true;
      fetch('/proc/eottae-business-coupon.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          alert(data.message || (data.success ? '처리되었습니다.' : '오류'));
          if (data.success) location.reload();
          else btn.disabled = false;
        })
        .catch(function () {
          alert('요청 중 오류가 발생했습니다.');
          btn.disabled = false;
        });
    });
  });
})();
</script>

<?php
g5_page_end();
