<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 칼럼니스트 목록 패널 (검색·필터·테이블·쪽지)
 *
 * @var array<int, array<string, mixed>> $authors
 * @var array<string, int> $summary
 * @var string $search
 * @var string $filter
 * @var string $sort
 * @var array<string, string> $list_query
 * @var callable(array): string $authors_list_url URL 빌더 (목록 페이지 기준)
 */
if (!isset($authors) || !is_array($authors)) {
    $authors = array();
}
if (!isset($summary) || !is_array($summary)) {
    $summary = array('total' => 0, 'active' => 0, 'visible' => 0, 'official' => 0);
}
$search = isset($search) ? (string) $search : '';
$filter = isset($filter) ? (string) $filter : 'all';
$sort = isset($sort) ? (string) $sort : 'updated';
$list_query = isset($list_query) && is_array($list_query) ? $list_query : array();

if (!is_callable($authors_list_url)) {
    $authors_list_url = function_exists('eottae_column_admin_authors_list_url')
        ? 'eottae_column_admin_authors_list_url'
        : 'eottae_column_admin_authors_url';
}
$authors_manage_url = function_exists('eottae_column_admin_authors_url')
    ? 'eottae_column_admin_authors_url'
    : function (array $params = array()) {
        $url = G5_URL.'/page/eottae-admin-column-authors.php';
        if (!empty($params)) {
            $url .= '?'.http_build_query($params);
        }

        return $url;
    };

$list_url = static function (array $params = array()) use ($authors_list_url) {
    return is_string($authors_list_url) ? call_user_func($authors_list_url, $params) : $authors_list_url($params);
};
$manage_url = static function (array $params = array()) use ($authors_manage_url) {
    return is_string($authors_manage_url) ? call_user_func($authors_manage_url, $params) : $authors_manage_url($params);
};
?>

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
    <form class="sebu-column-authors-toolbar__search" method="get" action="<?php echo get_text($list_url()); ?>">
        <?php if ($filter !== 'all') { ?><input type="hidden" name="filter" value="<?php echo get_text($filter); ?>"><?php } ?>
        <?php if ($sort !== 'updated') { ?><input type="hidden" name="sort" value="<?php echo get_text($sort); ?>"><?php } ?>
        <label class="sebu-column-authors-toolbar__search-label">
            <span class="sound_only">검색</span>
            <input type="search" name="q" class="sebu-column-form__input" value="<?php echo get_text($search); ?>" placeholder="필명, 회원ID, 타이틀 검색">
        </label>
        <button type="submit" class="sebu-column-btn sebu-column-btn--sm">검색</button>
        <?php if ($search !== '') { ?>
        <a href="<?php echo get_text($list_url(array('filter' => $filter !== 'all' ? $filter : null, 'sort' => $sort !== 'updated' ? $sort : null))); ?>" class="sebu-column-btn sebu-column-btn--ghost sebu-column-btn--sm">초기화</a>
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
        <a href="<?php echo get_text($list_url($params)); ?>" class="sebu-column-sort-btn<?php echo $filter === $code ? ' is-active' : ''; ?>"><?php echo $label; ?></a>
        <?php } ?>
    </div>

    <div class="sebu-column-authors-toolbar__actions">
        <button type="button" class="sebu-column-btn sebu-column-btn--primary sebu-column-btn--sm" data-sebu-column-bulk-memo>전체 쪽지 발송</button>
        <button type="button" class="sebu-column-btn sebu-column-btn--outline sebu-column-btn--sm" data-sebu-column-selected-memo disabled>선택 쪽지 (<span data-sebu-column-selected-count>0</span>)</button>
    </div>
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
            <a href="<?php echo get_text($list_url($params)); ?>" class="sebu-column-sort-btn<?php echo $sort === $code ? ' is-active' : ''; ?>"><?php echo $label; ?></a>
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
                        <a href="<?php echo get_text($manage_url($edit_params).'#sebu-column-author-form-panel'); ?>" class="sebu-column-btn sebu-column-btn--ghost sebu-column-btn--sm">수정</a>
                        <button type="button" class="sebu-column-btn sebu-column-btn--sm" data-sebu-column-single-memo data-mb-id="<?php echo get_text($mb_id); ?>" data-display-name="<?php echo get_text($author['display_name'] ?? $mb_id); ?>"<?php echo empty($author['member_ok']) ? ' disabled title="탈퇴·차단 회원"' : ''; ?>>쪽지</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</section>

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
