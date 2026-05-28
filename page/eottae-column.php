<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
include_once G5_PATH.'/components/eottae/column-card.php';
include_once G5_PATH.'/components/eottae/column-author-card.php';

eottae_column_ensure_schema();
eottae_column_ensure_badges();

$category = isset($_GET['category']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['category']) : '';
$sort = isset($_GET['sort']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['sort']) : '';
$member_mb_id = $is_member ? ($member['mb_id'] ?? '') : '';

$featured = eottae_column_list(array('limit' => 1, 'featured_only' => true, 'member_mb_id' => $member_mb_id));
$today_pick = !empty($featured[0]) ? $featured[0] : null;
if (!$today_pick) {
    $rec = eottae_column_list(array('limit' => 1, 'recommended_only' => true, 'member_mb_id' => $member_mb_id));
    $today_pick = !empty($rec[0]) ? $rec[0] : null;
}

$latest = eottae_column_list(array('limit' => 6, 'category' => $category, 'member_mb_id' => $member_mb_id));
$popular = eottae_column_list(array('limit' => 6, 'category' => $category, 'sort' => 'popular', 'member_mb_id' => $member_mb_id));
$week_top = eottae_column_list(array('limit' => 5, 'sort' => 'popular', 'member_mb_id' => $member_mb_id));
$authors = eottae_column_list_authors(array('limit' => 6));
$monthly = eottae_column_get_monthly_columnist();
$categories = eottae_column_category_options();

add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap">', 20);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);

g5_page_start(eottae_column_menu_label());
?>

<main class="sebu-column-page sebu-column-page--list sebu-column-editorial">
    <header class="sebu-column-masthead">
        <p class="sebu-column-masthead__eyebrow">Cebu Living · Column</p>
        <h1 class="sebu-column-masthead__title"><?php echo eottae_column_menu_label(); ?></h1>
        <p class="sebu-column-masthead__deck">세부에 살아가는 사람들의 경험과 통찰.<br class="sebu-column-masthead__br">병원·학교·비자·가족생활까지, 검증된 생활정보를 깊이 있게 읽습니다.</p>
        <div class="sebu-column-masthead__actions">
            <?php if ($is_member && eottae_column_can_write($member['mb_id'], $is_admin === 'super')) { ?>
            <a href="<?php echo eottae_column_write_url(); ?>" class="sebu-column-btn sebu-column-btn--editorial">컬럼 작성</a>
            <?php } elseif ($is_member) { ?>
            <a href="<?php echo eottae_column_apply_url(); ?>" class="sebu-column-btn sebu-column-btn--outline">칼럼니스트 신청</a>
            <?php } ?>
            <a href="<?php echo eottae_columnist_recruit_url(); ?>" class="sebu-column-btn sebu-column-btn--ghost">컬럼리스트 모집</a>
        </div>
    </header>

    <nav class="sebu-column-filter sebu-column-filter--editorial" aria-label="컬럼 카테고리">
        <div class="sebu-column-filter__scroll">
            <a href="<?php echo eottae_column_list_url(); ?>" class="sebu-column-filter__chip<?php echo $category === '' ? ' is-active' : ''; ?>">전체</a>
            <?php foreach ($categories as $code => $label) { ?>
            <a href="<?php echo eottae_column_category_url($code); ?>" class="sebu-column-filter__chip<?php echo $category === $code ? ' is-active' : ''; ?>"><?php echo get_text($label); ?></a>
            <?php } ?>
        </div>
    </nav>

    <?php if ($today_pick && $category === '') { ?>
    <section class="sebu-column-section sebu-column-section--spotlight" aria-labelledby="sebu-column-today-title">
        <div class="sebu-column-section__label-row">
            <span class="sebu-column-section__label">Editor's Pick</span>
            <h2 class="sebu-column-section__title" id="sebu-column-today-title">오늘의 추천 컬럼</h2>
        </div>
        <?php echo eottae_column_card_html($today_pick, 'featured'); ?>
    </section>
    <?php } ?>

    <?php if ($monthly && $category === '') { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-column-monthly-title">
        <h2 class="sebu-column-section__title" id="sebu-column-monthly-title">이달의 칼럼니스트</h2>
        <?php echo eottae_column_monthly_card_html($monthly); ?>
    </section>
    <?php } ?>

    <section class="sebu-column-section sebu-column-section--feed" aria-labelledby="sebu-column-latest-title">
        <div class="sebu-column-section__head">
            <div class="sebu-column-section__label-row">
                <span class="sebu-column-section__label">Latest Stories</span>
                <h2 class="sebu-column-section__title" id="sebu-column-latest-title">최신 컬럼</h2>
            </div>
            <div class="sebu-column-section__sort">
                <a href="<?php echo eottae_column_list_url(array_filter(array('category' => $category ?: null))); ?>" class="sebu-column-sort-btn<?php echo $sort === '' ? ' is-active' : ''; ?>">최신</a>
                <a href="<?php echo eottae_column_list_url(array_filter(array('category' => $category ?: null, 'sort' => 'popular'))); ?>" class="sebu-column-sort-btn<?php echo $sort === 'popular' ? ' is-active' : ''; ?>">인기</a>
            </div>
        </div>
        <?php
        $display = ($sort === 'popular') ? $popular : $latest;
        if (empty($display)) {
            echo '<p class="sebu-column-empty">표시할 컬럼이 없습니다.</p>';
        } else {
            echo '<ul class="sebu-column-grid">';
            foreach ($display as $post) {
                echo '<li class="sebu-column-grid__item">'.eottae_column_card_html($post).'</li>';
            }
            echo '</ul>';
        }
        ?>
    </section>

    <?php if ($category === '' && !empty($popular)) { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-column-popular-title">
        <h2 class="sebu-column-section__title" id="sebu-column-popular-title">인기 컬럼</h2>
        <ul class="sebu-column-grid">
            <?php foreach ($popular as $post) { ?>
            <li class="sebu-column-grid__item"><?php echo eottae_column_card_html($post); ?></li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <?php if ($category === '' && !empty($week_top)) { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-column-week-title">
        <h2 class="sebu-column-section__title" id="sebu-column-week-title">이번 주 많이 읽은 컬럼</h2>
        <ol class="sebu-column-rank">
            <?php foreach ($week_top as $i => $post) { ?>
            <li class="sebu-column-rank__item">
                <span class="sebu-column-rank__num"><?php echo $i + 1; ?></span>
                <?php echo eottae_column_card_html($post, 'compact'); ?>
            </li>
            <?php } ?>
        </ol>
    </section>
    <?php } ?>

    <?php if ($category === '' && !empty($authors)) { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-column-authors-title">
        <h2 class="sebu-column-section__title" id="sebu-column-authors-title">주목받는 칼럼니스트</h2>
        <ul class="sebu-column-authors">
            <?php foreach ($authors as $author) { ?>
            <li class="sebu-column-authors__item"><?php echo eottae_column_author_card_html($author); ?></li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>
</main>

<?php
g5_page_end();
