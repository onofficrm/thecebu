<?php
include_once(dirname(__FILE__).'/_init.php');

if ($is_admin !== 'super') {
    alert('최고관리자만 이용할 수 있습니다.', G5_URL);
}

include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-admin-authors.lib.php';
include_once G5_PATH.'/components/eottae/column-author-profile.php';
include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';

eottae_column_ensure_schema();
eottae_column_ensure_badges();

$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
$filter = isset($_GET['filter']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['filter']) : 'all';
$sort = isset($_GET['sort']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['sort']) : 'updated';

if (!in_array($filter, array('all', 'active', 'inactive', 'visible', 'hidden', 'official'), true)) {
    $filter = 'all';
}
if (!in_array($sort, array('updated', 'name', 'mb_id', 'created', 'columns', 'views'), true)) {
    $sort = 'updated';
}

$admin_token = eottae_talkroom_admin_token();
$proc_url = eottae_column_admin_proc_url();
$column_admin_url = eottae_column_admin_url();
$authors_manage_url = eottae_column_admin_authors_url();
$summary = eottae_column_admin_authors_summary();
$authors = eottae_column_admin_list_authors_filtered(array(
    'search' => $search,
    'filter' => $filter,
    'sort'   => $sort,
    'limit'  => 300,
));

$list_query = array();
if ($search !== '') {
    $list_query['q'] = $search;
}
if ($filter !== 'all') {
    $list_query['filter'] = $filter;
}
if ($sort !== 'updated') {
    $list_query['sort'] = $sort;
}

$authors_list_url = 'eottae_column_admin_authors_list_url';

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column-admin-authors.js" defer></script>', 24);

g5_page_start('칼럼니스트 목록');
?>

<main class="sebu-column-admin sebu-column-admin-authors sebu-column-admin-authors--list-only sebu-column-editorial"
      data-proc-url="<?php echo get_text($proc_url); ?>"
      data-admin-token="<?php echo get_text($admin_token); ?>"
      data-list-filter="<?php echo get_text($filter); ?>"
      data-list-search="<?php echo get_text($search); ?>">
    <header class="sebu-column-admin__header">
        <h1 class="sebu-column-admin__title">칼럼니스트 목록</h1>
        <p class="sebu-column-admin__desc">
            <a href="<?php echo eottae_column_list_url(); ?>">컬럼 섹션 보기 →</a>
            · <a href="<?php echo get_text($column_admin_url); ?>">컬럼 관리</a>
            · <a href="<?php echo get_text($authors_manage_url); ?>">칼럼니스트 등록·수정</a>
            · <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications')); ?>">신청 관리</a>
        </p>
    </header>

    <nav class="sebu-column-admin__tabs" aria-label="칼럼니스트 관리">
        <a href="<?php echo eottae_column_admin_authors_list_url(); ?>" class="sebu-column-admin__tab is-active">칼럼니스트 목록</a>
        <a href="<?php echo get_text($authors_manage_url); ?>" class="sebu-column-admin__tab">등록·수정</a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications')); ?>" class="sebu-column-admin__tab">신청 관리</a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'monthly')); ?>" class="sebu-column-admin__tab">이달의 칼럼니스트</a>
    </nav>

    <?php include G5_PATH.'/components/eottae/column-admin-authors-list.php'; ?>
</main>

<?php
g5_page_end();
