<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_golf_join_card_html')) {
    /**
     * @param array<string, mixed> $post
     */
    function eottae_golf_join_card_html(array $post)
    {
        $detail_url = get_text($post['detail_url'] ?? '#');
        $shop_url = get_text($post['shop_detail_url'] ?? '');
        $thumb_url = get_text($post['thumb_url'] ?? '');
        $status_class = get_text($post['status_class'] ?? 'recruiting');
        $course_name = get_text($post['golf_course_name'] ?? '');
        $course_href = $shop_url !== '' ? $shop_url : $detail_url;
        $thumb_href = $shop_url !== '' ? $shop_url : $detail_url;
        $tags = array_merge(
            (array) ($post['member_condition_tags'] ?? array()),
            (array) ($post['mood_tags'] ?? array())
        );
        $tags = array_slice(array_values(array_unique(array_filter($tags))), 0, 6);

        ob_start();
        ?>
        <li class="golf-join-card golf-join-card--<?php echo $status_class; ?>">
            <div class="golf-join-card__inner">
                <?php if ($thumb_url !== '') { ?>
                <a href="<?php echo $thumb_href; ?>" class="golf-join-card__thumb"<?php echo $shop_url !== '' ? ' aria-label="'.get_text($course_name).' 업체정보 보기"' : ''; ?>>
                    <img src="<?php echo $thumb_url; ?>" alt="" loading="lazy" decoding="async">
                </a>
                <?php } else { ?>
                <a href="<?php echo $thumb_href; ?>" class="golf-join-card__thumb golf-join-card__thumb--empty" aria-hidden="true">
                    <span>⛳</span>
                </a>
                <?php } ?>

                <div class="golf-join-card__body">
                    <div class="golf-join-card__top">
                        <span class="golf-join-card__status golf-join-card__status--<?php echo $status_class; ?>">
                            <?php echo get_text($post['status_label'] ?? ''); ?>
                        </span>
                        <?php if (!empty($post['venue_type']) && ($post['venue_type'] ?? '') === 'screen_golf') { ?>
                        <span class="golf-join-card__badge golf-join-card__badge--venue">스크린골프</span>
                        <?php } ?>
                        <?php if (!empty($post['is_tee_time_unknown'])) { ?>
                        <span class="golf-join-card__badge">미정</span>
                        <?php } ?>
                    </div>

                    <a href="<?php echo $course_href; ?>" class="golf-join-card__course<?php echo $shop_url !== '' ? ' golf-join-card__course--shop' : ''; ?>">
                        <?php echo $course_name; ?>
                        <?php if ($shop_url !== '') { ?>
                        <span class="golf-join-card__course-hint">업체정보</span>
                        <?php } ?>
                    </a>

                    <a href="<?php echo $detail_url; ?>" class="golf-join-card__detail">
                        <p class="golf-join-card__meta">
                            <span><?php echo get_text($post['region_label'] ?? ''); ?></span>
                            <span class="golf-join-card__dot" aria-hidden="true">·</span>
                            <span><?php echo get_text($post['round_date_label'] ?? ''); ?></span>
                            <span class="golf-join-card__dot" aria-hidden="true">·</span>
                            <span><?php echo get_text($post['tee_time_label'] ?? ''); ?></span>
                        </p>

                        <div class="golf-join-card__row">
                            <span class="golf-join-card__price"><?php echo get_text($post['price_label'] ?? ''); ?></span>
                            <span class="golf-join-card__count">
                                <strong><?php echo (int) ($post['current_count'] ?? 0); ?></strong>
                                <span>/</span>
                                <?php echo (int) ($post['recruit_count'] ?? 0); ?>명
                            </span>
                        </div>

                        <?php if ($tags) { ?>
                        <ul class="golf-join-card__tags" aria-label="조건 및 분위기">
                            <?php foreach ($tags as $tag) { ?>
                            <li><?php echo get_text($tag); ?></li>
                            <?php } ?>
                        </ul>
                        <?php } ?>

                        <p class="golf-join-card__host">
                            <span class="golf-join-card__host-label">방장</span>
                            <?php echo get_text($post['host_nickname'] ?? '회원'); ?>
                        </p>
                    </a>
                </div>
            </div>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_golf_join_member_chip_html')) {
    /**
     * @param array<string, mixed> $member
     */
    function eottae_golf_join_member_chip_html(array $member, $is_host = false)
    {
        $role = (string) ($member['role'] ?? 'member');
        $nick = get_text($member['nickname'] ?? '회원');
        $initial = function_exists('mb_substr') ? mb_substr($nick, 0, 1, 'UTF-8') : substr($nick, 0, 1);
        $badge = $is_host || $role === 'host' ? '방장' : ($role === 'companion' ? '동반' : '');

        ob_start();
        ?>
        <li class="golf-join-member-chip<?php echo $is_host || $role === 'host' ? ' golf-join-member-chip--host' : ''; ?>">
            <span class="golf-join-member-chip__avatar" aria-hidden="true"><?php echo get_text($initial); ?></span>
            <span class="golf-join-member-chip__name"><?php echo $nick; ?></span>
            <?php if ($badge !== '') { ?>
            <span class="golf-join-member-chip__role"><?php echo $badge; ?></span>
            <?php } ?>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}
