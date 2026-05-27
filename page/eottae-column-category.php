<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
include_once G5_PATH.'/components/eottae/column-card.php';
include_once G5_PATH.'/components/eottae/column-author-card.php';

global $is_member, $member;

eottae_column_ensure_schema();

$category = isset($_GET['category']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['category']) : '';
$categories = eottae_column_category_options();
if ($category === '' || !isset($categories[$category])) {
    alert('카테고리를 찾을 수 없습니다.', eottae_column_list_url());
}

$sort = isset($_GET['sort']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['sort']) : '';
$member_mb_id = $is_member ? ($member['mb_id'] ?? '') : '';
$category_label = $categories[$category];
$category_desc = eottae_column_category_description($category);
$posts = eottae_column_list(array(
    'category' => $category,
    'sort' => $sort === 'popular' ? 'popular' : 'latest',
    'limit' => 18,
    'member_mb_id' => $member_mb_id,
));
$featured = eottae_column_list(array(
    'category' => $category,
    'featured_only' => true,
    'limit' => 1,
    'member_mb_id' => $member_mb_id,
));
$popular = eottae_column_list(array(
    'category' => $category,
    'sort' => 'popular',
    'limit' => 5,
    'member_mb_id' => $member_mb_id,
));

global $page_title, $page_description, $page_canonical;
$page_title = $category_label.' | 세부 생활정보 컬럼';
$page_description = $category_desc;
$page_canonical = eottae_column_category_url($category);

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);

g5_page_start($category_label.' 컬럼');
?>

<main class="sebu-column-page sebu-column-category-page">
    <header class="sebu-column-page__hero sebu-column-category-hero">
        <p class="sebu-column-page__back"><a href="<?php echo eottae_column_list_url(); ?>">← 전체 컬럼</a></p>
        <span class="sebu-column-badge sebu-column-badge--category"><?php echo get_text($category_label); ?></span>
        <h1 class="sebu-column-page__title"><?php echo get_text($category_label); ?> 컬럼</h1>
        <p class="sebu-column-page__intro"><?php echo get_text($category_desc); ?></p>
    </header>

    <nav class="sebu-column-filter" aria-label="컬럼 카테고리">
        <div class="sebu-column-filter__scroll">
            <a href="<?php echo eottae_column_list_url(); ?>" class="sebu-column-filter__chip">전체</a>
            <?php foreach ($categories as $code => $label) { ?>
            <a href="<?php echo eottae_column_category_url($code); ?>" class="sebu-column-filter__chip<?php echo $category === $code ? ' is-active' : ''; ?>"><?php echo get_text($label); ?></a>
            <?php } ?>
        </div>
    </nav>

    <?php if (!empty($featured[0])) { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-column-category-pick-title">
        <h2 class="sebu-column-section__title" id="sebu-column-category-pick-title"><?php echo get_text($category_label); ?> 추천 컬럼</h2>
        <?php echo eottae_column_card_html($featured[0], 'featured'); ?>
    </section>
    <?php } ?>

    <section class="sebu-column-section" aria-labelledby="sebu-column-category-list-title">
        <div class="sebu-column-section__head">
            <h2 class="sebu-column-section__title" id="sebu-column-category-list-title"><?php echo get_text($category_label); ?> 최신 글</h2>
            <div class="sebu-column-section__sort">
                <a href="<?php echo eottae_column_category_url($category); ?>" class="sebu-column-sort-btn<?php echo $sort !== 'popular' ? ' is-active' : ''; ?>">최신</a>
                <a href="<?php echo eottae_column_category_url($category, array('sort' => 'popular')); ?>" class="sebu-column-sort-btn<?php echo $sort === 'popular' ? ' is-active' : ''; ?>">인기</a>
            </div>
        </div>
        <?php if (empty($posts)) { ?>
        <p class="sebu-column-empty">아직 이 카테고리에 발행된 컬럼이 없습니다.</p>
        <?php } else { ?>
        <ul class="sebu-column-grid">
            <?php foreach ($posts as $post) { ?>
            <li class="sebu-column-grid__item"><?php echo eottae_column_card_html($post); ?></li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <?php if (!empty($popular)) { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-column-category-popular-title">
        <h2 class="sebu-column-section__title" id="sebu-column-category-popular-title"><?php echo get_text($category_label); ?> 많이 읽은 컬럼</h2>
        <ol class="sebu-column-rank">
            <?php foreach ($popular as $i => $post) { ?>
            <li class="sebu-column-rank__item">
                <span class="sebu-column-rank__num"><?php echo $i + 1; ?></span>
                <?php echo eottae_column_card_html($post, 'compact'); ?>
            </li>
            <?php } ?>
        </ol>
    </section>
    <?php } ?>
</main>

<?php
g5_page_end();
