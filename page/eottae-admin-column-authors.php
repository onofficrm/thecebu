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
            · <a href="<?php echo eottae_column_admin_url(array('tab' => 'applications')); ?>">신청 관리</a>
        </p>
    </header>

    <div class="sebu-column-authors-summary" aria-label="칼럼니스트 현황">
        <div class="sebu-column-authors-summary__item">
            <span class="sebu-column-authors-summary__label">전체</span>
            <strong><?php echo number_format($summary['total']); ?></strong>
        </div>
        <div class="sebu-column-authors-summary__item">
            <span class="sebu-column-authors-summary__label">활성</span>
            <strong><?php echo number_format($summary['active']); ?></strong>
        </div>
        <div class="sebu-column-authors-summary__item">
            <span class="sebu-column-authors-summary__label">프로필 노출</span>
            <strong><?php echo number_format($summary['visible']); ?></strong>
        </div>
        <div class="sebu-column-authors-summary__item">
            <span class="sebu-column-authors-summary__label">공식</span>
            <strong><?php echo number_format($summary['official']); ?></strong>
        </div>
    </div>

    <section class="sebu-column-admin__panel sebu-column-authors-toolbar">
        <form class="sebu-column-authors-toolbar__search" method="get" action="<?php echo eottae_column_admin_authors_url(); ?>">
            <?php if ($filter !== 'all') { ?><input type="hidden" name="filter" value="<?php echo get_text($filter); ?>"><?php } ?>
            <?php if ($sort !== 'updated') { ?><input type="hidden" name="sort" value="<?php echo get_text($sort); ?>"><?php } ?>
            <label class="sebu-column-authors-toolbar__search-label">
                <span class="sound_only">검색</span>
                <input type="search" name="q" class="sebu-column-form__input" value="<?php echo get_text($search); ?>" placeholder="필명, 회원ID, 타이틀 검색">
            </label>
            <button type="submit" class="sebu-column-btn sebu-column-btn--sm">검색</button>
            <?php if ($search !== '') { ?>
            <a href="<?php echo eottae_column_admin_authors_url(array('filter' => $filter !== 'all' ? $filter : null, 'sort' => $sort !== 'updated' ? $sort : null)); ?>" class="sebu-column-btn sebu-column-btn--ghost sebu-column-btn--sm">초기화</a>
            <?php } ?>
        </form>

        <div class="sebu-column-authors-toolbar__filters" role="group" aria-label="필터">
            <?php
            $filter_links = array(
                'all'      => '전체',
                'active'   => '활성',
                'inactive' => '비활성',
                'visible'  => '노출',
                'hidden'   => '숨김',
                'official' => '공식',
            );
            foreach ($filter_links as $code => $label) {
                $params = $list_query;
                if ($code !== 'all') {
                    $params['filter'] = $code;
                } else {
                    unset($params['filter']);
                }
                ?>
            <a href="<?php echo eottae_column_admin_authors_url($params); ?>" class="sebu-column-sort-btn<?php echo $filter === $code ? ' is-active' : ''; ?>"><?php echo $label; ?></a>
            <?php } ?>
        </div>

        <div class="sebu-column-authors-toolbar__actions">
            <button type="button" class="sebu-column-btn sebu-column-btn--primary sebu-column-btn--sm" data-sebu-column-bulk-memo>전체 쪽지 발송</button>
            <button type="button" class="sebu-column-btn sebu-column-btn--outline sebu-column-btn--sm" data-sebu-column-selected-memo disabled>선택 쪽지 (<span data-sebu-column-selected-count>0</span>)</button>
        </div>
    </section>

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

    <section class="sebu-column-admin__panel">
        <div class="sebu-column-section__head">
            <h2 class="sebu-column-admin__panel-title">칼럼니스트 목록 <span class="sebu-column-authors-count">(<?php echo number_format(count($authors)); ?>)</span></h2>
            <div class="sebu-column-section__sort">
                <?php
                $sort_links = array(
                    'updated' => '최근 수정',
                    'columns' => '컬럼 수',
                    'views'   => '조회수',
                    'name'    => '이름',
                );
                foreach ($sort_links as $code => $label) {
                    $params = $list_query;
                    if ($code !== 'updated') {
                        $params['sort'] = $code;
                    } else {
                        unset($params['sort']);
                    }
                    ?>
                <a href="<?php echo eottae_column_admin_authors_url($params); ?>" class="sebu-column-sort-btn<?php echo $sort === $code ? ' is-active' : ''; ?>"><?php echo $label; ?></a>
                <?php } ?>
            </div>
        </div>

        <?php if (empty($authors)) { ?>
        <p class="sebu-column-empty">조건에 맞는 칼럼니스트가 없습니다.</p>
        <?php } else { ?>
        <div class="sebu-column-admin__table-wrap">
            <table class="sebu-column-admin__table sebu-column-authors-table">
                <thead>
                    <tr>
                        <th class="sebu-column-authors-table__check">
                            <label class="sebu-column-authors-check-all">
                                <input type="checkbox" data-sebu-column-select-all aria-label="전체 선택">
                            </label>
                        </th>
                        <th>칼럼니스트</th>
                        <th>상태</th>
                        <th>활동</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($authors as $author) {
                        $mb_id = (string) ($author['mb_id'] ?? '');
                        $edit_params = array_merge($list_query, array('edit' => $mb_id));
                        ?>
                    <tr data-sebu-column-author-row data-mb-id="<?php echo get_text($mb_id); ?>"<?php echo empty($author['member_ok']) ? ' class="is-member-issue"' : ''; ?>>
                        <td class="sebu-column-authors-table__check">
                            <input type="checkbox" class="sebu-column-author-select" value="<?php echo get_text($mb_id); ?>" aria-label="<?php echo get_text($author['display_name'] ?? $mb_id); ?> 선택"<?php echo empty($author['member_ok']) ? ' disabled' : ''; ?>>
                        </td>
                        <td>
                            <div class="sebu-column-authors-cell">
                                <?php if (!empty($author['has_profile_image'])) { ?>
                                <img src="<?php echo get_text($author['profile_image_url']); ?>" alt="" class="sebu-column-authors-cell__avatar" width="44" height="44" loading="lazy">
                                <?php } else { ?>
                                <span class="sebu-column-authors-cell__avatar sebu-column-avatar--initials" aria-hidden="true"><?php echo get_text($author['profile_initials'] ?? ''); ?></span>
                                <?php } ?>
                                <div class="sebu-column-authors-cell__body">
                                    <strong class="sebu-column-authors-cell__name"><?php echo get_text($author['display_name'] ?? ''); ?></strong>
                                    <span class="sebu-column-authors-cell__id"><?php echo get_text($mb_id); ?></span>
                                    <?php if (($author['title'] ?? '') !== '') { ?>
                                    <span class="sebu-column-authors-cell__title"><?php echo get_text($author['title']); ?></span>
                                    <?php } ?>
                                    <?php if (!empty($author['grade_label'])) { ?>
                                    <span class="sebu-column-authors-cell__grade"><?php echo get_text($author['grade_label']); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="sebu-column-authors-flags">
                                <button type="button" class="sebu-column-authors-flag<?php echo !empty($author['is_active']) ? ' is-on' : ''; ?>" data-sebu-column-toggle="is_active" data-value="<?php echo !empty($author['is_active']) ? '0' : '1'; ?>" title="활성 토글">활성</button>
                                <button type="button" class="sebu-column-authors-flag<?php echo !empty($author['is_visible']) ? ' is-on' : ''; ?>" data-sebu-column-toggle="is_visible" data-value="<?php echo !empty($author['is_visible']) ? '0' : '1'; ?>" title="노출 토글">노출</button>
                                <button type="button" class="sebu-column-authors-flag sebu-column-authors-flag--official<?php echo !empty($author['is_official']) ? ' is-on' : ''; ?>" data-sebu-column-toggle="is_official" data-value="<?php echo !empty($author['is_official']) ? '0' : '1'; ?>" title="공식 토글">공식</button>
                            </div>
                            <?php if (!empty($author['member_left'])) { ?><span class="sebu-column-authors-warn">탈퇴 회원</span><?php } elseif (!empty($author['member_blocked'])) { ?><span class="sebu-column-authors-warn">차단 회원</span><?php } ?>
                        </td>
                        <td class="sebu-column-authors-stats">
                            컬럼 <strong><?php echo number_format((int) ($author['stats']['column_count'] ?? 0)); ?></strong>
                            · 조회 <?php echo number_format((int) ($author['stats']['total_views'] ?? 0)); ?>
                            · 추천 <?php echo number_format((int) ($author['stats']['total_likes'] ?? 0)); ?>
                            <?php if (!empty($author['updated_at'])) { ?>
                            <span class="sebu-column-authors-stats__date">수정 <?php echo substr((string) $author['updated_at'], 0, 10); ?></span>
                            <?php } ?>
                        </td>
                        <td class="sebu-column-authors-actions">
                            <a href="<?php echo get_text($author['profile_url'] ?? '#'); ?>" class="sebu-column-btn sebu-column-btn--ghost sebu-column-btn--sm" target="_blank" rel="noopener noreferrer">프로필</a>
                            <a href="<?php echo eottae_column_admin_authors_url($edit_params); ?>#sebu-column-author-form-panel" class="sebu-column-btn sebu-column-btn--ghost sebu-column-btn--sm">수정</a>
                            <button type="button" class="sebu-column-btn sebu-column-btn--sm" data-sebu-column-single-memo data-mb-id="<?php echo get_text($mb_id); ?>" data-display-name="<?php echo get_text($author['display_name'] ?? $mb_id); ?>"<?php echo empty($author['member_ok']) ? ' disabled title="탈퇴·차단 회원"' : ''; ?>>쪽지</button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>

<dialog class="sebu-column-memo-dialog" id="sebuColumnMemoDialog">
    <form method="dialog" class="sebu-column-memo-dialog__form" data-sebu-column-memo-form>
        <header class="sebu-column-memo-dialog__head">
            <h2 class="sebu-column-memo-dialog__title" data-sebu-column-memo-title>쪽지 발송</h2>
            <button type="button" class="sebu-column-memo-dialog__close" data-sebu-column-memo-close aria-label="닫기">&times;</button>
        </header>
        <p class="sebu-column-memo-dialog__desc" data-sebu-column-memo-desc>선택한 칼럼니스트에게 사이트 쪽지가 발송됩니다. 메시지 앞에 [세부어때 · 생활정보 컬럼] 안내가 붙습니다.</p>
        <input type="hidden" name="memo_scope" value="selected" data-sebu-column-memo-scope>
        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label">메시지 *</span>
            <textarea name="memo_body" class="sebu-column-form__textarea" rows="8" required maxlength="60000" placeholder="칼럼니스트에게 전달할 안내를 입력하세요."></textarea>
        </label>
        <footer class="sebu-column-memo-dialog__foot">
            <button type="button" class="sebu-column-btn sebu-column-btn--ghost" data-sebu-column-memo-close>취소</button>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary">쪽지 발송</button>
        </footer>
    </form>
</dialog>

<?php
g5_page_end();
