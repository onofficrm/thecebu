<?php
$sub_menu = '100950';
include_once './_common.php';
include_once G5_LIB_PATH.'/eottae-translation.lib.php';
include_once G5_LIB_PATH.'/eottae-language-meta.lib.php';

$auth = isset($auth) && is_array($auth) ? $auth : array();
$config = isset($config) && is_array($config) ? $config : array();

auth_check_menu($auth, $sub_menu, 'r');

$cache_table = function_exists('eottae_translation_table') ? eottae_translation_table() : $g5['table_prefix'].'post_translations';
$exists = sql_fetch(" show tables like '".sql_escape_string($cache_table)."' ", false);

if (!empty($_POST['act'])) {
    auth_check_menu($auth, $sub_menu, 'd');
    check_admin_token();

    $act = preg_replace('/[^a-z_]/', '', (string) $_POST['act']);
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
$from_record = ($page - 1) * $rows;
$total_count = 0;
$list = array();

if (!empty($exists)) {
    $cnt = sql_fetch(" select count(*) as cnt from `{$cache_table}` ");
    $total_count = isset($cnt['cnt']) ? (int) $cnt['cnt'] : 0;
    $result = sql_query(" select * from `{$cache_table}` order by updated_at desc, id desc limit {$from_record}, {$rows} ");
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
}
$total_page = $rows > 0 ? (int) ceil($total_count / $rows) : 1;
$colspan = 10;
?>

<div class="local_desc01 local_desc">
    <p>자동 번역 캐시를 확인하고 삭제할 수 있습니다. 원문 게시글을 수정하면 기존 캐시 무효화 구조가 유지됩니다.</p>
</div>

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
        ?>
    <tr>
        <td class="td_num"><?php echo (int) $row['id']; ?></td>
        <td><?php echo get_text($row['board_type']); ?></td>
        <td class="td_num"><?php echo (int) $row['post_id']; ?></td>
        <td><?php echo get_text($source); ?></td>
        <td><strong><?php echo get_text($target); ?></strong></td>
        <td><?php echo get_text($row['provider']); ?></td>
        <td><?php echo get_text($row['source_updated_at']); ?></td>
        <td><?php echo get_text($row['created_at']); ?></td>
        <td><?php echo get_text($row['updated_at']); ?></td>
        <td class="td_mng td_mng_s">
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

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, G5_ADMIN_URL.'/eottae_translation_cache.php?page='); ?>
<?php } ?>

<?php
include_once './admin.tail.php';
