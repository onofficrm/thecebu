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

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$filters = array(
    'room_id'      => isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0,
    'trigger_type' => isset($_GET['trigger_type']) ? trim((string) $_GET['trigger_type']) : '',
    'status'       => isset($_GET['status']) ? trim((string) $_GET['status']) : '',
    'date_from'    => isset($_GET['date_from']) ? trim((string) $_GET['date_from']) : '',
    'date_to'      => isset($_GET['date_to']) ? trim((string) $_GET['date_to']) : '',
);
$list = eottae_talkroom_ai_admin_list_logs($filters, $page, 30);
$trigger_types = eottae_talkroom_ai_trigger_types();
$statuses = eottae_talkroom_ai_log_statuses();
$rooms = eottae_talkroom_ai_admin_list_rooms(200);
$admin_token = eottae_talkroom_admin_token();
$total_pages = max(1, (int) ceil($list['total'] / max(1, $list['per_page'])));

g5_page_start('AI 발언 로그');
?>

<main class="promo-admin-page talk-admin-page talk-admin-ai-logs-page">
    <header class="promo-admin-page__header">
        <div class="promo-admin-page__header-top">
            <a href="<?php echo eottae_talkroom_ai_admin_url(); ?>" class="promo-admin-page__back">← AI 도우미 설정</a>
            <a href="<?php echo G5_ADMIN_URL; ?>/" class="promo-admin-page__back">그누보드 관리자</a>
        </div>
        <h1 class="promo-admin-page__title">AI 발언 로그</h1>
        <p class="promo-admin-page__desc">톡방 AI 도우미의 자동 발언·리액션·테스트 기록을 확인하고, AI가 작성한 글/댓글을 삭제할 수 있습니다.</p>
        <?php eottae_talkroom_render_admin_nav('ai_logs'); ?>
    </header>

    <section class="promo-admin-panel talk-admin-panel">
        <form class="talk-apply-form talk-ai-logs-filter" method="get" action="<?php echo eottae_talkroom_ai_logs_url(); ?>">
            <div class="talk-ai-logs-filter__row">
                <label>톡방
                    <select name="room_id" class="talk-apply-form__select">
                        <option value="0">전체</option>
                        <?php foreach ($rooms as $room) { ?>
                        <option value="<?php echo (int) $room['room_id']; ?>"<?php echo $filters['room_id'] === (int) $room['room_id'] ? ' selected' : ''; ?>><?php echo $room['emoji'].' '.$room['room_name']; ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>트리거
                    <select name="trigger_type" class="talk-apply-form__select">
                        <option value="">전체</option>
                        <?php foreach ($trigger_types as $key => $label) { ?>
                        <option value="<?php echo get_text($key); ?>"<?php echo $filters['trigger_type'] === $key ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>상태
                    <select name="status" class="talk-apply-form__select">
                        <option value="">전체</option>
                        <?php foreach ($statuses as $key => $label) { ?>
                        <option value="<?php echo get_text($key); ?>"<?php echo $filters['status'] === $key ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                        <?php } ?>
                    </select>
                </label>
                <label>시작일 <input type="date" name="date_from" value="<?php echo get_text($filters['date_from']); ?>" class="talk-apply-form__input"></label>
                <label>종료일 <input type="date" name="date_to" value="<?php echo get_text($filters['date_to']); ?>" class="talk-apply-form__input"></label>
                <button type="submit" class="promo-admin-btn promo-admin-btn--primary">필터</button>
            </div>
        </form>
    </section>

    <section class="promo-admin-panel talk-admin-panel">
        <?php if (empty($list['rows'])) { ?>
        <p class="promo-admin-empty">표시할 로그가 없습니다.</p>
        <?php } else { ?>
        <div class="talk-admin-table-wrap">
            <table class="talk-admin-table talk-ai-logs-table">
                <thead>
                    <tr>
                        <th scope="col">날짜</th>
                        <th scope="col">톡방</th>
                        <th scope="col">트리거</th>
                        <th scope="col">AI 메시지</th>
                        <th scope="col">글/댓글</th>
                        <th scope="col">상태</th>
                        <th scope="col">에러</th>
                        <th scope="col">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list['rows'] as $row) { ?>
                    <tr class="talk-ai-logs-table__row">
                        <td data-label="날짜"><?php echo substr($row['created_at'], 0, 16); ?></td>
                        <td data-label="톡방"><?php echo get_text($row['room_emoji']); ?> <?php echo $row['room_name'] !== '' ? get_text($row['room_name']) : ('#'.(int) $row['room_id']); ?></td>
                        <td data-label="트리거"><span class="talk-ai-msg__badge talk-ai-msg__badge--sm"><span class="talk-ai-msg__icon" aria-hidden="true">💬</span><span class="talk-ai-msg__badge-label"><?php echo $row['trigger_label']; ?></span></span></td>
                        <td data-label="AI 메시지" class="talk-ai-logs-table__message"><?php echo get_text($row['response_text']); ?></td>
                        <td data-label="글/댓글">
                            <?php if ($row['post_id'] > 0) { ?>
                            <?php if ($row['content_link'] !== '') { ?><a href="<?php echo $row['content_link']; ?>">글 #<?php echo (int) $row['post_id']; ?></a><?php } else { ?>글 #<?php echo (int) $row['post_id']; ?><?php } ?>
                            <?php } ?>
                            <?php if ($row['comment_id'] > 0) { ?>
                            <?php if ($row['post_id'] > 0) { ?><br><?php } ?>댓글 #<?php echo (int) $row['comment_id']; ?>
                            <?php } ?>
                            <?php if ($row['post_id'] < 1 && $row['comment_id'] < 1) { ?>-<?php } ?>
                        </td>
                        <td data-label="상태"><?php echo $row['status_label']; ?></td>
                        <td data-label="에러"><?php echo $row['error_message'] !== '' ? get_text($row['error_message']) : '-'; ?></td>
                        <td data-label="관리" class="talk-admin-table__actions">
                            <?php if ($row['post_id'] > 0) { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-ai-delete="<?php echo (int) $row['post_id']; ?>">글 삭제</button>
                            <?php } ?>
                            <?php if ($row['comment_id'] > 0) { ?>
                            <button type="button" class="promo-admin-btn promo-admin-btn--sm" data-ai-delete="<?php echo (int) $row['comment_id']; ?>">댓글 삭제</button>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_pages > 1) { ?>
        <nav class="talk-ai-logs-pagination" aria-label="로그 페이지">
            <?php for ($p = 1; $p <= $total_pages; $p++) {
                if ($p > 20) {
                    break;
                }
                $qs = array_merge($filters, array('page' => $p));
                ?>
            <a href="<?php echo eottae_talkroom_ai_logs_url($qs); ?>" class="promo-admin-btn promo-admin-btn--sm<?php echo $p === $page ? ' is-active' : ''; ?>"><?php echo $p; ?></a>
            <?php } ?>
        </nav>
        <?php } ?>
        <?php } ?>
    </section>
</main>

<script>
(function () {
  var adminToken = <?php echo json_encode((string) $admin_token, JSON_UNESCAPED_UNICODE); ?>;
  document.querySelectorAll('[data-ai-delete]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var wrId = btn.getAttribute('data-ai-delete');
      if (!wrId || !confirm('AI가 작성한 글/댓글 #' + wrId + '을(를) 삭제할까요?')) return;
      btn.disabled = true;
      var fd = new FormData();
      fd.append('action', 'delete_ai_content');
      fd.append('wr_id', wrId);
      fd.append('eottae_talkroom_admin_token', adminToken);
      fetch('<?php echo G5_URL; ?>/proc/eottae-talkroom-ai-admin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          alert(data.message || (data.success ? '삭제했습니다.' : '실패했습니다.'));
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
