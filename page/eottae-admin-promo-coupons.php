<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-promo-coupon.lib.php';
eottae_promo_coupon_ensure_schema();

$promos = eottae_promo_coupon_list('', 100);
$trigger_types = eottae_promo_trigger_types();

g5_page_start('프로모션 쿠폰 관리');
?>

<main class="promo-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo G5_URL; ?>/page/eottae-events.php" class="promo-admin-page__back">← 이벤트·프로모션</a>
        </div>
        <h1 class="promo-admin-page__title">프로모션 쿠폰 관리</h1>
        <p class="promo-admin-page__desc">이벤트·미션 조건에 맞춰 회원에게 자동 또는 수동으로 쿠폰을 지급합니다. 퀴즈, 글 작성, 조회수, 출석, 우수 댓글 등 다양한 트리거를 설정할 수 있습니다.</p>
    </header>

    <section class="promo-admin-panel" aria-labelledby="promo-create-title">
        <h2 id="promo-create-title" class="promo-admin-panel__title">1. 프로모션 만들기</h2>
        <form class="promo-admin-form" id="promoCreateForm">
            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="promo_title">프로모션 제목</label>
                    <input type="text" name="promo_title" id="promo_title" maxlength="255" required placeholder="예: 30일 출석 미션 쿠폰">
                </div>
                <div class="promo-admin-form__field">
                    <label for="trigger_type">지급 조건</label>
                    <select name="trigger_type" id="trigger_type" required>
                        <?php foreach ($trigger_types as $key => $label) { ?>
                        <option value="<?php echo $key; ?>"><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="promo-admin-form__field">
                <label for="promo_desc">설명 (회원에게 표시)</label>
                <textarea name="promo_desc" id="promo_desc" rows="3" placeholder="참여 방법과 혜택을 안내해 주세요."></textarea>
            </div>

            <div class="promo-admin-form__group" data-trigger-fields="quiz" hidden>
                <div class="promo-admin-form__field">
                    <label for="quiz_question">퀴즈 질문</label>
                    <input type="text" name="quiz_question" id="quiz_question" placeholder="예: 세부의 수도는?">
                </div>
                <div class="promo-admin-form__field">
                    <label for="quiz_answer">정답</label>
                    <input type="text" name="quiz_answer" id="quiz_answer" placeholder="대소문자·공백 무시">
                </div>
                <div class="promo-admin-form__field">
                    <label for="quiz_hint">힌트 (선택)</label>
                    <input type="text" name="quiz_hint" id="quiz_hint">
                </div>
            </div>

            <div class="promo-admin-form__group" data-trigger-fields="post_count" hidden>
                <div class="promo-admin-form__field">
                    <label for="min_posts">필요 글 수</label>
                    <input type="number" name="min_posts" id="min_posts" min="1" placeholder="10">
                    <p class="promo-admin-form__hint">커뮤니티 게시글 기준 (댓글 제외)</p>
                </div>
            </div>

            <div class="promo-admin-form__group" data-trigger-fields="post_views" hidden>
                <div class="promo-admin-form__field">
                    <label for="min_views">필요 조회수</label>
                    <input type="number" name="min_views" id="min_views" min="1" placeholder="1000">
                    <p class="promo-admin-form__hint">글 작성자에게 1회 지급</p>
                </div>
            </div>

            <div class="promo-admin-form__group" data-trigger-fields="attendance_streak" hidden>
                <div class="promo-admin-form__field">
                    <label for="attendance_days">연속 출석 일수</label>
                    <input type="number" name="attendance_days" id="attendance_days" min="1" max="365" placeholder="30">
                </div>
            </div>

            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="promo_max_total">전체 발급 한도 (0=무제한)</label>
                    <input type="number" name="promo_max_total" id="promo_max_total" min="0" value="0">
                </div>
                <div class="promo-admin-form__field">
                    <label for="promo_max_per_member">회원당 한도</label>
                    <input type="number" name="promo_max_per_member" id="promo_max_per_member" min="1" value="1">
                </div>
            </div>

            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="promo_starts_at">시작일</label>
                    <input type="date" name="promo_starts_at" id="promo_starts_at">
                </div>
                <div class="promo-admin-form__field">
                    <label for="promo_ends_at">종료일</label>
                    <input type="date" name="promo_ends_at" id="promo_ends_at">
                </div>
                <div class="promo-admin-form__field">
                    <label for="cp_expires_at">쿠폰 만료일</label>
                    <input type="date" name="cp_expires_at" id="cp_expires_at">
                </div>
            </div>

            <p class="promo-admin-form__status" data-promo-create-status role="status"></p>
            <button type="submit" class="promo-admin-btn promo-admin-btn--primary">프로모션 등록</button>
        </form>
    </section>

    <section class="promo-admin-panel" aria-labelledby="promo-grant-title">
        <h2 id="promo-grant-title" class="promo-admin-panel__title">2. 수동 지급</h2>
        <form class="promo-admin-form" id="promoGrantForm">
            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="grant_promo_id">프로모션</label>
                    <select name="promo_id" id="grant_promo_id" required>
                        <option value="">선택</option>
                        <?php foreach ($promos as $p) {
                            if ($p['trigger_type'] !== 'admin_grant') {
                                continue;
                            } ?>
                        <option value="<?php echo (int) $p['promo_id']; ?>"><?php echo get_text($p['promo_title']); ?> (<?php echo eottae_promo_trigger_type_label($p['trigger_type']); ?>)</option>
                        <?php } ?>
                    </select>
                </div>
                <div class="promo-admin-form__field">
                    <label for="grant_target_mb_id">회원 아이디</label>
                    <input type="text" name="target_mb_id" id="grant_target_mb_id" required placeholder="mb_id">
                </div>
            </div>
            <p class="promo-admin-form__status" data-promo-grant-status role="status"></p>
            <button type="submit" class="promo-admin-btn">회원에게 지급</button>
        </form>

        <form class="promo-admin-form promo-admin-form--comment" id="promoBestCommentForm">
            <h3 class="promo-admin-form__subtitle">우수 댓글 선정</h3>
            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="bc_promo_id">프로모션</label>
                    <select name="promo_id" id="bc_promo_id" required>
                        <option value="">선택</option>
                        <?php foreach ($promos as $p) {
                            if ($p['trigger_type'] !== 'best_comment') {
                                continue;
                            } ?>
                        <option value="<?php echo (int) $p['promo_id']; ?>"><?php echo get_text($p['promo_title']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="promo-admin-form__field">
                    <label for="bc_bo_table">게시판</label>
                    <input type="text" name="bo_table" id="bc_bo_table" value="<?php echo defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community'; ?>" required>
                </div>
                <div class="promo-admin-form__field">
                    <label for="bc_wr_id">원글 wr_id</label>
                    <input type="number" name="wr_id" id="bc_wr_id" min="1" required>
                </div>
                <div class="promo-admin-form__field">
                    <label for="bc_comment_wr_id">댓글 wr_id</label>
                    <input type="number" name="comment_wr_id" id="bc_comment_wr_id" min="1" required>
                </div>
            </div>
            <p class="promo-admin-form__status" data-promo-bc-status role="status"></p>
            <button type="submit" class="promo-admin-btn">댓글 작성자에게 지급</button>
        </form>
    </section>

    <section class="promo-admin-panel" aria-labelledby="promo-list-title">
        <h2 id="promo-list-title" class="promo-admin-panel__title">3. 등록된 프로모션</h2>
        <?php if (empty($promos)) { ?>
        <p class="promo-admin-empty">등록된 프로모션이 없습니다.</p>
        <?php } else { ?>
        <ul class="promo-admin-list">
            <?php foreach ($promos as $p) {
                $cfg = eottae_promo_parse_config($p);
                $active = eottae_promo_coupon_is_active($p);
                ?>
            <li class="promo-admin-list__item">
                <div class="promo-admin-list__main">
                    <strong><?php echo get_text($p['promo_title']); ?></strong>
                    <span class="promo-admin-list__badge promo-admin-list__badge--<?php echo $active ? 'active' : 'paused'; ?>">
                        <?php echo $active ? '진행중' : get_text($p['promo_status']); ?>
                    </span>
                    <span class="promo-admin-list__type"><?php echo eottae_promo_trigger_type_label($p['trigger_type']); ?></span>
                    <?php if (!empty($p['promo_desc'])) { ?>
                    <p class="promo-admin-list__desc"><?php echo get_text($p['promo_desc']); ?></p>
                    <?php } ?>
                    <?php if ($p['trigger_type'] === 'quiz' && !empty($cfg['question'])) { ?>
                    <p class="promo-admin-list__meta">Q: <?php echo get_text($cfg['question']); ?></p>
                    <?php } elseif ($p['trigger_type'] === 'post_count' && !empty($cfg['min_posts'])) { ?>
                    <p class="promo-admin-list__meta">글 <?php echo (int) $cfg['min_posts']; ?>개 작성</p>
                    <?php } elseif ($p['trigger_type'] === 'post_views' && !empty($cfg['min_views'])) { ?>
                    <p class="promo-admin-list__meta">조회수 <?php echo number_format((int) $cfg['min_views']); ?>회</p>
                    <?php } elseif ($p['trigger_type'] === 'attendance_streak' && !empty($cfg['days'])) { ?>
                    <p class="promo-admin-list__meta">연속 <?php echo (int) $cfg['days']; ?>일 출석</p>
                    <?php } ?>
                    <p class="promo-admin-list__stats">발급 <?php echo number_format((int) $p['awarded_count']); ?><?php if ((int) $p['promo_max_total'] > 0) { ?> / <?php echo number_format((int) $p['promo_max_total']); ?><?php } ?></p>
                </div>
                <div class="promo-admin-list__actions">
                    <?php if ($p['promo_status'] === 'active') { ?>
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-promo-status="<?php echo (int) $p['promo_id']; ?>" data-status="paused">일시중지</button>
                    <?php } else { ?>
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-promo-status="<?php echo (int) $p['promo_id']; ?>" data-status="active">활성화</button>
                    <?php } ?>
                    <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-promo-status="<?php echo (int) $p['promo_id']; ?>" data-status="ended">종료</button>
                </div>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>
</main>

<script>
(function () {
  var triggerSelect = document.getElementById('trigger_type');
  function syncTriggerFields() {
    if (!triggerSelect) return;
    var val = triggerSelect.value;
    document.querySelectorAll('[data-trigger-fields]').forEach(function (el) {
      var fields = el.getAttribute('data-trigger-fields').split(/\s+/);
      el.hidden = fields.indexOf(val) === -1;
    });
  }
  if (triggerSelect) {
    triggerSelect.addEventListener('change', syncTriggerFields);
    syncTriggerFields();
  }

  function bindForm(formId, action, statusSel) {
    var form = document.getElementById(formId);
    if (!form) return;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(form);
      fd.append('action', action);
      var statusEl = document.querySelector(statusSel);
      var btn = form.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      fetch('/proc/eottae-promo-coupon.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
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

  bindForm('promoCreateForm', 'create', '[data-promo-create-status]');
  bindForm('promoGrantForm', 'grant', '[data-promo-grant-status]');
  bindForm('promoBestCommentForm', 'best_comment', '[data-promo-bc-status]');

  document.querySelectorAll('[data-promo-status]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var fd = new FormData();
      fd.append('action', 'status');
      fd.append('promo_id', btn.getAttribute('data-promo-status'));
      fd.append('status', btn.getAttribute('data-status'));
      btn.disabled = true;
      fetch('/proc/eottae-promo-coupon.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) location.reload();
          else alert(data.message || '오류');
          btn.disabled = false;
        });
    });
  });
})();
</script>

<?php
g5_page_end();
