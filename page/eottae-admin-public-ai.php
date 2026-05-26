<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_PATH.'/components/eottae/public-ai-admin-nav.php';

eottae_public_ai_ensure_schema();

$settings = eottae_public_ai_get_settings();
$admin_token = eottae_public_ai_admin_token();
$saved = !empty($_GET['saved']);

g5_page_start('공개톡 AI 기본 설정');
?>

<main class="promo-admin-page talk-admin-page public-ai-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo G5_URL; ?>/" class="promo-admin-page__back">← 홈</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">공개톡 AI 관리</h1>
        <p class="promo-admin-page__desc">홈 <strong>세부공개단체톡</strong> 분위기 메이커(어때봇) 설정입니다. 이번 단계에서는 자동 발행하지 않습니다.</p>
        <?php eottae_public_ai_render_admin_nav('settings'); ?>
    </header>

    <?php if ($saved) { ?>
    <p class="talk-ai-settings__saved" role="status">설정을 저장했습니다.</p>
    <?php } ?>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">AI 기본 설정</h2>
        <form id="publicAiSettingsForm" class="talk-admin-form talk-apply-form">
            <label class="talk-admin-form__check">
                <input type="checkbox" name="ai_enabled" value="1"<?php echo !empty($settings['ai_enabled']) ? ' checked' : ''; ?>>
                AI 사용 (분위기 메이커 활성화)
            </label>

            <div class="talk-apply-form__field">
                <label for="public_ai_name">AI 이름</label>
                <input type="text" id="public_ai_name" name="ai_name" class="talk-apply-form__input" maxlength="50" value="<?php echo get_text($settings['ai_name']); ?>">
            </div>

            <div class="talk-apply-form__field">
                <label for="public_ai_persona">AI 성격/역할</label>
                <input type="text" id="public_ai_persona" name="ai_persona" class="talk-apply-form__input" maxlength="255" value="<?php echo get_text($settings['ai_persona']); ?>">
            </div>

            <fieldset class="talk-apply-form__fieldset">
                <legend>발행 정책</legend>
                <label class="talk-admin-form__check">
                    <input type="checkbox" name="auto_publish" value="1"<?php echo !empty($settings['auto_publish']) ? ' checked' : ''; ?>>
                    자동 발행 (3단계 이후 연결)
                </label>
                <label class="talk-admin-form__check">
                    <input type="checkbox" name="require_admin_approval" value="1"<?php echo !empty($settings['require_admin_approval']) ? ' checked' : ''; ?>>
                    관리자 승인 후 발행
                </label>
            </fieldset>

            <div class="talk-apply-form__field">
                <label for="public_ai_max_messages">하루 최대 발언 수</label>
                <input type="number" id="public_ai_max_messages" name="max_messages_per_day" class="talk-apply-form__input" min="1" max="50" value="<?php echo (int) $settings['max_messages_per_day']; ?>">
            </div>

            <div class="talk-apply-form__field">
                <label for="public_ai_silence">조용한 방 판단 기준 (분)</label>
                <input type="number" id="public_ai_silence" name="min_silence_minutes" class="talk-apply-form__input" min="5" max="1440" value="<?php echo (int) $settings['min_silence_minutes']; ?>">
                <p class="talk-ai-settings__hint">이 시간 동안 회원 메시지가 없으면 AI가 화제를 던질 수 있습니다.</p>
            </div>

            <div class="talk-apply-form__field talk-apply-form__field--inline">
                <label for="public_ai_start">AI 활동 시작</label>
                <input type="time" id="public_ai_start" name="active_start_time" class="talk-apply-form__input" value="<?php echo htmlspecialchars(substr($settings['active_start_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="talk-apply-form__field talk-apply-form__field--inline">
                <label for="public_ai_end">AI 활동 종료</label>
                <input type="time" id="public_ai_end" name="active_end_time" class="talk-apply-form__input" value="<?php echo htmlspecialchars(substr($settings['active_end_time'], 0, 5), ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <fieldset class="talk-apply-form__fieldset">
                <legend>참고 데이터 ON/OFF</legend>
                <label class="talk-admin-form__check"><input type="checkbox" name="use_calendar" value="1"<?php echo !empty($settings['use_calendar']) ? ' checked' : ''; ?>> 캘린더 일정</label>
                <label class="talk-admin-form__check"><input type="checkbox" name="use_weather" value="1"<?php echo !empty($settings['use_weather']) ? ' checked' : ''; ?>> 날씨</label>
                <label class="talk-admin-form__check"><input type="checkbox" name="use_holidays" value="1"<?php echo !empty($settings['use_holidays']) ? ' checked' : ''; ?>> 공휴일</label>
                <label class="talk-admin-form__check"><input type="checkbox" name="use_talk_rooms" value="1"<?php echo !empty($settings['use_talk_rooms']) ? ' checked' : ''; ?>> 세부톡방 활동</label>
                <label class="talk-admin-form__check"><input type="checkbox" name="use_popular_posts" value="1"<?php echo !empty($settings['use_popular_posts']) ? ' checked' : ''; ?>> 인기글</label>
                <label class="talk-admin-form__check"><input type="checkbox" name="use_business_events" value="1"<?php echo !empty($settings['use_business_events']) ? ' checked' : ''; ?>> 업체 이벤트/기획전</label>
                <label class="talk-admin-form__check"><input type="checkbox" name="use_external_news" value="1"<?php echo !empty($settings['use_external_news']) ? ' checked' : ''; ?>> 외부뉴스 (승인 후)</label>
            </fieldset>

            <div class="talk-apply-form__actions">
                <button type="submit" class="promo-admin-btn promo-admin-btn--primary">설정 저장</button>
            </div>
        </form>
    </section>
</main>

<?php eottae_public_ai_render_admin_actions_script($admin_token); ?>
<script>
(function () {
  var form = document.getElementById('publicAiSettingsForm');
  var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;
  if (!form) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(form);
    fd.append('action', 'save_settings');
    fd.append('eottae_public_ai_admin_token', adminToken);
    fetch('/proc/eottae-public-ai-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        alert(data.message || (data.success ? '저장했습니다.' : '저장에 실패했습니다.'));
        if (data.success) location.href = location.pathname + '?saved=1';
      });
  });
}());
</script>

<?php
g5_page_end();
