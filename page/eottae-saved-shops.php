<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-saved-shops.php'));
}

$tab = isset($_GET['tab']) ? preg_replace('/[^a-z]/', '', $_GET['tab']) : 'saved';
if (!in_array($tab, array('saved', 'recent'), true)) {
    $tab = 'saved';
}

$saved_rows = eottae_get_shop_rows_by_ids(eottae_get_saved_shop_ids($member['mb_id'], 30));
$recent_rows = eottae_get_shop_rows_by_ids(eottae_get_recent_shop_ids(20));

g5_page_start('저장·최근 본 업체');
?>

<main class="mypage-subpage">
    <?php eottae_render_mypage_back(); ?>
    <h1 class="mypage-subpage__title">저장·최근 본 업체</h1>

    <nav class="mypage-tabs" aria-label="업체 목록 탭">
        <a href="<?php echo G5_URL; ?>/page/eottae-saved-shops.php?tab=saved" class="mypage-tabs__item<?php echo $tab === 'saved' ? ' is-active' : ''; ?>">
            찜한 업체 (<?php echo count($saved_rows); ?>)
        </a>
        <a href="<?php echo G5_URL; ?>/page/eottae-saved-shops.php?tab=recent" class="mypage-tabs__item<?php echo $tab === 'recent' ? ' is-active' : ''; ?>">
            최근 본 업체 (<?php echo count($recent_rows); ?>)
        </a>
    </nav>

    <?php if ($tab === 'saved') { ?>
        <?php if (empty($saved_rows)) { ?>
        <div class="empty-state">
            <p class="empty-state__title">찜한 업체가 없습니다</p>
            <p><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>">내주변 업소</a>에서 ♥ 찜하기를 눌러 보세요.</p>
        </div>
        <?php } else { ?>
        <div class="shop-list-page shop-list-page--compact">
            <?php foreach ($saved_rows as $row) {
                eottae_render_shop_card($row, EOTTae_SHOP_TABLE);
            } ?>
        </div>
        <?php } ?>
    <?php } else { ?>
        <?php if (empty($recent_rows)) { ?>
        <div class="empty-state">
            <p class="empty-state__title">최근 본 업체가 없습니다</p>
            <p>업소 상세를 방문하면 이곳에 표시됩니다.</p>
        </div>
        <?php } else { ?>
        <div class="shop-list-page shop-list-page--compact">
            <?php foreach ($recent_rows as $row) {
                eottae_render_shop_card($row, EOTTae_SHOP_TABLE);
            } ?>
        </div>
        <?php } ?>
    <?php } ?>
</main>

<?php
g5_page_end();
