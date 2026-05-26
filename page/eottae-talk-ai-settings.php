<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-admin.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-ai-guard.lib.php';
include_once G5_PATH.'/components/eottae/talk-ai-settings-form.php';

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-talk-ai-settings.php'));
}

eottae_talkroom_ai_ensure_schema();
eottae_talkroom_ensure_board();

$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
$is_super = ($is_admin === 'super');
$saved = !empty($_GET['saved']);

if ($room_id < 1) {
    alert('톡방 정보가 올바르지 않습니다.', eottae_talkroom_list_url());
}

if (!eottae_talkroom_ai_can_view_settings($room_id, $member['mb_id'], $is_super)) {
    alert('AI 도우미 설정 권한이 없습니다.', eottae_talkroom_list_url());
}

$room = eottae_talkroom_get_operating_room($room_id);
if (!$room) {
    alert('운영 중인 톡방을 찾을 수 없습니다.', eottae_talkroom_list_url());
}

$stats = eottae_talkroom_room_stats($room_id);
$detail = eottae_talkroom_format_detail($room, $stats);
$settings = eottae_talkroom_ai_get_settings($room_id);
$policy_notices = array();
if (!eottae_talkroom_ai_is_site_ai_enabled()) {
    $policy_notices[] = '사이트 전체 AI 사용이 중지되어 있습니다. AI 자동 발언은 동작하지 않습니다.';
}
if (eottae_talkroom_ai_is_room_force_disabled($room_id)) {
    $policy_notices[] = '최고관리자가 이 톡방 AI를 강제 OFF 했습니다.';
}
if (!$is_super && !eottae_talkroom_ai_is_owner_config_allowed()) {
    $policy_notices[] = '최고관리자가 방장 AI 설정 변경을 중지했습니다.';
}
$readonly = !$is_super && (
    !eottae_talkroom_ai_is_owner_config_allowed()
    || !eottae_talkroom_ai_is_site_ai_enabled()
    || eottae_talkroom_ai_is_room_force_disabled($room_id)
);
$owner_token = eottae_talkroom_owner_token();
$admin_token = $is_super ? eottae_talkroom_admin_token() : '';
$form_token = $is_super && $admin_token !== '' ? $admin_token : $owner_token;
$form_token_field = $is_super && $admin_token !== '' ? 'eottae_talkroom_admin_token' : 'eottae_talkroom_owner_token';
$can_test = eottae_talkroom_ai_can_edit_settings($room_id, $member['mb_id'], $is_super);
$ai_limit_status = eottae_talkroom_ai_daily_limit_status($room_id, $settings);
$room_today_count = (int) ($ai_limit_status['today_count'] ?? 0);
$room_effective_limit = (int) ($ai_limit_status['effective_limit'] ?? eottae_talkroom_ai_min_messages_per_day());
$room_max_cap = (int) ($ai_limit_status['max_cap'] ?? (int) $settings['max_messages_per_day']);
$room_member_activity = (int) ($ai_limit_status['member_activity'] ?? 0);
$test_triggers = array(
    'quiet_room'     => '조용한 방 화제',
    'daily_question' => '오늘의 질문',
    'meetup_suggest' => '모임 제안',
    'summary'        => '방 요약',
);

g5_page_start('AI 도우미 설정');
?>

<main class="mypage-subpage talk-manage-page talk-ai-settings-page">
    <p class="mypage-subpage__back">
        <a href="<?php echo eottae_talkroom_enter_url($room_id); ?>">← 톡방으로</a>
        · <a href="<?php echo eottae_talkroom_owner_manage_url($room_id); ?>">톡방 관리</a>
        <?php if ($is_super) { ?>
        · <a href="<?php echo eottae_talkroom_ai_admin_url(); ?>">AI 관리 목록</a>
        · <a href="<?php echo eottae_talkroom_ai_logs_url(array('room_id' => $room_id)); ?>">AI 로그</a>
        <?php } ?>
    </p>
    <h1 class="mypage-subpage__title">AI 도우미 설정</h1>
    <p class="talk-manage-page__intro"><?php echo eottae_talkroom_display_emoji($detail['emoji'], $detail['category_code'] ?? ''); ?> <?php echo get_text($detail['room_name']); ?> · 오늘 AI 발언 <?php echo number_format($room_today_count); ?> / <?php echo number_format($room_effective_limit); ?>회<?php if ($room_max_cap > $room_effective_limit) { ?> <span class="talk-ai-settings__limit-note">(최대 <?php echo number_format($room_max_cap); ?>회 · 오늘 대화 <?php echo number_format($room_member_activity); ?>건)</span><?php } elseif ($room_member_activity > 0) { ?> <span class="talk-ai-settings__limit-note">(오늘 대화 <?php echo number_format($room_member_activity); ?>건)</span><?php } ?></p>

    <section class="promo-admin-panel talk-manage-panel">
        <?php
        eottae_talkroom_ai_render_settings_form($settings, array(
            'room_id'        => $room_id,
            'readonly'       => $readonly,
            'token'          => $form_token,
            'token_field'    => $form_token_field,
            'saved'          => $saved,
            'policy_notices' => $policy_notices,
        ));
        ?>
    </section>

    <?php if ($can_test) { ?>
    <section class="promo-admin-panel talk-manage-panel talk-ai-settings-test">
        <h2 class="promo-admin-panel__title">기능 테스트</h2>
        <p class="talk-ai-settings__hint">미리보기는 실제 글을 등록하지 않습니다. 테스트 작성은 <code>admin_test</code> 로그로 기록되며, 하루 AI 발언 한도에 포함됩니다. 작성 후 아래에서 바로 삭제할 수 있습니다.</p>
        <div class="talk-ai-settings-test__grid">
            <?php foreach ($test_triggers as $trigger_key => $trigger_label) { ?>
            <div class="talk-ai-settings-test__item">
                <h3 class="talk-ai-settings-test__label"><?php echo get_text($trigger_label); ?></h3>
                <div class="talk-apply-form__actions">
                    <button type="button" class="talk-page__btn talk-ai-test-preview" data-trigger="<?php echo get_text($trigger_key); ?>">미리보기</button>
                    <button type="button" class="talk-page__btn talk-page__btn--primary talk-ai-test-write" data-trigger="<?php echo get_text($trigger_key); ?>">테스트 작성</button>
                </div>
            </div>
            <?php } ?>
        </div>
        <div id="talkAiTestPreview" class="talk-ai-settings-test__preview" hidden>
            <h3 class="talk-ai-settings-test__preview-title">미리보기</h3>
            <pre id="talkAiTestPreviewBody"></pre>
        </div>
        <?php if ($is_super) { ?>
        <div id="talkAiTestDeleteWrap" class="talk-apply-form__actions" hidden>
            <button type="button" class="talk-page__btn" id="talkAiTestDeleteBtn">방금 작성한 테스트 글 삭제</button>
        </div>
        <?php } ?>
    </section>
    <?php } ?>
</main>

<script>
(function () {
  var roomId = <?php echo (int) $room_id; ?>;
  var tokenField = <?php echo json_encode((string) $form_token_field, JSON_UNESCAPED_UNICODE); ?>;
  var tokenValue = <?php echo json_encode((string) $form_token, JSON_UNESCAPED_UNICODE); ?>;
  var lastTestPostId = 0;

  function parseJsonResponse(response) {
    return response.text().then(function (text) {
      var trimmed = (text || '').trim();
      if (!trimmed) {
        throw new Error('서버 응답이 비어 있습니다.');
      }
      try {
        return JSON.parse(trimmed);
      } catch (e) {
        throw new Error('서버 응답 형식이 올바르지 않습니다.');
      }
    });
  }

  function runTest(trigger, dryRun, btn) {
    var fd = new FormData();
    fd.append('action', 'test_ai_trigger');
    fd.append('room_id', String(roomId));
    fd.append('trigger', trigger);
    if (dryRun) fd.append('dry_run', '1');
    fd.append(tokenField, tokenValue);

  return fetch('<?php echo G5_URL; ?>/proc/eottae-talkroom-ai-settings.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(parseJsonResponse)
      .then(function (data) {
        if (btn) btn.disabled = false;
        return data;
      })
      .catch(function (err) {
        if (btn) btn.disabled = false;
        alert(err && err.message ? err.message : '네트워크 오류가 발생했습니다.');
        return null;
      });
  }

  document.querySelectorAll('.talk-ai-test-preview').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.disabled = true;
      runTest(btn.getAttribute('data-trigger'), true, btn).then(function (data) {
        if (!data) return;
        if (!data.success) {
          alert(data.message || '미리보기에 실패했습니다.');
          return;
        }
        var result = data.result || {};
        var lines = [];
        if (result.subject) lines.push('[제목]\n' + result.subject);
        if (result.content) lines.push('[내용]\n' + result.content);
        if (!lines.length) lines.push(data.message || '미리보기 결과 없음');
        document.getElementById('talkAiTestPreviewBody').textContent = lines.join('\n\n');
        document.getElementById('talkAiTestPreview').hidden = false;
      });
    });
  });

  document.querySelectorAll('.talk-ai-test-write').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var trigger = btn.getAttribute('data-trigger');
      if (!confirm('테스트 글을 실제로 작성할까요?')) return;
      btn.disabled = true;
      runTest(trigger, false, btn).then(function (data) {
        if (!data) return;
        alert(data.message || (data.success ? '작성되었습니다.' : '실패했습니다.'));
        if (data.success && data.post_id) {
          lastTestPostId = data.post_id;
          document.getElementById('talkAiTestDeleteWrap').hidden = false;
        }
      });
    });
  });

  var deleteBtn = document.getElementById('talkAiTestDeleteBtn');
  if (deleteBtn) {
    deleteBtn.addEventListener('click', function () {
      if (lastTestPostId < 1) return;
      if (!confirm('테스트 글 #' + lastTestPostId + '을(를) 삭제할까요?')) return;
      deleteBtn.disabled = true;
      var fd = new FormData();
      fd.append('action', 'delete_ai_content');
      fd.append('wr_id', String(lastTestPostId));
      fd.append('eottae_talkroom_admin_token', tokenValue);
      fetch('<?php echo G5_URL; ?>/proc/eottae-talkroom-ai-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(parseJsonResponse)
        .then(function (data) {
          alert(data.message || (data.success ? '삭제했습니다.' : '실패했습니다.'));
          if (data.success) {
            lastTestPostId = 0;
            document.getElementById('talkAiTestDeleteWrap').hidden = true;
          }
          deleteBtn.disabled = false;
        })
        .catch(function (err) {
          alert(err && err.message ? err.message : '네트워크 오류가 발생했습니다.');
          deleteBtn.disabled = false;
        });
    });
  }
})();
</script>

<script>
(function () {
  var form = document.getElementById('talkAiSettingsForm');
  if (!form || form.getAttribute('data-readonly') === '1') return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var fd = new FormData(form);
    var aiEnabled = form.querySelector('input[name="ai_enabled"]:checked');
    if (aiEnabled) {
      fd.set('ai_enabled', aiEnabled.value === '1' ? '1' : '0');
    }
    fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(parseJsonResponse)
      .then(function (data) {
        if (data.success && data.redirect_url) {
          window.location.href = data.redirect_url;
          return;
        }
        alert(data.message || (data.success ? '저장되었습니다.' : '저장에 실패했습니다.'));
      })
      .catch(function (err) {
        alert(err && err.message ? err.message : '네트워크 오류가 발생했습니다.');
      });
  });
})();
</script>

<?php
g5_page_end();
