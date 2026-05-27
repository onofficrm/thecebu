<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', function_exists('eottae_login_url') ? eottae_login_url(eottae_column_mypage_url()) : G5_BBS_URL.'/login.php');
}

include_once G5_LIB_PATH.'/eottae-column.lib.php';
include_once G5_LIB_PATH.'/eottae-column-bookmarks.lib.php';
include_once G5_PATH.'/components/eottae/column-card.php';
include_once G5_PATH.'/components/eottae/column-mypage.php';

eottae_column_ensure_schema();

$bookmarks = eottae_column_list_bookmarks($member['mb_id'], 20);
$is_columnist = eottae_column_is_columnist($member['mb_id']);

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);

g5_page_start('내 컬럼');
?>

<main class="sebu-column-mypage-page">
    <?php echo eottae_column_mypage_section_html($member, $is_columnist); ?>

    <?php if (!empty($bookmarks)) { ?>
    <section class="sebu-column-mypage" aria-labelledby="sebu-column-saved-title">
        <h2 class="sebu-column-mypage__title" id="sebu-column-saved-title">저장한 컬럼</h2>
        <ul class="sebu-column-mypage__list">
            <?php foreach ($bookmarks as $post) { ?>
            <li><?php echo eottae_column_card_html($post, 'compact'); ?></li>
            <?php } ?>
        </ul>
    </section>
    <?php } ?>
</main>

<?php
g5_page_end();
