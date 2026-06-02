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
$edit_mb_id = isset($_GET['edit']) ? preg_replace('/[^a-z0-9_]/i', '', (string) $_GET['edit']) : '';

if (!in_array($filter, array('all', 'active', 'inactive', 'visible', 'hidden', 'official'), true)) {
    $filter = 'all';
}
if (!in_array($sort, array('updated', 'name', 'mb_id', 'created', 'columns', 'views'), true)) {
    $sort = 'updated';
}

$admin_token = eottae_talkroom_admin_token();
$proc_url = eottae_column_admin_proc_url();
$column_admin_url = eottae_column_admin_url();
$summary = eottae_column_admin_authors_summary();
$authors = eottae_column_admin_list_authors_filtered(array(
    'search' => $search,
    'filter' => $filter,
    'sort'   => $sort,
    'limit'  => 300,
));
$edit_author = $edit_mb_id !== '' ? eottae_column_get_author($edit_mb_id) : null;
if ($edit_author) {
    $edit_author = eottae_column_enrich_author($edit_author);
}

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

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column-admin-authors.js" defer></script>', 24);

g5_page_start('칼럼니스트 관리');
?>

<main class="sebu-column-admin sebu-column-admin-authors sebu-column-editorial"
      data-proc-url="<?php echo get_text($proc_url); ?>"
      data-admin-token="<?php echo get_text($admin_token); ?>"
      data-list-filter="<?php echo get_text($filter); ?>"
      data-list-search="<?php echo get_text($search); ?>">
    <header class="sebu-column-admin__header">
        <h1 class="sebu-column-admin__title">칼럼니스트 관리</h1>
        <p class="sebu-column-admin__desc">
            <a href="<?php echo eottae_column_list_url(); ?>">컬럼 섹션 보기 →</a>
            · <a href="<?php echo get_text($column_admin_url); ?>">컬럼 관리</a>
            · <a href="<?php echo eottae_column_admin_authors_list_url(); ?>">칼럼니스트 목록만 보기</a>
            · <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications')); ?>">신청 관리</a>
        </p>
    </header>

    <nav class="sebu-column-admin__tabs" aria-label="칼럼니스트 관리">
        <a href="<?php echo eottae_column_admin_authors_list_url(); ?>" class="sebu-column-admin__tab">칼럼니스트 목록</a>
        <a href="<?php echo eottae_column_admin_authors_url(); ?>" class="sebu-column-admin__tab is-active">등록·수정</a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications')); ?>" class="sebu-column-admin__tab">신청 관리</a>
        <a href="<?php echo eottae_column_admin_url(array('tab' => 'monthly')); ?>" class="sebu-column-admin__tab">이달의 칼럼니스트</a>
    </nav>

    <section class="sebu-column-admin__panel" id="sebu-column-author-form-panel">
        <h2 class="sebu-column-admin__panel-title"><?php echo $edit_author ? '칼럼니스트 수정' : '칼럼니스트 등록'; ?></h2>
        <?php if ($edit_author) { ?>
        <p class="sebu-column-authors-edit-note">
            <strong><?php echo get_text($edit_author['display_name'] ?? ''); ?></strong>
            (<?php echo get_text($edit_author['mb_id'] ?? ''); ?>) 수정 중
            · <a href="<?php echo eottae_column_admin_authors_url($list_query); ?>">새 등록으로 전환</a>
        </p>
        <?php } ?>
        <form class="sebu-column-admin-form" method="post" action="<?php echo get_text($proc_url); ?>" enctype="multipart/form-data" data-sebu-column-author-form>
            <input type="hidden" name="action" value="save_author">
            <input type="hidden" name="admin_token" value="<?php echo get_text($admin_token); ?>">

            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">회원 ID (mb_id) *</span>
                <input type="text" name="mb_id" class="sebu-column-form__input" required<?php echo $edit_author ? ' readonly' : ''; ?> value="<?php echo $edit_author ? get_text($edit_author['mb_id'] ?? '') : ''; ?>">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">필명</span>
                <input type="text" name="pen_name" class="sebu-column-form__input" value="<?php echo $edit_author ? get_text($edit_author['pen_name'] ?? '') : ''; ?>">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">타이틀</span>
                <input type="text" name="title" class="sebu-column-form__input" placeholder="예: 교육·가족 컬럼니스트" value="<?php echo $edit_author ? get_text($edit_author['title'] ?? '') : ''; ?>">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">전문 분야</span>
                <input type="text" name="specialty" class="sebu-column-form__input" value="<?php echo $edit_author ? get_text($edit_author['specialty'] ?? '') : ''; ?>">
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">소개글</span>
                <textarea name="bio" class="sebu-column-form__textarea" rows="4"><?php echo $edit_author ? get_text($edit_author['bio'] ?? '') : ''; ?></textarea>
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">프로필 이미지</span>
                <input type="file" name="profile_image" accept="image/*">
                <?php if ($edit_author && !empty($edit_author['has_profile_image'])) { ?>
                <input type="hidden" name="profile_image_keep" value="1">
                <?php } ?>
            </label>
            <label class="sebu-column-form__field">
                <span class="sebu-column-form__label">활동 지역</span>
                <select name="area" class="sebu-column-form__select">
                    <option value="">선택</option>
                    <?php foreach (eottae_column_area_options() as $code => $label) { ?>
                    <option value="<?php echo get_text($code); ?>"<?php echo $edit_author && ($edit_author['area'] ?? '') === $code ? ' selected' : ''; ?>><?php echo get_text($label); ?></option>
                    <?php } ?>
                </select>
            </label>
            <label class="sebu-column-form__field"><input type="checkbox" name="is_active" value="1"<?php echo !$edit_author || !empty($edit_author['is_active']) ? ' checked' : ''; ?>> 칼럼니스트 활성</label>
            <label class="sebu-column-form__field"><input type="checkbox" name="is_visible" value="1"<?php echo !$edit_author || !empty($edit_author['is_visible']) ? ' checked' : ''; ?>> 프로필 노출</label>
            <label class="sebu-column-form__field"><input type="checkbox" name="is_official" value="1"<?php echo $edit_author && !empty($edit_author['is_official']) ? ' checked' : ''; ?>> 공식 칼럼니스트</label>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary"><?php echo $edit_author ? '수정 저장' : '칼럼니스트 저장'; ?></button>
        </form>
    </section>

    <p class="sebu-column-authors-manage-list-link">
        <a href="<?php echo eottae_column_admin_authors_list_url($list_query); ?>" class="sebu-column-btn sebu-column-btn--outline sebu-column-btn--sm">칼럼니스트 목록 보기 (<?php echo number_format(count($authors)); ?>명)</a>
    </p>
</main>

<?php
g5_page_end();
