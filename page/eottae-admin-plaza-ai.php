<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
include_once G5_PATH.'/components/eottae/plaza-admin-nav.php';

$settings = eottae_plaza_ai_get_settings();
$logs = eottae_plaza_ai_admin_list_logs(30);
$admin_token = eottae_plaza_admin_token();

g5_page_start('세부광장 AI 설정');
?>

<main class="promo-admin-page talk-admin-page plaza-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_plaza_list_url(); ?>" class="promo-admin-page__back">← 세부광장</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">세부광장 AI 설정</h1>
        <p class="promo-admin-page__desc">어때봇의 오늘의 질문 자동 작성을 관리합니다. (1차: 템플릿 기반)</p>
        <?php eottae_plaza_render_admin_nav('ai'); ?>
    </header>

    <section class="promo-admin-panel talk-admin-panel">
        <form id="plazaAiSettingsForm" class="talk-admin-form">
            <input type="hidden" name="action" value="save_ai_settings">
            <label class="talk-admin-form__check">
                <input type="checkbox" name="daily_question_enabled" value="1"<?php echo !empty($settings['daily_question_enabled']) ? ' checked' : ''; ?>>
                오늘의 질문 자동 작성 사용
            </label>
            <div class="talk-admin-form__field">
                <label for="plaza_ai_name">AI 이름</label>
                <input type="text" id="plaza_ai_name" name="ai_name" value="<?php echo get_text($settings['ai_name']); ?>" maxlength="50">
            </div>
            <div class="talk-admin-form__actions">
                <button type="submit" class="promo-admin-btn promo-admin-btn--primary">설정 저장</button>
                <button type="button" class="promo-admin-btn" id="plazaAiTestBtn">오늘의 질문 테스트 작성</button>
            </div>
        </form>

        <div class="plaza-admin-cron-help">
            <h2 class="plaza-admin-cron-help__title">크론 실행</h2>
            <pre class="plaza-admin-cron-help__code">php cron/sebu_plaza_ai_daily_question.php</pre>
            <p class="plaza-admin-cron-help__desc">미리보기: <code>php cron/sebu_plaza_ai_daily_question.php --dry-run</code></p>
        </div>
    </section>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">최근 AI 로그</h2>
        <?php if (empty($logs)) { ?>
        <p class="promo-admin-empty">표시할 로그가 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table plaza-admin-table">
                <thead>
                    <tr>
                        <th scope="col">일시</th>
                        <th scope="col">트리거</th>
                        <th scope="col">상태</th>
                        <th scope="col">글 ID</th>
                        <th scope="col">메시지</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) { ?>
                    <tr>
                        <td data-label="일시"><?php echo $log['created_at'] !== '' ? substr($log['created_at'], 0, 16) : '-'; ?></td>
                        <td data-label="트리거"><?php echo $log['trigger_type']; ?></td>
                        <td data-label="상태"><?php echo $log['status']; ?></td>
                        <td data-label="글 ID"><?php echo $log['post_id'] > 0 ? (int) $log['post_id'] : '-'; ?></td>
                        <td data-label="메시지"><?php echo $log['message']; ?><?php if ($log['error_message'] !== '') { ?><div class="talk-report-list__meta"><?php echo $log['error_message']; ?></div><?php } ?></td>
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

  function postPlazaAdmin(formData) {
    formData.append('eottae_plaza_admin_token', adminToken);
    return fetch('/proc/eottae-plaza-admin.php', { method: 'POST', body: formData, credentials: 'same-origin' })
      .then(function (r) { return r.json(); });
  }

  var settingsForm = document.getElementById('plazaAiSettingsForm');
  if (settingsForm) {
    settingsForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(settingsForm);
      postPlazaAdmin(fd).then(function (data) {
        alert(data.message || (data.success ? '저장했습니다.' : '저장에 실패했습니다.'));
        if (data.success) location.reload();
      });
    });
  }

  var testBtn = document.getElementById('plazaAiTestBtn');
  if (testBtn) {
    testBtn.addEventListener('click', function () {
      if (!confirm('테스트용 오늘의 질문을 지금 작성할까요?')) return;
      testBtn.disabled = true;
      var fd = new FormData();
      fd.append('action', 'test_daily_question');
      postPlazaAdmin(fd).then(function (data) {
        alert(data.message || (data.success ? '작성했습니다.' : '실패했습니다.'));
        testBtn.disabled = false;
        if (data.success) location.reload();
      });
    });
  }
})();
</script>

<?php g5_page_end(); ?>
