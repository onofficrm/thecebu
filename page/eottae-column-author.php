<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-likes.lib.php';
include_once G5_PATH.'/components/eottae/column-card.php';
include_once G5_PATH.'/components/eottae/column-author-card.php';
include_once G5_PATH.'/components/eottae/column-author-profile.php';

$mb_id = isset($_GET['mb_id']) ? trim((string) $_GET['mb_id']) : '';
if ($mb_id === '') {
    alert('칼럼니스트를 찾을 수 없습니다.', eottae_column_list_url());
}

$author = eottae_column_get_author($mb_id);
if (!$author || empty($author['is_visible'])) {
    alert('칼럼니스트 프로필을 찾을 수 없습니다.', eottae_column_list_url());
}

$sort = isset($_GET['sort']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['sort']) : 'latest';
$member_mb_id = $is_member ? ($member['mb_id'] ?? '') : '';

$columns = eottae_column_list(array(
    'mb_id'  => $mb_id,
    'sort'   => $sort === 'popular' ? 'popular' : 'latest',
    'limit'  => 20,
    'member_mb_id' => $member_mb_id,
));

$representative = array_values(array_filter($columns, function ($item) {
    return !empty($item['is_representative']);
}));
if (empty($representative)) {
    $representative = eottae_column_list(array(
        'mb_id' => $mb_id,
        'sort'  => 'popular',
        'limit' => 3,
        'member_mb_id' => $member_mb_id,
    ));
} else {
    $representative = array_slice($representative, 0, 3);
}

$stats = $author['stats'] ?? array();
$specialties = array_filter(array_map('trim', explode(',', (string) ($author['specialty'] ?? ''))));
$primary_specialty = !empty($specialties) ? $specialties[0] : '';
$author_meta = array();
if (!empty($author['area_label'])) {
    $author_meta[] = array('label' => '활동 지역', 'value' => $author['area_label']);
}
if (!empty($primary_specialty)) {
    $author_meta[] = array('label' => '대표 분야', 'value' => $primary_specialty);
}
if (!empty($author['title'])) {
    $author_meta[] = array('label' => '소개', 'value' => $author['title']);
}

add_stylesheet('<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap">', 20);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);

g5_page_start(get_text($author['display_name'] ?? '').' · 칼럼니스트');
?>

<main class="sebu-writer-page sebu-column-editorial">
    <p class="sebu-writer-page__back"><a href="<?php echo eottae_column_list_url(); ?>">← <?php echo eottae_column_menu_label(); ?></a></p>

    <header class="sebu-writer-page__profile">
        <div class="sebu-writer-page__profile-visual" aria-hidden="true">
            <?php echo eottae_column_render_avatar_html($author, 'lg', 'sebu-writer-page__avatar'); ?>
            <span class="sebu-writer-page__avatar-ring"></span>
        </div>
        <div class="sebu-writer-page__intro">
            <p class="sebu-writer-page__eyebrow">Columnist Profile</p>
            <div class="sebu-writer-page__headline">
                <?php echo eottae_column_render_author_profile_info_badges_html($author, 'sebu-writer-page__info-badges'); ?>
                <?php echo eottae_column_render_author_growth_badges_html($mb_id, eottae_column_author_profile_info_labels($author)); ?>
            </div>
            <h1 class="sebu-writer-page__name"><?php echo get_text($author['display_name'] ?? ''); ?></h1>
            <?php if (!empty($author['title'])) { ?>
            <p class="sebu-writer-page__title"><?php echo get_text($author['title']); ?></p>
            <?php } ?>
            <?php if (!empty($author['bio'])) { ?>
            <p class="sebu-writer-page__bio"><?php echo nl2br(get_text($author['bio'])); ?></p>
            <?php } ?>
            <?php if (!empty($author_meta)) { ?>
            <dl class="sebu-writer-page__meta">
                <?php foreach ($author_meta as $meta) { ?>
                <div>
                    <dt><?php echo get_text($meta['label']); ?></dt>
                    <dd><?php echo get_text($meta['value']); ?></dd>
                </div>
                <?php } ?>
            </dl>
            <?php } ?>
            <div class="sebu-writer-page__links">
                <?php echo eottae_column_render_author_profile_link_badges_html($author, 'sebu-writer-page__social'); ?>
            </div>
        </div>
    </header>

    <section class="sebu-writer-page__stats" aria-label="활동 지표">
        <div class="sebu-writer-stat"><span class="sebu-writer-stat__value"><?php echo number_format((int) ($stats['column_count'] ?? 0)); ?></span><span class="sebu-writer-stat__label">작성 컬럼</span></div>
        <div class="sebu-writer-stat"><span class="sebu-writer-stat__value"><?php echo number_format((int) ($stats['total_views'] ?? 0)); ?></span><span class="sebu-writer-stat__label">누적 조회</span></div>
        <div class="sebu-writer-stat"><span class="sebu-writer-stat__value"><?php echo number_format((int) ($stats['total_likes'] ?? 0)); ?></span><span class="sebu-writer-stat__label">누적 공감</span></div>
        <div class="sebu-writer-stat"><span class="sebu-writer-stat__value"><?php echo number_format((int) ($stats['total_comments'] ?? 0)); ?></span><span class="sebu-writer-stat__label">댓글</span></div>
    </section>

    <?php if (!empty($representative)) { ?>
    <section class="sebu-column-section" aria-labelledby="sebu-writer-rep-title">
        <h2 class="sebu-column-section__title" id="sebu-writer-rep-title">대표 컬럼</h2>
        <ul class="sebu-column-grid">
            <?php foreach ($representative as $post) { ?>
            <li class="sebu-column-grid__item"><?php echo eottae_column_card_html($post); ?></li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>

    <section class="sebu-column-section" aria-labelledby="sebu-writer-columns-title">
        <div class="sebu-column-section__head">
            <h2 class="sebu-column-section__title" id="sebu-writer-columns-title">작성한 컬럼</h2>
            <div class="sebu-column-section__sort">
                <a href="<?php echo eottae_column_author_url($mb_id); ?>" class="sebu-column-sort-btn<?php echo $sort !== 'popular' ? ' is-active' : ''; ?>">최신순</a>
                <a href="<?php echo eottae_column_author_url($mb_id).'?sort=popular'; ?>" class="sebu-column-sort-btn<?php echo $sort === 'popular' ? ' is-active' : ''; ?>">인기순</a>
            </div>
        </div>
        <?php if (empty($columns)) { ?>
        <p class="sebu-column-empty">아직 발행된 컬럼이 없습니다.</p>
        <?php } else { ?>
        <ul class="sebu-column-grid">
            <?php foreach ($columns as $post) { ?>
            <li class="sebu-column-grid__item"><?php echo eottae_column_card_html($post); ?></li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>
</main>

<?php
g5_page_end();
