<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-openai.lib.php';
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
            <?php eottae_public_ai_render_admin_page_mypage_back(); ?>
            <a href="<?php echo G5_URL; ?>/" class="promo-admin-page__back">홈</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">공개톡 AI 관리</h1>
        <p class="promo-admin-page__desc">홈 <strong>세부공개단체톡</strong> 분위기 메이커(어때봇) 설정입니다. 기본은 관리자 승인 후 발행이며, 자동 발행은 옵션으로 켤 수 있습니다.</p>
        <?php eottae_public_ai_render_admin_nav('settings'); ?>
    </header>

    <?php eottae_public_ai_render_admin_dashboard_stats(); ?>

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

            <fieldset class="talk-apply-form__fieldset public-ai-openai-settings">
                <legend>OpenAI API (6단계)</legend>
                <p class="talk-ai-settings__hint">
                    API 키 우선순위: <strong>환경변수</strong> <code>PUBLIC_AI_OPENAI_API_KEY</code>
                    → <code>_site.config.php</code> <code>public_ai_openai_api_key</code>
                    → DB 저장값. 키는 화면에 노출되지 않습니다.
                </p>
                <?php
                $key_source = (string) ($settings['openai_api_key_source'] ?? '');
                $key_masked = (string) ($settings['openai_api_key_masked'] ?? '');
                $key_source_label = array(
                    'env'         => '환경변수 사용 중',
                    'site_config' => '사이트 설정 파일 사용 중',
                    'database'    => 'DB에 저장된 키 사용 중',
                );
                ?>
                <label class="talk-admin-form__check">
                    <input type="checkbox" name="openai_enabled" value="1"<?php echo !empty($settings['openai_enabled']) ? ' checked' : ''; ?>>
                    OpenAI API 사용 (후보 생성 시에만 호출)
                </label>
                <div class="talk-apply-form__field">
                    <label for="public_ai_openai_model">모델명</label>
                    <input type="text" id="public_ai_openai_model" name="openai_model" class="talk-apply-form__input" maxlength="40" value="<?php echo get_text($settings['openai_model']); ?>" placeholder="gpt-4o-mini">
                </div>
                <div class="talk-apply-form__field">
                    <label for="public_ai_openai_key">API Key (DB 저장, 선택)</label>
                    <input type="password" id="public_ai_openai_key" name="openai_api_key" class="talk-apply-form__input" autocomplete="new-password" placeholder="<?php echo $key_masked !== '' ? htmlspecialchars($key_masked, ENT_QUOTES, 'UTF-8') : '새 키 입력 시에만 저장'; ?>">
                    <?php if ($key_source !== '') { ?>
                    <p class="talk-ai-settings__hint">현재 키 출처: <?php echo htmlspecialchars($key_source_label[$key_source] ?? $key_source, ENT_QUOTES, 'UTF-8'); ?><?php if ($key_masked !== '') { ?> (<?php echo htmlspecialchars($key_masked, ENT_QUOTES, 'UTF-8'); ?>)<?php } ?></p>
                    <?php } ?>
                    <?php if (in_array($key_source, array('env', 'site_config'), true)) { ?>
                    <p class="talk-ai-settings__hint">환경변수·사이트 설정 키가 있으면 DB 입력값은 사용되지 않습니다.</p>
                    <?php } ?>
                </div>
                <div class="talk-apply-form__field">
                    <label for="public_ai_openai_max_calls">하루 최대 API 호출 수</label>
                    <input type="number" id="public_ai_openai_max_calls" name="openai_max_calls_per_day" class="talk-apply-form__input" min="1" max="200" value="<?php echo (int) $settings['openai_max_calls_per_day']; ?>">
                    <p class="talk-ai-settings__hint">관리자 테스트 호출은 한도에 포함하지 않습니다. 같은 source_id는 하루 1회만 API 호출합니다.</p>
                </div>
                <div class="talk-apply-form__field">
                    <label for="public_ai_openai_max_len">메시지 최대 길이</label>
                    <input type="number" id="public_ai_openai_max_len" name="openai_max_message_length" class="talk-apply-form__input" min="80" max="1000" value="<?php echo (int) $settings['openai_max_message_length']; ?>">
                </div>
                <label class="talk-admin-form__check">
                    <input type="checkbox" name="openai_fallback_template" value="1"<?php echo !empty($settings['openai_fallback_template']) ? ' checked' : ''; ?>>
                    API 실패 시 템플릿 메시지로 fallback
                </label>
            </fieldset>

            <div class="talk-apply-form__actions">
                <button type="submit" class="promo-admin-btn promo-admin-btn--primary">설정 저장</button>
                <button type="button" id="publicAiGenerateCandidatesBtn" class="promo-admin-btn">후보 메시지 생성 테스트</button>
            </div>
            <p class="talk-ai-settings__hint">후보 생성 테스트는 OpenAI 사용 시 AI 문구로 저장됩니다(실패 시 템플릿). 공개톡에는 발행되지 않습니다.</p>
        </form>
    </section>

    <section class="promo-admin-panel talk-admin-panel public-ai-openai-test-panel">
        <h2 class="promo-admin-panel__title">OpenAI 테스트 생성</h2>
        <p class="talk-ai-settings__hint">발행·후보 저장 없이 메시지만 미리 봅니다. 호출은 OpenAI 로그에 <code>테스트</code>로 기록됩니다.</p>
        <div class="talk-apply-form__actions public-ai-openai-test-actions">
            <button type="button" class="promo-admin-btn public-ai-openai-test-btn" data-test-type="calendar">캘린더 일정으로 테스트</button>
            <button type="button" class="promo-admin-btn public-ai-openai-test-btn" data-test-type="weather">날씨로 테스트</button>
            <button type="button" class="promo-admin-btn public-ai-openai-test-btn" data-test-type="popular">인기글로 테스트</button>
        </div>
        <div class="talk-apply-form__field">
            <label for="public_ai_openai_custom">직접 입력으로 테스트</label>
            <textarea id="public_ai_openai_custom" class="talk-apply-form__input" rows="3" maxlength="800" placeholder="예: 막탄에서 저녁 뭐 먹을지 고민 중이에요. 추천해 주실 분?"></textarea>
            <button type="button" id="publicAiOpenaiCustomTestBtn" class="promo-admin-btn">직접 입력 테스트</button>
        </div>
        <pre id="publicAiOpenaiTestResult" class="public-ai-openai-test-result" hidden></pre>
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

  function runOpenAiTest(testType, customText) {
    var post = window.eottaePublicAiAdminPost;
    if (!post) return;
    var out = document.getElementById('publicAiOpenaiTestResult');
    if (out) {
      out.hidden = false;
      out.textContent = '생성 중…';
    }
    var fields = { test_type: testType };
    if (customText) fields.custom_text = customText;
    post('test_openai', fields)
      .then(function (data) {
        if (!out) return;
        if (!data.success) {
          out.textContent = (data.message || '실패') + (data.error ? '\n오류: ' + data.error : '');
          return;
        }
        var lines = [
          '출처: ' + (data.source || ''),
          '트리거: ' + (data.trigger_type || ''),
          data.is_sensitive ? '⚠ 민감 키워드 감지 — 자동 발행 불가' : '',
          data.force_admin_approval ? '관리자 승인 필요' : '',
          '',
          (data.title ? '[' + data.title + ']\n' : '') + (data.message || ''),
        ];
        if (data.action_label || data.action_url) {
          lines.push('', '버튼: ' + (data.action_label || '') + ' → ' + (data.action_url || ''));
        }
        if (data.template_message && data.source !== 'openai') {
          lines.push('', '--- 템플릿 참고 ---', data.template_message);
        }
        out.textContent = lines.filter(Boolean).join('\n');
      })
      .catch(function () {
        if (out) out.textContent = '요청에 실패했습니다.';
      });
  }

  document.querySelectorAll('.public-ai-openai-test-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      runOpenAiTest(btn.getAttribute('data-test-type') || '', '');
    });
  });

  var customBtn = document.getElementById('publicAiOpenaiCustomTestBtn');
  if (customBtn) {
    customBtn.addEventListener('click', function () {
      var ta = document.getElementById('public_ai_openai_custom');
      var text = ta ? ta.value.trim() : '';
      if (!text) {
        alert('직접 입력 내용을 적어주세요.');
        return;
      }
      runOpenAiTest('custom', text);
    });
  }

  var genBtn = document.getElementById('publicAiGenerateCandidatesBtn');
  if (genBtn) {
    genBtn.addEventListener('click', function () {
      if (!confirm('내부 데이터를 바탕으로 후보 메시지를 생성할까요? (공개톡 발행 없음)')) return;
      var post = window.eottaePublicAiAdminPost || function () { return Promise.reject(); };
      post('generate_candidates', {})
        .then(function (data) {
          alert(data.message || (data.success ? '생성했습니다.' : '생성에 실패했습니다.'));
          if (data.redirect) location.href = data.redirect;
        })
        .catch(function () { alert('요청에 실패했습니다.'); });
    });
  }
}());
</script>

<?php
g5_page_end();
