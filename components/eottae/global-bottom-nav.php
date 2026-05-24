<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_global_bottom_nav_html')) {
    function eottae_global_bottom_nav_html()
    {
        $home = G5_URL.'/';
        $nearby = G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE;
        $board = G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE;
        $mypage = eottae_mypage_url();

        ob_start();
        ?>
        <nav class="mobile-bottom-nav mobile-bottom-nav--global" aria-label="하단 메뉴">
            <a href="<?php echo $home; ?>" class="mobile-bottom-nav__item">
                <span class="mobile-bottom-nav__icon" aria-hidden="true">🏠</span>
                홈
            </a>
            <a href="<?php echo $nearby; ?>" class="mobile-bottom-nav__item">
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
        <?php

        return ob_get_clean();
    }
}
