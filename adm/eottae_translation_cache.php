<?php
$sub_menu = '100950';
include_once './_common.php';
include_once G5_LIB_PATH.'/eottae-translation.lib.php';
include_once G5_LIB_PATH.'/eottae-language-meta.lib.php';
if (is_file(G5_LIB_PATH.'/eottae-multilingual-ops.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-multilingual-ops.lib.php';
}

if (function_exists('eottae_translation_ensure_schema')) {
    eottae_translation_ensure_schema();
}

$auth = isset($auth) && is_array($auth) ? $auth : array();
$config = isset($config) && is_array($config) ? $config : array();
$g5 = isset($g5) && is_array($g5) ? $g5 : array('table_prefix' => 'g5_');

auth_check_menu($auth, $sub_menu, 'r');

$cache_table = function_exists('eottae_translation_table') ? eottae_translation_table() : $g5['table_prefix'].'post_translations';
$job_table = function_exists('eottae_translation_job_table') ? eottae_translation_job_table() : 'post_translation_jobs';
$exists = sql_fetch(" show tables like '".sql_escape_string($cache_table)."' ", false);
$job_exists = sql_fetch(" show tables like '".sql_escape_string($job_table)."' ", false);

if (!empty($_POST['act'])) {
    check_admin_token();

    $act = preg_replace('/[^a-z_]/', '', (string) $_POST['act']);
    if ($act === 'run_queue') {
        auth_check_menu($auth, $sub_menu, 'w');
    } else {
        auth_check_menu($auth, $sub_menu, 'd');
    }
    if (!empty($exists) && $act === 'delete_one') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id > 0) {
            sql_query(" delete from `{$cache_table}` where id = '{$id}' ");
        }
    } elseif (!empty($exists) && $act === 'delete_post') {
        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        $board_type = isset($_POST['board_type']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_POST['board_type']) : '';
        if ($post_id > 0 && $board_type !== '') {
            sql_query(" delete from `{$cache_table}` where post_id = '{$post_id}' and board_type = '".sql_escape_string($board_type)."' ");
            if (!empty($job_exists)) {
                sql_query(" delete from `{$job_table}` where post_id = '{$post_id}' and board_type = '".sql_escape_string($board_type)."' ");
            }
        }
    } elseif (!empty($job_exists) && $act === 'retry_failed_jobs') {
        sql_query(" update `{$job_table}` set status = 'queued', attempts = 0, last_error = '', updated_at = '".G5_TIME_YMDHIS."' where status = 'failed' ", false);
    } elseif (!empty($job_exists) && $act === 'run_queue') {
        $queue_limit = isset($_POST['queue_limit']) ? max(1, min(30, (int) $_POST['queue_limit'])) : 5;
        if (function_exists('eottae_translation_run_queue')) {
            $_SESSION['eottae_translation_queue_flash'] = eottae_translation_run_queue($queue_limit);
        }
    }

    goto_url(G5_ADMIN_URL.'/eottae_translation_cache.php');
}

$g5['title'] = '번역 캐시 관리';
include_once './admin.head.php';

$rows = (int) ($config['cf_page_rows'] ?? 15);
if ($rows < 1) {
    $rows = 15;
}
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$filter_status = isset($_GET['review_status']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['review_status']) : '';
if (!in_array($filter_status, array('', 'auto', 'reviewed'), true)) {
    $filter_status = '';
}
$from_record = ($page - 1) * $rows;
$total_count = 0;
$list = array();
$job_counts = array('queued' => 0, 'processing' => 0, 'done' => 0, 'failed' => 0);
$where_sql = '';
if ($filter_status !== '') {
    $where_sql = " where review_status = '".sql_escape_string($filter_status)."' ";
}

if (!empty($exists)) {
    $cnt = sql_fetch(" select count(*) as cnt from `{$cache_table}` {$where_sql} ");
    $total_count = isset($cnt['cnt']) ? (int) $cnt['cnt'] : 0;
    $result = sql_query(" select * from `{$cache_table}` {$where_sql} order by updated_at desc, id desc limit {$from_record}, {$rows} ");
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
}
$job_list = array();
if (!empty($job_exists)) {
    $job_result = sql_query(" select status, count(*) as cnt from `{$job_table}` group by status ", false);
    while ($row = sql_fetch_array($job_result)) {
        $status = (string) $row['status'];
        $job_counts[$status] = (int) $row['cnt'];
    }
    $job_recent = sql_query(" select * from `{$job_table}` order by updated_at desc, id desc limit 10 ", false);
    while ($row = sql_fetch_array($job_recent)) {
        $job_list[] = $row;
    }
}
$total_page = $rows > 0 ? (int) ceil($total_count / $rows) : 1;
$colspan = 11;
$paging_qs = 'page=';
if ($filter_status !== '') {
    $paging_qs = 'review_status='.$filter_status.'&amp;page=';
}
?>

<?php if (function_exists('eottae_multilingual_ops_render_panel')) {
    echo eottae_multilingual_ops_render_panel();
} ?>

<?php if (!empty($_SESSION['eottae_translation_queue_flash']) && is_array($_SESSION['eottae_translation_queue_flash'])) {
    $queue_flash = $_SESSION['eottae_translation_queue_flash'];
    unset($_SESSION['eottae_translation_queue_flash']);
    ?>
<div class="local_desc01 local_desc">
    <p>
        큐 처리 완료:
        <?php echo number_format((int) ($queue_flash['processed'] ?? 0)); ?>건 처리 /
        성공 <?php echo number_format((int) ($queue_flash['succeeded'] ?? 0)); ?> /
        실패 <?php echo number_format((int) ($queue_flash['failed'] ?? 0)); ?>
    </p>
</div>
<?php } ?>

<div class="local_desc01 local_desc">
    <p>자동 번역 캐시를 확인하고 검수·삭제할 수 있습니다. 원문 게시글을 수정하면 기존 캐시 무효화 구조가 유지됩니다.</p>
</div>

<div class="local_ov01 local_ov">
    <a href="<?php echo G5_ADMIN_URL; ?>/eottae_translation_cache.php" class="ov_listall<?php echo $filter_status === '' ? ' btn_ov01' : ''; ?>">전체</a>
    <a href="<?php echo G5_ADMIN_URL; ?>/eottae_translation_cache.php?review_status=auto" class="ov_listall<?php echo $filter_status === 'auto' ? ' btn_ov01' : ''; ?>">자동번역</a>
    <a href="<?php echo G5_ADMIN_URL; ?>/eottae_translation_cache.php?review_status=reviewed" class="ov_listall<?php echo $filter_status === 'reviewed' ? ' btn_ov01' : ''; ?>">검수완료</a>
</div>

<?php if (!empty($job_exists)) { ?>
<div class="local_desc01 local_desc">
    <p>
        번역 작업 큐:
        대기 <?php echo number_format($job_counts['queued']); ?>건 /
        처리중 <?php echo number_format($job_counts['processing']); ?>건 /
        완료 <?php echo number_format($job_counts['done']); ?>건 /
        실패 <?php echo number_format($job_counts['failed']); ?>건
    </p>
    <form method="post" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px;align-items:center" onsubmit="return confirm('실패한 번역 작업을 다시 대기 상태로 바꿀까요?');">
        <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
        <input type="hidden" name="act" value="retry_failed_jobs">
        <button type="submit" class="btn btn_02">실패 작업 재시도</button>
    </form>
    <form method="post" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px;align-items:center">
        <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
        <input type="hidden" name="act" value="run_queue">
        <label for="queue_limit">대기 작업 처리</label>
        <select name="queue_limit" id="queue_limit">
            <?php foreach (array(3, 5, 10, 20) as $queue_limit_option) { ?>
            <option value="<?php echo $queue_limit_option; ?>"<?php echo $queue_limit_option === 5 ? ' selected' : ''; ?>><?php echo $queue_limit_option; ?>건</option>
            <?php } ?>
        </select>
        <button type="submit" class="btn btn_01">지금 처리</button>
    </form>
</div>
<?php } ?>

<?php if (empty($exists)) { ?>
<div class="local_desc01 local_desc">
    <p>번역 캐시 테이블이 아직 생성되지 않았습니다.</p>
</div>
<?php } else { ?>
<div class="tbl_head01 tbl_wrap eottae-admin-cache">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">게시판</th>
        <th scope="col">게시글 ID</th>
        <th scope="col">원문 언어</th>
        <th scope="col">대상 언어</th>
        <th scope="col">상태</th>
        <th scope="col">제공자</th>
        <th scope="col">원문 수정일</th>
        <th scope="col">생성일</th>
        <th scope="col">수정일</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i = 0; $i < count($list); $i++) {
        $row = $list[$i];
        $source = function_exists('eottae_lang_label') ? eottae_lang_label($row['source_language'] ?? 'ko') : get_text($row['source_language'] ?? 'ko');
        $target = function_exists('eottae_lang_label') ? eottae_lang_label($row['target_language'] ?? 'ko') : get_text($row['target_language'] ?? '');
        $review_status = (string) ($row['review_status'] ?? 'auto');
        $review_label = function_exists('eottae_translation_review_status_label')
            ? eottae_translation_review_status_label($review_status)
            : ($review_status === 'reviewed' ? '검수완료' : '자동번역');
        ?>
    <tr>
        <td class="td_num"><?php echo (int) $row['id']; ?></td>
        <td><?php echo get_text($row['board_type']); ?></td>
        <td class="td_num"><?php echo (int) $row['post_id']; ?></td>
        <td><?php echo get_text($source); ?></td>
        <td><strong><?php echo get_text($target); ?></strong></td>
        <td><strong><?php echo get_text($review_label); ?></strong></td>
        <td><?php echo get_text($row['provider']); ?></td>
        <td><?php echo get_text($row['source_updated_at']); ?></td>
        <td><?php echo get_text($row['created_at']); ?></td>
        <td><?php echo get_text($row['updated_at']); ?></td>
        <td class="td_mng td_mng_s">
            <a href="<?php echo G5_ADMIN_URL; ?>/eottae_translation_edit.php?id=<?php echo (int) $row['id']; ?>" class="btn btn_03">검수</a>
            <form method="post" style="display:inline" onsubmit="return confirm('이 번역 캐시를 삭제할까요?');">
                <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
                <input type="hidden" name="act" value="delete_one">
                <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
                <button type="submit" class="btn btn_02">삭제</button>
            </form>
            <form method="post" style="display:inline" onsubmit="return confirm('이 게시글의 모든 번역 캐시를 삭제할까요?');">
                <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
                <input type="hidden" name="act" value="delete_post">
                <input type="hidden" name="post_id" value="<?php echo (int) $row['post_id']; ?>">
                <input type="hidden" name="board_type" value="<?php echo get_text($row['board_type']); ?>">
                <button type="submit" class="btn btn_01">글 전체 삭제</button>
            </form>
        </td>
    </tr>
    <?php } ?>
    <?php if (count($list) === 0) { ?>
    <tr><td colspan="<?php echo $colspan; ?>" class="empty_table">번역 캐시가 없습니다.</td></tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, G5_ADMIN_URL.'/eottae_translation_cache.php?'.$paging_qs); ?>
<?php } ?>

<?php if (!empty($job_exists)) { ?>
<h2 class="h2_frm">최근 번역 작업</h2>
<div class="tbl_head01 tbl_wrap eottae-admin-cache">
    <table>
    <caption>최근 번역 작업 목록</caption>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">게시판</th>
        <th scope="col">게시글 ID</th>
        <th scope="col">대상 언어</th>
        <th scope="col">상태</th>
        <th scope="col">시도</th>
        <th scope="col">오류</th>
        <th scope="col">수정일</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($job_list as $job) { ?>
    <tr>
        <td class="td_num"><?php echo (int) $job['id']; ?></td>
        <td><?php echo get_text($job['board_type']); ?></td>
        <td class="td_num"><?php echo (int) $job['post_id']; ?></td>
        <td><?php echo get_text(function_exists('eottae_lang_label') ? eottae_lang_label($job['target_language']) : $job['target_language']); ?></td>
        <td><strong><?php echo get_text($job['status']); ?></strong></td>
        <td class="td_num"><?php echo (int) $job['attempts']; ?></td>
        <td><?php echo get_text($job['last_error']); ?></td>
        <td><?php echo get_text($job['updated_at']); ?></td>
    </tr>
    <?php } ?>
    <?php if (count($job_list) === 0) { ?>
    <tr><td colspan="8" class="empty_table">번역 작업이 없습니다.</td></tr>
    <?php } ?>
    </tbody>
    </table>
</div>
<?php } ?>

<?php
include_once './admin.tail.php';
