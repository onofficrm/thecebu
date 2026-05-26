<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
include_once G5_LIB_PATH.'/eottae-public-ai-publish.lib.php';
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
$settings = eottae_public_ai_get_settings();

g5_page_start('공개톡 AI 후보 메시지');
?>

<main class="promo-admin-page talk-admin-page public-ai-admin-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_public_ai_admin_settings_url(); ?>" class="promo-admin-page__back">← AI 기본 설정</a>
            <a href="<?php echo G5_URL; ?>/" class="promo-admin-page__back">홈</a>
        </div>
        <h1 class="promo-admin-page__title">AI 후보 메시지</h1>
        <p class="promo-admin-page__desc">승인·수정 후 <strong>바로 발행</strong>하면 홈 세부공개단체톡에 어때봇 메시지가 등록됩니다.<?php if (empty($settings['ai_enabled'])) { ?> <span class="talk-admin-table__sub">(현재 AI 비활성 — 설정에서 켜야 발행됩니다)</span><?php } ?></p>
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
        <h2 class="promo-admin-panel__title"><?php echo $edit_candidate ? '후보 메시지 수정 #'.(int) $edit_candidate['candidate_id'] : '수동 후보 등록'; ?></h2>
        <form id="publicAiCandidateForm" class="talk-apply-form">
            <input type="hidden" name="candidate_id" value="<?php echo $edit_candidate ? (int) $edit_candidate['candidate_id'] : 0; ?>">

            <div class="talk-apply-form__field">
                <label for="candidate_trigger">트리거 타입</label>
                <select id="candidate_trigger" name="trigger_type" class="talk-apply-form__select"<?php echo $edit_candidate ? ' disabled' : ''; ?>>
                    <?php foreach ($trigger_types as $key => $label) { ?>
                    <option value="<?php echo $key; ?>"<?php echo ($edit_candidate && $edit_candidate['trigger_type'] === $key) || (!$edit_candidate && $key === 'admin_manual') ? ' selected' : ''; ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_source">참고 데이터</label>
                <select id="candidate_source" name="source_type" class="talk-apply-form__select"<?php echo $edit_candidate ? ' disabled' : ''; ?>>
                    <?php foreach ($source_types as $key => $label) { ?>
                    <option value="<?php echo $key; ?>"<?php echo ($edit_candidate && $edit_candidate['source_type'] === $key) || (!$edit_candidate && $key === 'manual') ? ' selected' : ''; ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_title">제목 (선택)</label>
                <input type="text" id="candidate_title" name="title" class="talk-apply-form__input" maxlength="120" value="<?php echo $edit_candidate ? $edit_candidate['title'] : ''; ?>">
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_message">메시지 내용</label>
                <textarea id="candidate_message" name="message" class="talk-apply-form__textarea" rows="5" required><?php echo $edit_candidate ? htmlspecialchars($edit_candidate['message'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            </div>

            <div class="talk-apply-form__field">
                <label for="candidate_action_label">액션 버튼 라벨 (선택)</label>
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
                <a href="<?php echo eottae_public_ai_admin_candidates_url($status); ?>" class="promo-admin-btn">목록으로</a>
                <?php if (!in_array($edit_candidate['status'], array('published', 'deleted'), true)) { ?>
                <button type="button" class="promo-admin-btn promo-admin-btn--primary" data-public-ai-publish="<?php echo (int) $edit_candidate['candidate_id']; ?>">바로 발행</button>
                <?php } ?>
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
                        <th scope="col">참고 데이터</th>
                        <th scope="col">제목</th>
                        <th scope="col">메시지</th>
                        <th scope="col">액션 라벨</th>
                        <th scope="col">액션 URL</th>
                        <th scope="col">상태</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidates as $item) {
                        $can_manage = !in_array($item['status'], array('published', 'deleted'), true);
                        $msg_short = $item['message'];
                        if (function_exists('mb_strlen') && mb_strlen($msg_short, 'UTF-8') > 120) {
                            $msg_short = mb_substr($msg_short, 0, 120, 'UTF-8').'…';
                        }
                        ?>
                    <tr>
                        <td data-label="생성일"><?php echo $item['created_at'] !== '' ? substr($item['created_at'], 0, 16) : '-'; ?></td>
                        <td data-label="트리거"><?php echo $item['trigger_label']; ?></td>
                        <td data-label="참고"><?php echo $item['source_label']; ?><?php if ($item['source_id'] > 0) { ?><br><span class="talk-admin-table__sub">#<?php echo (int) $item['source_id']; ?></span><?php } ?></td>
                        <td data-label="제목"><?php echo $item['title'] !== '' ? $item['title'] : '-'; ?></td>
                        <td data-label="메시지" class="public-ai-candidates-table__message"><?php echo nl2br(get_text($msg_short)); ?></td>
                        <td data-label="액션 라벨"><?php echo $item['action_label'] !== '' ? $item['action_label'] : '-'; ?></td>
                        <td data-label="액션 URL"><?php if ($item['action_url'] !== '') { ?><a href="<?php echo htmlspecialchars($item['action_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="talk-admin-table__link"><?php echo get_text(cut_str($item['action_url'], 40, '…')); ?></a><?php } else { ?>-<?php } ?></td>
                        <td data-label="상태">
                            <span class="talk-apply-status talk-apply-status--<?php echo htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $item['status_label']; ?></span>
                            <?php if (!empty($item['is_sensitive'])) { ?><br><span class="talk-apply-status talk-apply-status--rejected">민감</span><?php } ?>
                            <?php if (!empty($item['force_admin_approval'])) { ?><br><span class="talk-admin-table__sub">승인필수</span><?php } ?>
                            <?php if ($item['published_at'] !== '' && $item['published_at'] !== '0000-00-00 00:00:00') { ?><br><span class="talk-admin-table__sub"><?php echo substr($item['published_at'], 0, 16); ?></span><?php } ?>
                        </td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <?php if ($can_manage) { ?>
                            <a href="<?php echo eottae_public_ai_admin_candidates_url($status); ?>&amp;edit=<?php echo (int) $item['candidate_id']; ?>" class="promo-admin-btn promo-admin-btn--sm">수정</a>
                            <?php if ($item['status'] === 'pending') { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-public-ai-approve="<?php echo (int) $item['candidate_id']; ?>">승인</button>
                            <?php } ?>
                            <?php if (in_array($item['status'], array('pending', 'approved'), true)) { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm promo-admin-btn--primary" data-public-ai-publish="<?php echo (int) $item['candidate_id']; ?>">바로 발행</button>
                            <?php } ?>
                            <?php if ($item['status'] === 'pending') { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-public-ai-reject="<?php echo (int) $item['candidate_id']; ?>">반려</button>
                            <?php } ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-public-ai-delete="<?php echo (int) $item['candidate_id']; ?>">삭제</button>
                            <?php } else { ?>
                            <span class="talk-admin-table__sub">—</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<style>
.public-ai-candidates-table__message {
  max-width: 280px;
  white-space: pre-wrap;
  word-break: break-word;
  font-size: 0.85rem;
  line-height: 1.45;
}
.talk-apply-status--published { color: #0369a1; }
.talk-apply-status--approved { color: #15803d; }
</style>

<?php eottae_public_ai_render_admin_actions_script($admin_token); ?>
<script>
(function () {
  if (typeof window.eottaePublicAiAdminPost !== 'function') return;

  function bindAction(attr, action, confirmMsg, extra) {
    document.querySelectorAll('[data-' + attr + ']').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-' + attr);
        if (confirmMsg && !confirm(confirmMsg)) return;
        btn.disabled = true;
        var fields = { candidate_id: id };
        if (extra) {
          Object.keys(extra).forEach(function (k) { fields[k] = extra[k]; });
        }
        window.eottaePublicAiAdminPost(action, fields).then(function (data) {
          alert(data.message || (data.success ? '처리했습니다.' : '실패했습니다.'));
          if (data.success) location.reload();
          else btn.disabled = false;
        });
      });
    });
  }

  bindAction('public-ai-approve', 'approve_candidate', '이 후보를 승인할까요?');
  bindAction('public-ai-reject', 'reject_candidate', '이 후보를 반려할까요?');
  bindAction('public-ai-delete', 'delete_candidate', '이 후보를 삭제 처리할까요?');
  bindAction('public-ai-publish', 'publish_candidate', '이 메시지를 홈 공개톡에 발행할까요?');

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
