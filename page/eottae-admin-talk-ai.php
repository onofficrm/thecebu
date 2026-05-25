<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-admin.lib.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';

eottae_talkroom_ai_ensure_schema();

$rooms = eottae_talkroom_ai_admin_list_rooms(200);
$global = eottae_talkroom_ai_get_global_policy();
$site_today_count = eottae_talkroom_ai_get_today_site_message_count();
$admin_token = eottae_talkroom_admin_token();
$saved = !empty($_GET['saved']);

g5_page_start('AI 도우미 설정 관리');
?>

<main class="promo-admin-page talk-admin-page talk-admin-ai-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_talkroom_list_url(); ?>" class="promo-admin-page__back">← 세부톡방</a>
            <a href="<?php echo eottae_talkroom_ai_logs_url(); ?>" class="promo-admin-page__back">AI 발언 로그</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">AI 도우미 설정</h1>
        <p class="promo-admin-page__desc">사이트 전체 AI 정책, 톡방별 강제 OFF, 발언 한도를 관리합니다. 오늘 사이트 전체 AI 발언: <?php echo number_format($site_today_count); ?> / <?php echo number_format($global['site_daily_limit']); ?>회</p>
        <?php eottae_talkroom_render_admin_nav('ai'); ?>
    </header>

    <?php if ($saved) { ?>
    <p class="talk-ai-settings__saved" role="status">전역 AI 정책이 저장되었습니다.</p>
    <?php } ?>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">전역 AI 정책</h2>
        <form class="talk-apply-form talk-ai-settings-form" id="talkAiGlobalPolicyForm" method="post" action="<?php echo G5_URL; ?>/proc/eottae-talkroom-ai-admin.php">
            <input type="hidden" name="action" value="save_global_policy">
            <input type="hidden" name="eottae_talkroom_admin_token" value="<?php echo get_text($admin_token); ?>">

            <fieldset class="talk-apply-form__fieldset">
                <legend>세부톡방 AI 전체 사용</legend>
                <label class="talk-apply-form__radio">
                    <input type="radio" name="site_ai_enabled" value="1"<?php echo !empty($global['site_ai_enabled']) ? ' checked' : ''; ?>>
                    <span>사용함</span>
                </label>
                <label class="talk-apply-form__radio">
                    <input type="radio" name="site_ai_enabled" value="0"<?php echo empty($global['site_ai_enabled']) ? ' checked' : ''; ?>>
                    <span>사용 안 함 (모든 방 AI 자동 발언 중지)</span>
                </label>
            </fieldset>

            <fieldset class="talk-apply-form__fieldset">
                <legend>방장 AI 설정 허용</legend>
                <label class="talk-apply-form__radio">
                    <input type="radio" name="owner_ai_allowed" value="1"<?php echo !empty($global['owner_config_allowed']) ? ' checked' : ''; ?>>
                    <span>허용 (방장이 톡방별 AI 설정 가능)</span>
                </label>
                <label class="talk-apply-form__radio">
                    <input type="radio" name="owner_ai_allowed" value="0"<?php echo empty($global['owner_config_allowed']) ? ' checked' : ''; ?>>
                    <span>중지 (방장 변경 불가, 최고관리자만 설정 가능)</span>
                </label>
            </fieldset>

            <div class="talk-apply-form__field">
                <label for="site_daily_limit">사이트 전체 하루 AI 발언 최대 수</label>
                <input type="number" id="site_daily_limit" name="site_daily_limit" class="talk-apply-form__input" min="1" max="10000" value="<?php echo (int) $global['site_daily_limit']; ?>">
                <p class="talk-ai-settings__hint">모든 톡방 AI 발언 합계가 이 값을 넘으면 당일 추가 발언이 중지됩니다.</p>
            </div>

            <div class="talk-apply-form__actions">
                <button type="submit" class="promo-admin-btn promo-admin-btn--primary">전역 정책 저장</button>
            </div>
        </form>
    </section>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">톡방별 AI 설정</h2>
        <?php if (empty($rooms)) { ?>
        <p class="promo-admin-empty">운영 중인 톡방이 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table">
                <thead>
                    <tr>
                        <th scope="col">톡방</th>
                        <th scope="col">방장</th>
                        <th scope="col">방 설정</th>
                        <th scope="col">실제 동작</th>
                        <th scope="col">강제 OFF</th>
                        <th scope="col">AI 이름</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $item) { ?>
                    <tr>
                        <td data-label="톡방"><?php echo get_text($item['emoji']); ?> <?php echo get_text($item['room_name']); ?></td>
                        <td data-label="방장"><?php echo get_text($item['owner_nick']); ?></td>
                        <td data-label="방 설정"><?php echo !empty($item['ai_enabled']) ? '사용함' : '사용 안 함'; ?></td>
                        <td data-label="실제 동작"><?php echo !empty($item['ai_effective']) ? '동작 가능' : '중지'; ?></td>
                        <td data-label="강제 OFF">
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm<?php echo !empty($item['admin_force_disabled']) ? ' is-active' : ''; ?>" data-talk-ai-force="<?php echo (int) $item['room_id']; ?>" data-force-state="<?php echo !empty($item['admin_force_disabled']) ? '1' : '0'; ?>">
                                <?php echo !empty($item['admin_force_disabled']) ? '강제 OFF' : '허용'; ?>
                            </button>
                        </td>
                        <td data-label="AI 이름"><?php echo $item['ai_name'] !== '' ? get_text($item['ai_name']) : '-'; ?></td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <a href="<?php echo $item['settings_url']; ?>" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary">AI 설정</a>
                            <a href="<?php echo $item['manage_url']; ?>" class="promo-admin-btn promo-admin-btn--sm">톡방 관리</a>
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
  var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;

  var form = document.getElementById('talkAiGlobalPolicyForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(form);
      fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success && data.redirect_url) {
            window.location.href = data.redirect_url;
            return;
          }
          alert(data.message || '저장에 실패했습니다.');
        })
        .catch(function () { alert('네트워크 오류가 발생했습니다.'); });
    });
  }

  document.querySelectorAll('[data-talk-ai-force]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var roomId = btn.getAttribute('data-talk-ai-force');
      var next = btn.getAttribute('data-force-state') === '1' ? '0' : '1';
      var msg = next === '1'
        ? '이 톡방 AI를 강제 OFF 할까요? 방장 설정과 무관하게 AI가 동작하지 않습니다.'
        : '이 톡방 AI 강제 OFF를 해제할까요?';
      if (!confirm(msg)) return;
      btn.disabled = true;
      var fd = new FormData();
      fd.append('action', 'toggle_room_force_off');
      fd.append('room_id', roomId);
      fd.append('force_disabled', next);
      fd.append('eottae_talkroom_admin_token', adminToken);
      fetch('<?php echo G5_URL; ?>/proc/eottae-talkroom-ai-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          alert(data.message || (data.success ? '저장되었습니다.' : '실패했습니다.'));
          if (data.success) location.reload();
          else btn.disabled = false;
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
