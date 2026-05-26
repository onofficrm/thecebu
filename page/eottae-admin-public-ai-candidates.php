<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_PATH.'/components/eottae/public-ai-admin-nav.php';

eottae_public_ai_ensure_schema();

$status = isset($_GET['status']) ? trim((string) $_GET['status']) : 'pending';
$edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$candidates = eottae_public_ai_admin_list_candidates($status === 'all' ? 'all' : $status, 100);
$edit_candidate = $edit_id > 0 ? eottae_public_ai_get_candidate($edit_id) : null;
$trigger_types = eottae_public_ai_trigger_types();
$source_types = eottae_public_ai_source_types();
$statuses = eottae_public_ai_candidate_statuses();
$admin_token = eottae_public_ai_admin_token();

g5_page_start('공개톡 AI 후보 메시지');
?>

<main class="promo-admin-page talk-admin-page public-ai-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="promo-admin-page__back">← AI 기본 설정</a>
            <a href="<?php echo G5_URL; ?>/" class="promo-admin-page__back">홈</a>
        </div>
        <h1 class="promo-admin-page__title">AI 후보 메시지</h1>
        <p class="promo-admin-page__desc">승인·수정·삭제 후 다음 단계에서 공개톡에 발행합니다. (현재 자동 발행 미연결)</p>
        <?php eottae_public_ai_render_admin_nav('candidates'); ?>
    </header>

    <nav class="talk-admin-filter" aria-label="상태 필터">
        <a href="<?php echo eottae_public_ai_admin_candidates_url('pending'); ?>" class="talk-admin-filter__item<?php echo $status === 'pending' ? ' is-active' : ''; ?>">승인 대기</a>
        <a href="<?php echo eottae_public_ai_admin_candidates_url('approved'); ?>" class="talk-admin-filter__item<?php echo $status === 'approved' ? ' is-active' : ''; ?>">승인됨</a>
        <a href="<?php echo eottae_public_ai_admin_candidates_url('published'); ?>" class="talk-admin-filter__item<?php echo $status === 'published' ? ' is-active' : ''; ?>">발행됨</a>
        <a href="<?php echo eottae_public_ai_admin_candidates_url('rejected'); ?>" class="talk-admin-filter__item<?php echo $status === 'rejected' ? ' is-active' : ''; ?>">반려</a>
        <a href="<?php echo eottae_public_ai_admin_candidates_url('all'); ?>" class="talk-admin-filter__item<?php echo $status === 'all' ? ' is-active' : ''; ?>">전체</a>
    </nav>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title"><?php echo $edit_candidate ? '후보 메시지 수정' : '수동 후보 등록'; ?></h2>
        <form id="publicAiCandidateForm" class="talk-apply-form">
            <input type="hidden" name="candidate_id" value="<?php echo $edit_candidate ? (int) $edit_candidate['candidate_id'] : 0; ?>">

            <div class="talk-apply-form__field">
                <label for="candidate_trigger">트리거 타입</label>
                <select id="candidate_trigger" name="trigger_type" class="talk-apply-form__select">
                    <?php foreach ($trigger_types as $key => $label) { ?>
                    <option value="<?php echo $key; ?>"<?php echo ($edit_candidate && $edit_candidate['trigger_type'] === $key) || (!$edit_candidate && $key === 'admin_manual') ? ' selected' : ''; ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_source">참고 데이터</label>
                <select id="candidate_source" name="source_type" class="talk-apply-form__select">
                    <?php foreach ($source_types as $key => $label) { ?>
                    <option value="<?php echo $key; ?>"<?php echo ($edit_candidate && $edit_candidate['source_type'] === $key) || (!$edit_candidate && $key === 'manual') ? ' selected' : ''; ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_source_id">참고 ID (선택)</label>
                <input type="number" id="candidate_source_id" name="source_id" class="talk-apply-form__input" min="0" value="<?php echo $edit_candidate ? (int) $edit_candidate['source_id'] : 0; ?>">
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_title">제목 (선택)</label>
                <input type="text" id="candidate_title" name="title" class="talk-apply-form__input" maxlength="120" value="<?php echo $edit_candidate ? $edit_candidate['title'] : ''; ?>">
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_message">메시지 내용</label>
                <textarea id="candidate_message" name="message" class="talk-apply-form__textarea" rows="4" required><?php echo $edit_candidate ? htmlspecialchars($edit_candidate['message'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_action_label">액션 라벨 (선택)</label>
                <input type="text" id="candidate_action_label" name="action_label" class="talk-apply-form__input" maxlength="80" value="<?php echo $edit_candidate ? $edit_candidate['action_label'] : ''; ?>">
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_action_url">액션 URL (선택)</label>
                <input type="url" id="candidate_action_url" name="action_url" class="talk-apply-form__input" maxlength="255" value="<?php echo $edit_candidate ? $edit_candidate['action_url'] : ''; ?>">
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_memo">관리자 메모</label>
                <input type="text" id="candidate_memo" name="admin_memo" class="talk-apply-form__input" maxlength="500" value="<?php echo $edit_candidate ? $edit_candidate['admin_memo'] : ''; ?>">
            </div>

            <div class="talk-apply-form__actions">
                <button type="submit" class="promo-admin-btn promo-admin-btn--primary"><?php echo $edit_candidate ? '수정 저장' : '후보 등록'; ?></button>
                <?php if ($edit_candidate) { ?>
                <a href="<?php echo eottae_public_ai_admin_candidates_url($status); ?>" class="promo-admin-btn">취소</a>
                <?php } ?>
            </div>
        </form>
    </section>

    <section class="promo-admin-panel talk-admin-panel">
        <h2 class="promo-admin-panel__title">후보 목록</h2>
        <?php if (empty($candidates)) { ?>
        <p class="promo-admin-empty">표시할 후보 메시지가 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table public-ai-candidates-table">
                <thead>
                    <tr>
                        <th scope="col">생성일</th>
                        <th scope="col">트리거</th>
                        <th scope="col">참고</th>
                        <th scope="col">제목</th>
                        <th scope="col">내용</th>
                        <th scope="col">상태</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidates as $item) { ?>
                    <tr>
                        <td data-label="생성일"><?php echo $item['created_at'] !== '' ? substr($item['created_at'], 0, 16) : '-'; ?></td>
                        <td data-label="트리거"><?php echo $item['trigger_label']; ?></td>
                        <td data-label="참고"><?php echo $item['source_label']; ?><?php if ($item['source_id'] > 0) { ?><br><span class="talk-admin-table__sub">#<?php echo (int) $item['source_id']; ?></span><?php } ?></td>
                        <td data-label="제목"><?php echo $item['title'] !== '' ? $item['title'] : '-'; ?></td>
                        <td data-label="내용"><?php echo $item['message_preview']; ?></td>
                        <td data-label="상태"><span class="talk-apply-status talk-apply-status--<?php echo htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $item['status_label']; ?></span></td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <?php if (!in_array($item['status'], array('published', 'deleted'), true)) { ?>
                            <a href="<?php echo eottae_public_ai_admin_candidates_url($status); ?>&amp;edit=<?php echo (int) $item['candidate_id']; ?>" class="promo-admin-btn promo-admin-btn--sm">수정</a>
                            <?php if ($item['status'] === 'pending') { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-public-ai-approve="<?php echo (int) $item['candidate_id']; ?>">승인</button>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-public-ai-reject="<?php echo (int) $item['candidate_id']; ?>">반려</button>
                            <?php } ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-public-ai-delete="<?php echo (int) $item['candidate_id']; ?>">삭제</button>
                            <?php } ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-public-ai-test="<?php echo (int) $item['candidate_id']; ?>">테스트 발행</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<?php eottae_public_ai_render_admin_actions_script($admin_token); ?>
<script>
(function () {
  if (typeof window.eottaePublicAiAdminPost !== 'function') return;

  function bindAction(attr, action, confirmMsg) {
    document.querySelectorAll('[data-' + attr + ']').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-' + attr);
        if (confirmMsg && !confirm(confirmMsg)) return;
        btn.disabled = true;
        window.eottaePublicAiAdminPost(action, { candidate_id: id }).then(function (data) {
          alert(data.message || (data.success ? '처리했습니다.' : '실패했습니다.'));
          if (data.success) location.reload();
          else btn.disabled = false;
        });
      });
    });
  }

  bindAction('public-ai-approve', 'approve_candidate', '이 후보를 승인할까요?');
  bindAction('public-ai-reject', 'reject_candidate', '이 후보를 반려할까요?');
  bindAction('public-ai-delete', 'delete_candidate', '이 후보를 삭제할까요?');
  bindAction('public-ai-test', 'test_publish_candidate', '테스트 발행 로그를 기록할까요? (공개톡 발행은 다음 단계)');

  var form = document.getElementById('publicAiCandidateForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd = new FormData(form);
      var fields = {};
      fd.forEach(function (value, key) { fields[key] = value; });
      window.eottaePublicAiAdminPost('save_candidate', fields).then(function (data) {
        alert(data.message || (data.success ? '저장했습니다.' : '저장에 실패했습니다.'));
        if (data.success) {
          location.href = '<?php echo eottae_public_ai_admin_candidates_url($status); ?>';
        }
      });
    });
  }
}());
</script>

<?php
g5_page_end();
