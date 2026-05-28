<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_global_bottom_nav_html')) {
    function eottae_global_bottom_nav_html()
    {
        $home = G5_URL.'/';
        $shop_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        $nearby = G5_BBS_URL.'/board.php?bo_table='.$shop_table;
        $board = G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE;
        $mypage = eottae_mypage_url();
        $near_nav_js = G5_JS_URL.'/eottae-mobile-near-nav.js';
        $near_nav_path = G5_PATH.'/js/eottae-mobile-near-nav.js';
        if (is_file($near_nav_path)) {
            $near_nav_js .= '?ver='.(int) filemtime($near_nav_path);
        }

        ob_start();
        ?>
        <nav class="mobile-bottom-nav mobile-bottom-nav--global" aria-label="하단 메뉴" data-eottae-shop-list-url="<?php echo htmlspecialchars($nearby, ENT_QUOTES, 'UTF-8'); ?>">
            <a href="<?php echo $home; ?>" class="mobile-bottom-nav__item">
                <span class="mobile-bottom-nav__icon" aria-hidden="true">🏠</span>
                홈
            </a>
            <a href="<?php echo $nearby; ?>" class="mobile-bottom-nav__item" data-eottae-mobile-near="1">
                <span class="mobile-bottom-nav__icon" aria-hidden="true">📍</span>
                내주변
            </a>
            <a href="<?php echo $board; ?>" class="mobile-bottom-nav__item">
                <span class="mobile-bottom-nav__icon" aria-hidden="true">💬</span>
                커뮤니티
            </a>
            <a href="<?php echo $mypage; ?>" class="mobile-bottom-nav__item">
                <span class="mobile-bottom-nav__icon" aria-hidden="true">👤</span>
                MY
            </a>
        </nav>
        <script src="<?php echo htmlspecialchars($near_nav_js, ENT_QUOTES, 'UTF-8'); ?>" defer></script>
        <?php

        return ob_get_clean();
    }
}
