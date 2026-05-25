<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_home_feed_html')) {
    /**
     * @param int $limit
     */
    function eottae_talkroom_home_feed_html($limit = 8)
    {
        if (!function_exists('eottae_talkroom_list_home_feed')) {
            include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
        }
        include_once G5_PATH.'/components/eottae/talk-ai-message-ui.php';

        $posts = eottae_talkroom_list_home_feed($limit);
        $list_url = function_exists('eottae_talkroom_list_url') ? eottae_talkroom_list_url() : G5_URL.'/talk';

        ob_start();
        ?>
        <section class="talk-home-feed" id="eottae-home-talk-feed" aria-labelledby="talk-home-feed-title">
            <div class="talk-home-feed__inner">
                <header class="talk-home-feed__head">
                    <h2 class="talk-home-feed__title" id="talk-home-feed-title">오늘의 세부톡</h2>
                    <p class="talk-home-feed__desc">세부톡방에서 올라온 최신 이야기</p>
                </header>

                <?php if (empty($posts)) { ?>
                <p class="talk-home-feed__empty">아직 표시할 게시글이 없습니다. 세부톡방에서 첫 글을 남겨 보세요.</p>
                <?php } else { ?>
                <ul class="talk-home-feed__list">
                    <?php foreach ($posts as $post) {
                        if (empty($post['href'])) {
                            continue;
                        }
                        $is_ai_post = !empty($post['is_ai']);
                        ?>
                    <li class="talk-home-feed__item<?php echo $is_ai_post ? ' talk-home-feed__item--ai is-talk-ai-message' : ''; ?>">
                        <a href="<?php echo $post['href']; ?>" class="talk-home-feed__link">
                            <span class="talk-home-feed__line">
                                <span class="talk-home-feed__room"><?php echo $post['room_name']; ?></span>
                                <?php if ($is_ai_post) { ?>
                                <?php echo eottae_talkroom_ai_message_render_badge($post, 'sm'); ?>
                                <?php } else { ?>
                                <span class="talk-home-feed__type community-badge <?php echo $post['type_class']; ?>"><?php echo $post['type_label']; ?></span>
                                <?php } ?>
                                <strong class="talk-home-feed__subject<?php echo $is_ai_post ? ' talk-ai-msg__title' : ''; ?>"><?php echo $post['subject']; ?></strong>
                            </span>
                            <span class="talk-home-feed__meta">
                                <?php if ($is_ai_post) { ?>
                                <span class="talk-home-feed__author talk-ai-msg__author-line"><?php echo $post['ai_display_name'] ?? eottae_talkroom_ai_message_display_name($post); ?></span>
                                <?php } else { ?>
                                <span class="talk-home-feed__author"><?php echo $post['author']; ?></span>
                                <?php } ?>
                                <?php if ($post['comment_count'] > 0) { ?>
                                <span class="talk-home-feed__comments">댓글 <?php echo number_format($post['comment_count']); ?></span>
                                <?php } ?>
                                <?php if ($post['time_label'] !== '') { ?>
                                <span class="talk-home-feed__time"><?php echo $post['time_label']; ?></span>
                                <?php } ?>
                            </span>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>

                <div class="talk-home-feed__footer">
                    <a href="<?php echo $list_url; ?>" class="talk-home-feed__more">더보기</a>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
