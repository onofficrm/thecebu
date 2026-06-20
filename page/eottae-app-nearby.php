<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-app-home.lib.php';

$g5['body_script'] = ' class="eottae-app-home-shell"';

$category = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
$category = preg_replace('/[^\pL\pN _-]/u', '', $category);
$categories = array('전체', '맛집', '카페', '병원', '마트', '뷰티', '여행');
$shops = eottae_app_latest_shop_cards(12, $category === '전체' ? '' : $category);
$shop_table = function_exists('eottae_shop_table') ? eottae_shop_table() : (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop');
$board_url = function_exists('eottae_shop_list_url') ? eottae_shop_list_url() : G5_BBS_URL.'/board.php?bo_table='.$shop_table;

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-app-home.css">', 24);

g5_page_start('내주변');
?>
<main class="eottae-app-home eottae-app-nearby-page">
    <header class="eottae-app-top">
        <a href="<?php echo G5_URL; ?>/page/eottae-app-home.php" class="eottae-app-top__logo">내주변</a>
        <div class="eottae-app-top__actions">
            <a href="<?php echo $board_url; ?>" class="eottae-app-top__login">목록</a>
        </div>
    </header>

    <section class="eottae-app-banner eottae-app-banner--nearby">
        <div>
            <span>Cebu Nearby</span>
            <strong>전화·길찾기까지 바로 쓰는 업체 카드</strong>
            <p>앱에서는 자주 찾는 업체를 빠르게 확인하고 바로 연락할 수 있게 구성했습니다.</p>
        </div>
        <a href="<?php echo $board_url; ?>">전체 업체</a>
    </section>

    <nav class="eottae-app-filter" aria-label="업체 카테고리">
        <?php foreach ($categories as $label) {
            $active = ($category === '' && $label === '전체') || $category === $label;
            $href = $label === '전체'
                ? G5_URL.'/page/eottae-app-nearby.php'
                : G5_URL.'/page/eottae-app-nearby.php?category='.rawurlencode($label);
            ?>
        <a href="<?php echo $href; ?>" class="<?php echo $active ? 'is-active' : ''; ?>"><?php echo get_text($label); ?></a>
        <?php } ?>
    </nav>

    <section class="eottae-app-section" aria-labelledby="nearby-list-title">
        <div class="eottae-app-section__head">
            <h2 id="nearby-list-title"><?php echo $category !== '' ? get_text($category) : '추천 업체'; ?></h2>
            <a href="<?php echo $board_url; ?>">게시판 보기</a>
        </div>
        <div class="eottae-app-shop-list eottae-app-shop-list--large">
            <?php if (empty($shops)) { ?>
            <p class="eottae-app-empty">표시할 업체가 없습니다.</p>
            <?php } else { foreach ($shops as $shop) { ?>
            <article class="eottae-app-shop-card">
                <a href="<?php echo get_text($shop['href'] ?? '#'); ?>" class="eottae-app-shop-card__main">
                    <span class="eottae-app-shop-card__thumb"<?php echo !empty($shop['thumb']) ? ' style="background-image:url('.get_text($shop['thumb']).')"' : ''; ?>></span>
                    <span class="eottae-app-shop-card__body">
                        <em><?php echo get_text($shop['category'] ?: '업체'); ?><?php echo !empty($shop['status']) ? ' · '.get_text($shop['status']) : ''; ?></em>
                        <strong><?php echo get_text($shop['title']); ?></strong>
                        <small><?php echo get_text($shop['region'] ?: $shop['address']); ?></small>
                    </span>
                </a>
                <div class="eottae-app-shop-card__actions">
                    <a href="<?php echo get_text($shop['phone_href'] ?? '#'); ?>">전화</a>
                    <a href="<?php echo get_text($shop['map_href'] ?? '#'); ?>" target="_blank" rel="noopener noreferrer">길찾기</a>
                </div>
            </article>
            <?php }} ?>
        </div>
    </section>
</main>
<?php
g5_page_end();
