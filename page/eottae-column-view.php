<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
include_once G5_LIB_PATH.'/eottae-column-bookmarks.lib.php';
include_once G5_LIB_PATH.'/eottae-column-report.lib.php';
include_once G5_PATH.'/components/eottae/column-card.php';
include_once G5_PATH.'/components/eottae/column-author-card.php';

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
$report_token = eottae_column_report_token();
$report_reasons = eottae_column_report_reasons();
$bo_table = eottae_column_board_table();
$comment_action = G5_BBS_URL.'/write_comment_update.php';

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
add_javascript('<script src="'.G5_JS_URL.'/eottae-column.js" defer></script>', 24);

g5_page_start(get_text($post['wr_subject'] ?? '컬럼'));
?>

<main class="sebu-article-page" data-sebu-column-view data-wr-id="<?php echo (int) $wr_id; ?>" data-proc-url="<?php echo get_text(eottae_column_proc_url()); ?>">
    <p class="sebu-article-page__back"><a href="<?php echo eottae_column_list_url(); ?>">← 생활정보 컬럼</a></p>

    <article class="sebu-article">
        <header class="sebu-article__header">
            <?php if (!empty($post['category_label'])) { ?>
            <span class="sebu-column-badge sebu-column-badge--category"><?php echo get_text($post['category_label']); ?></span>
            <?php } ?>
            <h1 class="sebu-article__title"><?php echo get_text($post['wr_subject'] ?? ''); ?></h1>
            <?php if (!empty($post['subtitle'])) { ?>
            <p class="sebu-article__subtitle"><?php echo get_text($post['subtitle']); ?></p>
            <?php } elseif (!empty($post['summary'])) { ?>
            <p class="sebu-article__subtitle"><?php echo get_text($post['summary']); ?></p>
            <?php } ?>

            <div class="sebu-article__author-bar">
                <?php if ($author) { ?>
                <a href="<?php echo get_text($author['profile_url'] ?? '#'); ?>" class="sebu-article__author-link">
                    <img src="<?php echo get_text($author['profile_image_url'] ?? ''); ?>" alt="" class="sebu-article__author-avatar" width="48" height="48">
                    <span class="sebu-article__author-name"><?php echo get_text($author['display_name'] ?? ''); ?></span>
                    <?php if (!empty($author['title'])) { ?>
                    <span class="sebu-article__author-title"><?php echo get_text($author['title']); ?></span>
                    <?php } ?>
                </a>
                <?php } else { ?>
                <span class="sebu-article__author-name"><?php echo get_text($post['author_name'] ?? ''); ?></span>
                <?php } ?>
            </div>

            <p class="sebu-article__meta">
                <?php echo get_text($post['date_label'] ?? ''); ?>
                <?php if (!empty($post['modified_label']) && $post['modified_label'] !== $post['date_label']) { ?>
                · 수정 <?php echo get_text($post['modified_label']); ?>
                <?php } ?>
                · 조회 <?php echo number_format((int) ($post['wr_hit'] ?? 0)); ?>
                · 댓글 <?php echo number_format((int) ($post['wr_comment'] ?? 0)); ?>
                · 공감 <?php echo number_format((int) ($post['like_count'] ?? 0)); ?>
                · <?php echo get_text($post['read_time_label'] ?? ''); ?>
            </p>

            <div class="sebu-article__share">
                <button type="button" class="sebu-column-btn sebu-column-btn--ghost" data-sebu-column-share data-share-url="<?php echo get_text($post['view_url'] ?? ''); ?>" data-share-title="<?php echo get_text($post['wr_subject'] ?? ''); ?>">공유</button>
            </div>
        </header>

        <?php if (!empty($post['thumbnail_url'])) { ?>
        <figure class="sebu-article__hero">
            <img src="<?php echo get_text($post['thumbnail_url']); ?>" alt="" class="sebu-article__hero-img">
        </figure>
        <?php } ?>

        <div class="sebu-article__content sebu-article__content--rich">
            <?php echo conv_content($post['wr_content'] ?? '', 1); ?>
        </div>

        <footer class="sebu-article__actions">
            <?php if ($is_member) { ?>
            <button type="button" class="sebu-column-like-btn<?php echo !empty($post['liked']) ? ' is-liked' : ''; ?>" data-sebu-column-like data-token="<?php echo get_text($token); ?>">
                공감 <span data-sebu-column-like-count><?php echo number_format((int) ($post['like_count'] ?? 0)); ?></span>
            </button>
            <button type="button" class="sebu-column-bookmark-btn<?php echo !empty($post['bookmarked']) ? ' is-saved' : ''; ?>" data-sebu-column-bookmark data-token="<?php echo get_text($token); ?>">
                <?php echo !empty($post['bookmarked']) ? '저장됨' : '저장하기'; ?>
            </button>
            <button type="button" class="sebu-column-btn sebu-column-btn--ghost" data-sebu-column-report-open>신고</button>
            <?php } else { ?>
            <span class="sebu-column-like-btn is-disabled">공감 <?php echo number_format((int) ($post['like_count'] ?? 0)); ?></span>
            <?php } ?>
        </footer>
    </article>

    <?php if ($author) { ?>
    <section class="sebu-article-author-card" aria-labelledby="sebu-article-author-title">
        <h2 class="sebu-article-author-card__title" id="sebu-article-author-title">작성자 소개</h2>
        <?php echo eottae_column_author_card_html($author); ?>
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
