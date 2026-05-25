<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_home_feed_html')) {
    function eottae_plaza_home_feed_html($limit = 5)
    {
        include_once G5_LIB_PATH.'/eottae-plaza.lib.php';

        $feed_context = eottae_plaza_home_feed_context($limit);
        $posts = $feed_context['posts'];
        $list_url = $feed_context['list_url'];
        $list_label = $feed_context['list_label'];

        ob_start();
        ?>
        <section class="plaza-home-feed" id="eottae-home-plaza-feed" aria-labelledby="plaza-home-feed-title">
            <div class="plaza-home-feed__inner">
                <header class="plaza-home-feed__head">
                    <h2 class="plaza-home-feed__title" id="plaza-home-feed-title">지금 세부광장에서는</h2>
                    <p class="plaza-home-feed__desc">세부광장에 올라온 최신 이야기</p>
                </header>

                <?php if (empty($posts)) { ?>
                <p class="plaza-home-feed__empty">아직 표시할 글이 없습니다. 세부광장에 첫 이야기를 남겨 보세요.</p>
                <?php } else { ?>
                <ul class="plaza-home-feed__list">
                    <?php foreach ($posts as $post) {
                        if (empty($post['href'])) {
                            continue;
                        }
                        ?>
                    <li class="plaza-home-feed__item<?php echo !empty($post['is_ai']) ? ' plaza-home-feed__item--ai is-plaza-ai-message' : ''; ?>">
                        <a href="<?php echo $post['href']; ?>" class="plaza-home-feed__link">
                            <span class="plaza-home-feed__line">
                                <?php if (!empty($post['is_ai'])) { ?>
                                <span class="plaza-home-feed__type plaza-ai-badge plaza-ai-badge--sm">🤖 AI질문</span>
                                <?php } elseif ($post['type_label'] !== '') {
                                    $badge_kind = isset($post['badge_kind']) ? (string) $post['badge_kind'] : 'plaza';
                                    $badge_base = $badge_kind === 'community' ? 'community-badge' : 'plaza-badge';
                                    ?>
                                <span class="plaza-home-feed__type <?php echo $badge_base; ?> <?php echo $post['type_class']; ?>"><?php echo $post['type_label']; ?></span>
                                <?php } ?>
                                <strong class="plaza-home-feed__subject"><?php echo $post['subject']; ?></strong>
                            </span>
                            <span class="plaza-home-feed__meta">
                                <?php if ($post['comment_count'] > 0) { ?>
                                <span>댓글 <?php echo number_format($post['comment_count']); ?></span>
                                <?php } ?>
                                <?php if ($post['like_count'] > 0) { ?>
                                <span>공감 <?php echo number_format($post['like_count']); ?></span>
                                <?php } ?>
                                <?php if ($post['time_label'] !== '') { ?>
                                <span><?php echo $post['time_label']; ?></span>
                                <?php } ?>
                            </span>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>

                <div class="plaza-home-feed__footer">
                    <a href="<?php echo $list_url; ?>" class="plaza-home-feed__more"><?php echo get_text($list_label); ?></a>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
