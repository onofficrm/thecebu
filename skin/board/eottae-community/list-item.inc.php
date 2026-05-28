<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 커뮤니티 목록 1건 — 한 행 3열 (썸네일 | 제목·본문 | 통계)
 *
 * @var array<string, mixed> $item
 * @var string $item_class
 * @var string $thumb
 * @var string $snippet
 * @var bool   $is_ai_post
 * @var bool   $is_notice
 * @var string $ca_name
 * @var string $region
 * @var bool   $is_new
 * @var bool   $is_hot
 * @var string $author
 * @var string $time_label
 * @var int    $hit_num
 * @var int    $good_num
 * @var int    $comment_num
 * @var string $estate_deal_label
 * @var string $estate_thumb_html
 * @var string $estate_deal_status
 * @var string $job_recruit_label
 * @var string $job_thumb_html
 * @var string $job_recruit_status
 */
$estate_deal_label = $estate_deal_label ?? '';
$estate_thumb_html = $estate_thumb_html ?? '';
$estate_deal_status = $estate_deal_status ?? '';
$job_recruit_label = $job_recruit_label ?? '';
$job_thumb_html = $job_thumb_html ?? '';
$job_recruit_status = $job_recruit_status ?? '';
$status_thumb_html = $estate_thumb_html !== '' ? $estate_thumb_html : $job_thumb_html;
$has_badges = $is_ai_post || $is_notice || $ca_name !== '' || $region !== '' || $is_new || $is_hot
    || $estate_deal_label !== '' || $job_recruit_label !== '';
?>
<article class="<?php echo $item_class; ?>">
    <a href="<?php echo $item['href']; ?>" class="community-post__link">
        <div class="community-post__cols">
            <?php if ($status_thumb_html !== '') {
                echo $status_thumb_html;
            } elseif ($thumb !== '') { ?>
            <div class="community-post__thumb" aria-hidden="true">
                <img src="<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>" alt="" width="104" height="104" loading="lazy" decoding="async">
            </div>
            <?php } ?>
            <div class="community-post__body">
                <div class="community-post__text">
                    <div class="community-post__title-line">
                        <?php if ($has_badges) { ?>
                        <span class="community-post__badges">
                            <?php if ($is_ai_post) { ?>
                            <?php echo eottae_talkroom_ai_message_render_badge($item, 'sm'); ?>
                            <?php } ?>
                            <?php if ($is_notice) { ?>
                            <span class="community-badge community-badge--notice"><span aria-hidden="true">📣</span> 공지</span>
                            <?php } else { ?>
                                <?php if ($ca_name !== '') { ?>
                            <span class="community-badge <?php echo eottae_community_badge_class($ca_name); ?>"><?php echo $ca_name; ?></span>
                                <?php } ?>
                                <?php if ($region !== '') { ?>
                            <span class="community-badge community-badge--region"><?php echo $region; ?></span>
                                <?php } ?>
                                <?php if ($estate_deal_label !== '') { ?>
                            <span class="community-badge estate-deal-badge <?php echo htmlspecialchars('estate-deal-badge--'.$estate_deal_status, ENT_QUOTES, 'UTF-8'); ?>"><?php echo get_text($estate_deal_label); ?></span>
                                <?php } ?>
                                <?php if ($job_recruit_label !== '') { ?>
                            <span class="community-badge job-recruit-badge <?php echo htmlspecialchars('job-recruit-badge--'.$job_recruit_status, ENT_QUOTES, 'UTF-8'); ?>"><?php echo get_text($job_recruit_label); ?></span>
                                <?php } ?>
                                <?php if ($is_new) { ?><span class="community-badge community-badge--new">NEW</span><?php } ?>
                                <?php if ($is_hot) { ?><span class="community-badge community-badge--hot">HOT</span><?php } ?>
                            <?php } ?>
                        </span>
                        <?php } ?>
                        <h2 class="community-post__title<?php echo $is_ai_post ? ' talk-ai-msg__title' : ''; ?>"><?php echo $item['subject']; ?></h2>
                    </div>
                    <?php if ($snippet !== '') { ?>
                    <p class="community-post__excerpt"><?php echo $snippet; ?></p>
                    <?php } ?>
                </div>
                <div class="community-post__meta">
                    <?php if ($is_ai_post) { ?>
                    <span class="community-post__author talk-ai-msg__author-line"><?php echo eottae_talkroom_ai_message_display_name($item); ?></span>
                    <?php } elseif (function_exists('eottae_member_growth_render_author_line') && !empty($item['mb_id'])) { ?>
                    <span class="community-post__author"><?php echo eottae_member_growth_render_author_line($item['mb_id'], $author, array('inline' => true, 'badge_only' => true)); ?></span>
                    <?php } else { ?>
                    <span class="community-post__author"><?php echo $author; ?></span>
                    <?php } ?>
                    <span class="community-post__time"><?php echo $time_label; ?></span>
                </div>
            </div>
            <div class="community-post__aside" aria-label="게시글 통계">
                <div class="community-post__stats">
                    <span class="community-post__stat" title="조회"><span class="community-post__stat-icon" aria-hidden="true">👁</span><span class="community-post__stat-num"><?php echo number_format($hit_num); ?></span></span>
                    <span class="community-post__stat" title="추천"><span class="community-post__stat-icon" aria-hidden="true">👍</span><span class="community-post__stat-num"><?php echo number_format($good_num); ?></span></span>
                    <span class="community-post__stat" title="댓글"><span class="community-post__stat-icon" aria-hidden="true">💬</span><span class="community-post__stat-num"><?php echo number_format($comment_num); ?></span></span>
                </div>
            </div>
        </div>
    </a>
</article>
