<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_home_community_dual_html')) {
    function eottae_home_community_dual_html($plaza_limit = 5, $chat_limit = 20)
    {
        if (!function_exists('eottae_plaza_home_feed_html')) {
            include_once G5_PATH.'/components/eottae/plaza-home-feed.php';
        }
        if (!function_exists('eottae_public_group_chat_html')) {
            include_once G5_PATH.'/components/eottae/public-group-chat.php';
        }

        $plaza_html = eottae_plaza_home_feed_html($plaza_limit);
        $chat_html = eottae_public_group_chat_html($chat_limit);

        ob_start();
        ?>
        <section class="home-community-dual" id="eottae-home-community-dual" aria-label="세부광장과 공개 단체톡">
            <div class="home-community-dual__grid">
                <div class="home-community-dual__col home-community-dual__col--plaza">
                    <?php echo $plaza_html; ?>
                </div>
                <div class="home-community-dual__col home-community-dual__col--chat">
                    <?php echo $chat_html; ?>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
