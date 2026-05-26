<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_challenge_card_html')) {
    /**
     * @param array<string, mixed> $challenge
     */
    function eottae_challenge_card_html(array $challenge)
    {
        $status = (string) ($challenge['display_status'] ?? 'active');
        $can_join = $status === 'active';
        global $is_member;
        if ($can_join && !empty($is_member)) {
            $write_href = $challenge['write_url'] ?? '#';
        } elseif ($can_join) {
            $write_href = function_exists('eottae_login_url')
                ? eottae_login_url($challenge['write_url'] ?? eottae_challenge_list_url())
                : G5_BBS_URL.'/login.php';
        } else {
            $write_href = $challenge['view_url'] ?? '#';
        }

        ob_start();
        ?>
        <li class="sebu-challenge-card sebu-challenge-card--<?php echo get_text($status); ?>">
            <article class="sebu-challenge-card__inner">
                <div class="sebu-challenge-card__media">
                    <?php if (!empty($challenge['image_url'])) { ?>
                    <img src="<?php echo get_text($challenge['image_url']); ?>" alt="" class="sebu-challenge-card__image" loading="lazy">
                    <?php } else { ?>
                    <span class="sebu-challenge-card__icon" aria-hidden="true"><?php echo get_text($challenge['icon_display'] ?? '🏆'); ?></span>
                    <?php } ?>
                    <span class="<?php echo get_text($challenge['display_status_class'] ?? ''); ?>"><?php echo get_text($challenge['display_status_label'] ?? ''); ?></span>
                </div>
                <div class="sebu-challenge-card__body">
                    <h2 class="sebu-challenge-card__title">
                        <a href="<?php echo get_text($challenge['view_url'] ?? '#'); ?>"><?php echo get_text($challenge['title'] ?? ''); ?></a>
                    </h2>
                    <p class="sebu-challenge-card__period"><?php echo get_text($challenge['period_label'] ?? ''); ?></p>
                    <ul class="sebu-challenge-card__stats">
                        <li>참여 <?php echo number_format((int) ($challenge['participant_count'] ?? 0)); ?>명</li>
                        <li>인증 <?php echo number_format((int) ($challenge['entry_count'] ?? 0)); ?>개</li>
                    </ul>
                    <?php if (!empty($challenge['reward_text'])) { ?>
                    <p class="sebu-challenge-card__reward"><?php echo nl2br(get_text($challenge['reward_text'])); ?></p>
                    <?php } ?>
                    <div class="sebu-challenge-card__actions">
                        <a href="<?php echo get_text($challenge['view_url'] ?? '#'); ?>" class="sebu-challenge-btn sebu-challenge-btn--ghost sebu-challenge-btn--sm">자세히</a>
                        <?php if ($can_join) { ?>
                        <a href="<?php echo $write_href; ?>" class="sebu-challenge-btn sebu-challenge-btn--primary sebu-challenge-btn--sm">참여하기</a>
                        <?php } ?>
                    </div>
                </div>
            </article>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_challenge_entry_card_html')) {
    /**
     * @param array<string, mixed> $entry
     */
    function eottae_challenge_entry_card_html(array $entry)
    {
        if (empty($entry['href'])) {
            return '';
        }

        ob_start();
        ?>
        <li class="sebu-challenge-entry-card<?php echo !empty($entry['is_best']) ? ' sebu-challenge-entry-card--best' : ''; ?>">
            <a href="<?php echo $entry['href']; ?>" class="sebu-challenge-entry-card__link">
                <?php if (!empty($entry['image_url'])) { ?>
                <span class="sebu-challenge-entry-card__thumb">
                    <img src="<?php echo get_text($entry['image_url']); ?>" alt="" loading="lazy">
                </span>
                <?php } ?>
                <span class="sebu-challenge-entry-card__body">
                    <?php if (!empty($entry['is_best'])) { ?>
                    <span class="sebu-challenge-badge sebu-challenge-badge--best">우수 인증</span>
                    <?php } ?>
                    <strong class="sebu-challenge-entry-card__title"><?php echo get_text($entry['title'] ?? ''); ?></strong>
                    <span class="sebu-challenge-entry-card__meta">
                        <?php echo get_text($entry['writer_name'] ?? ''); ?>
                        <?php if (!empty($entry['area_label'])) { ?> · <?php echo get_text($entry['area_label']); ?><?php } ?>
                        <?php if (!empty($entry['time_label'])) { ?> · <?php echo get_text($entry['time_label']); ?><?php } ?>
                    </span>
                    <span class="sebu-challenge-entry-card__counts">
                        <?php if ((int) ($entry['like_count'] ?? 0) > 0) { ?>공감 <?php echo number_format((int) $entry['like_count']); ?><?php } ?>
                        <?php if ((int) ($entry['comment_count'] ?? 0) > 0) { ?> · 댓글 <?php echo number_format((int) $entry['comment_count']); ?><?php } ?>
                    </span>
                </span>
            </a>
        </li>
        <?php

        return (string) ob_get_clean();
    }
}
