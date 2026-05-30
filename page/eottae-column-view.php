<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-column-bookmarks.lib.php';
include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
include_once G5_PATH.'/components/eottae/column-card.php';
include_once G5_PATH.'/components/eottae/column-author-card.php';
include_once G5_PATH.'/components/eottae/column-author-profile.php';

$wr_id = isset($_GET['wr_id']) ? (int) $_GET['wr_id'] : 0;
$member_mb_id = $is_member ? ($member['mb_id'] ?? '') : '';

$post = eottae_column_get_post($wr_id, array(
    'member_mb_id' => $member_mb_id,
    'is_super'       => ($is_admin === 'super'),
));

if (!$post) {
    alert('컬럼을 찾을 수 없습니다.', eottae_column_list_url());
}

eottae_column_apply_seo($post);

$author = $post['author'] ?? null;
$related = eottae_column_list(array(
    'category' => $post['category'] ?? '',
    'limit'    => 4,
    'member_mb_id' => $member_mb_id,
));
$related = array_values(array_filter($related, function ($item) use ($wr_id) {
    return (int) ($item['wr_id'] ?? 0) !== $wr_id;
}));
$author_more = array();
if ($author) {
    $author_more = eottae_column_list(array(
        'mb_id' => $author['mb_id'] ?? '',
        'limit' => 3,
        'member_mb_id' => $member_mb_id,
    ));
    $author_more = array_values(array_filter($author_more, function ($item) use ($wr_id) {
        return (int) ($item['wr_id'] ?? 0) !== $wr_id;
    }));
}

$comments = eottae_column_list_comments($wr_id);
$token = eottae_column_member_token();
$is_super = ($is_admin === 'super');
$can_edit = $is_member && eottae_column_can_edit($member['mb_id'] ?? '', $wr_id, $is_super);
$can_delete = $is_member && eottae_column_can_delete($member['mb_id'] ?? '', $wr_id, $is_super);
$thumb_url = trim((string) ($post['thumbnail_url'] ?? ''));
$has_thumb = $thumb_url !== '' && stripos($thumb_url, 'no_img') === false;
$tag_list = array();
if (!empty($post['tags'])) {
    foreach (preg_split('/\s*,\s*/', (string) $post['tags']) as $tag) {
        $tag = trim($tag);
        if ($tag !== '') {
            $tag_list[] = $tag;
        }
    }
}
$report_token = eottae_column_report_token();
$report_reasons = eottae_column_report_reasons();
$bo_table = eottae_column_board_table();
$comment_action = G5_BBS_URL.'/write_comment_update.php';
$column_youtube_id = (string) ($post['youtube_id'] ?? '');
$column_youtube_embed = '';
if ($column_youtube_id !== '') {
    if (!function_exists('g5b_youtube_embed_html')) {
        $yt_inc = G5_SKIN_PATH.'/board/_inc/g5b-youtube.php';
        if (is_file($yt_inc)) {
            include_once $yt_inc;
        }
    }
    if (function_exists('g5b_youtube_embed_html')) {
        $column_youtube_embed = g5b_youtube_embed_html($column_youtube_id, $post['wr_subject'] ?? '');
    }
}

add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap">', 20);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column.js" defer></script>', 24);

g5_page_start(get_text($post['wr_subject'] ?? '컬럼'));
?>

<main class="sebu-article-page sebu-column-editorial" data-sebu-column-view data-wr-id="<?php echo (int) $wr_id; ?>" data-proc-url="<?php echo get_text(eottae_column_proc_url()); ?>" data-member-token="<?php echo get_text($token); ?>">
    <nav class="sebu-article-page__breadcrumb" aria-label="경로">
        <a href="<?php echo eottae_column_list_url(); ?>"><?php echo eottae_column_menu_label(); ?></a>
        <?php if (!empty($post['category_label'])) { ?>
        <span class="sebu-article-page__breadcrumb-sep" aria-hidden="true">·</span>
        <a href="<?php echo eottae_column_category_url($post['category'] ?? ''); ?>"><?php echo get_text($post['category_label']); ?></a>
        <?php } ?>
    </nav>

    <?php if ($can_edit || $can_delete) { ?>
    <div class="sebu-article__owner-bar">
        <?php if ($can_edit) { ?>
        <a href="<?php echo eottae_column_write_url($wr_id); ?>" class="sebu-column-btn sebu-column-btn--outline sebu-column-btn--sm">수정</a>
        <?php } ?>
        <?php if ($can_delete) { ?>
        <button type="button" class="sebu-column-btn sebu-column-btn--danger sebu-column-btn--sm" data-sebu-column-delete>삭제</button>
        <?php } ?>
    </div>
    <?php } ?>

    <article class="sebu-article sebu-article--editorial">
        <?php if ($has_thumb) { ?>
        <div class="sebu-article__cover">
            <img src="<?php echo get_text($thumb_url); ?>" alt="" class="sebu-article__cover-img">
            <div class="sebu-article__cover-shade" aria-hidden="true"></div>
            <header class="sebu-article__cover-header">
                <?php if (!empty($post['category_label'])) { ?>
                <span class="sebu-column-badge sebu-column-badge--category sebu-column-badge--on-dark"><?php echo get_text($post['category_label']); ?></span>
                <?php } ?>
                <h1 class="sebu-article__title"><?php echo get_text($post['wr_subject'] ?? ''); ?></h1>
                <?php if (!empty($post['subtitle'])) { ?>
                <p class="sebu-article__subtitle"><?php echo get_text($post['subtitle']); ?></p>
                <?php } elseif (!empty($post['summary'])) { ?>
                <p class="sebu-article__subtitle"><?php echo get_text($post['summary']); ?></p>
                <?php } ?>
            </header>
        </div>
        <?php } else { ?>
        <header class="sebu-article__header sebu-article__header--solo">
            <?php if (!empty($post['category_label'])) { ?>
            <span class="sebu-column-badge sebu-column-badge--category"><?php echo get_text($post['category_label']); ?></span>
            <?php } ?>
            <h1 class="sebu-article__title"><?php echo get_text($post['wr_subject'] ?? ''); ?></h1>
            <?php if (!empty($post['subtitle'])) { ?>
            <p class="sebu-article__subtitle"><?php echo get_text($post['subtitle']); ?></p>
            <?php } elseif (!empty($post['summary'])) { ?>
            <p class="sebu-article__subtitle"><?php echo get_text($post['summary']); ?></p>
            <?php } ?>
        </header>
        <?php } ?>

        <div class="sebu-article__byline">
            <div class="sebu-article__byline-author">
                <?php if ($author) { ?>
                <?php echo eottae_column_render_author_profile_block_html($author, 'sm'); ?>
                <?php } else { ?>
                <span class="sebu-article__author-name"><?php echo get_text($post['author_name'] ?? ''); ?></span>
                <?php } ?>
            </div>
            <div class="sebu-article__byline-meta">
                <time datetime="<?php echo get_text($post['date_label'] ?? ''); ?>"><?php echo get_text($post['date_label'] ?? ''); ?></time>
                <span class="sebu-article__byline-dot" aria-hidden="true">·</span>
                <span><?php echo get_text($post['read_time_label'] ?? ''); ?></span>
            </div>
            <dl class="sebu-article__stats">
                <div><dt>조회</dt><dd><?php echo number_format((int) ($post['wr_hit'] ?? 0)); ?></dd></div>
                <div><dt>댓글</dt><dd><?php echo number_format((int) ($post['wr_comment'] ?? 0)); ?></dd></div>
                <div><dt>공감</dt><dd><?php echo number_format((int) ($post['like_count'] ?? 0)); ?></dd></div>
            </dl>
        </div>

        <?php if (!empty($tag_list)) { ?>
        <ul class="sebu-article__tags">
            <?php foreach ($tag_list as $tag) { ?>
            <li><span class="sebu-article__tag">#<?php echo get_text($tag); ?></span></li>
            <?php } ?>
        </ul>
        <?php } ?>

        <?php if ($column_youtube_embed !== '') { ?>
        <div class="sebu-article__video">
            <?php echo $column_youtube_embed; ?>
        </div>
        <?php } ?>

        <div class="sebu-article__body">
            <div class="sebu-article__content sebu-article__content--rich">
                <?php echo eottae_column_render_content($post['wr_content'] ?? ''); ?>
            </div>
        </div>

        <footer class="sebu-article__engage">
            <p class="sebu-article__engage-label">이 글이 도움이 되셨나요?</p>
            <div class="sebu-article__actions">
                <?php if ($is_member) { ?>
                <button type="button" class="sebu-column-like-btn<?php echo !empty($post['liked']) ? ' is-liked' : ''; ?>" data-sebu-column-like data-token="<?php echo get_text($token); ?>">
                    <span class="sebu-article__action-icon" aria-hidden="true">♥</span>
                    공감 <span data-sebu-column-like-count><?php echo number_format((int) ($post['like_count'] ?? 0)); ?></span>
                </button>
                <button type="button" class="sebu-column-bookmark-btn<?php echo !empty($post['bookmarked']) ? ' is-saved' : ''; ?>" data-sebu-column-bookmark data-token="<?php echo get_text($token); ?>">
                    <span class="sebu-article__action-icon" aria-hidden="true">☆</span>
                    <?php echo !empty($post['bookmarked']) ? '저장됨' : '저장하기'; ?>
                </button>
                <button type="button" class="sebu-column-btn sebu-column-btn--outline" data-sebu-column-share data-share-url="<?php echo get_text($post['view_url'] ?? ''); ?>" data-share-title="<?php echo get_text($post['wr_subject'] ?? ''); ?>">공유</button>
                <button type="button" class="sebu-column-btn sebu-column-btn--ghost" data-sebu-column-report-open>신고</button>
                <?php } else { ?>
                <span class="sebu-column-like-btn is-disabled">공감 <?php echo number_format((int) ($post['like_count'] ?? 0)); ?></span>
                <?php } ?>
            </div>
        </footer>
    </article>

    <?php if ($author) { ?>
    <section class="sebu-article-author-card" aria-labelledby="sebu-article-author-title">
        <h2 class="sebu-article-author-card__title" id="sebu-article-author-title">작성자 소개</h2>
        <?php echo eottae_column_author_card_html($author, 'article'); ?>
    </section>
    <?php } ?>

    <?php if (!empty($author_more)) { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-author-more-title">
        <h2 class="sebu-column-section__title" id="sebu-author-more-title">같은 작성자의 다른 컬럼</h2>
        <ul class="sebu-column-grid">
            <?php foreach ($author_more as $item) { ?>
            <li class="sebu-column-grid__item"><?php echo eottae_column_card_html($item, 'compact'); ?></li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <?php if (!empty($related)) { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-related-title">
        <h2 class="sebu-column-section__title" id="sebu-related-title">관련 컬럼</h2>
        <ul class="sebu-column-grid">
            <?php foreach (array_slice($related, 0, 3) as $item) { ?>
            <li class="sebu-column-grid__item"><?php echo eottae_column_card_html($item, 'compact'); ?></li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <section class="sebu-article-comments" aria-labelledby="sebu-comments-title">
        <h2 class="sebu-article-comments__title" id="sebu-comments-title">댓글 <?php echo number_format(count($comments)); ?></h2>
        <?php if ($is_member) { ?>
        <form class="sebu-article-comments__form" method="post" action="<?php echo $comment_action; ?>">
            <input type="hidden" name="w" value="c">
            <input type="hidden" name="bo_table" value="<?php echo get_text($bo_table); ?>">
            <input type="hidden" name="wr_id" value="<?php echo (int) $wr_id; ?>">
            <textarea name="wr_content" class="sebu-article-comments__input" rows="3" placeholder="댓글을 입력해 주세요" required></textarea>
            <button type="submit" class="sebu-column-btn sebu-column-btn--primary">댓글 등록</button>
        </form>
        <?php } ?>
        <ul class="sebu-article-comments__list">
            <?php foreach ($comments as $comment) { ?>
            <li class="sebu-article-comments__item">
                <strong><?php echo get_text($comment['wr_name'] ?? ''); ?></strong>
                <time datetime="<?php echo get_text($comment['wr_datetime'] ?? ''); ?>"><?php echo substr(get_text($comment['wr_datetime'] ?? ''), 0, 16); ?></time>
                <p><?php echo nl2br(get_text($comment['wr_content'] ?? '')); ?></p>
            </li>
            <?php } ?>
        </ul>
    </section>
</main>

<?php if ($is_member) { ?>
<div class="sebu-column-modal" id="sebuColumnReportModal" hidden>
    <div class="sebu-column-modal__backdrop" data-sebu-column-report-close></div>
    <div class="sebu-column-modal__panel" role="dialog" aria-labelledby="sebuColumnReportTitle">
        <h2 id="sebuColumnReportTitle" class="sebu-column-modal__title">컬럼 신고</h2>
        <form class="sebu-column-report-form" data-sebu-column-report-form data-proc-url="<?php echo get_text(eottae_column_proc_url()); ?>" data-token="<?php echo get_text($report_token); ?>" data-wr-id="<?php echo (int) $wr_id; ?>">
            <input type="hidden" name="action" value="report">
            <?php foreach ($report_reasons as $code => $label) { ?>
            <label class="sebu-column-report-form__option">
                <input type="radio" name="reason" value="<?php echo get_text($code); ?>" required> <?php echo get_text($label); ?>
            </label>
            <?php } ?>
            <textarea name="memo" class="sebu-column-report-form__memo" rows="3" placeholder="추가 설명 (선택)"></textarea>
            <div class="sebu-column-modal__actions">
                <button type="button" class="sebu-column-btn sebu-column-btn--ghost" data-sebu-column-report-close>취소</button>
                <button type="submit" class="sebu-column-btn sebu-column-btn--primary">신고하기</button>
            </div>
        </form>
    </div>
</div>
<?php } ?>

<?php
g5_page_end();
