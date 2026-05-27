<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 커뮤니티 목록 1건 — 왼쪽 썸네일, 오른쪽 제목·설명 2행
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
 */
?>
<article class="<?php echo $item_class; ?>">
    <a href="<?php echo $item['href']; ?>" class="community-post__link">
        <?php if ($thumb !== '') { ?>
        <div class="community-post__thumb" aria-hidden="true">
            <img src="<?php echo htmlspecialchars($thumb, ENT_QUOTES, 'UTF-8'); ?>" alt="" width="96" height="96" loading="lazy" decoding="async">
        </div>
        <?php } ?>
        <div class="community-post__body">
            <?php
            $has_badges = $is_ai_post || $is_notice || $ca_name !== '' || $region !== '' || $is_new || $is_hot;
            if ($has_badges) {
                ?>
            <div class="community-post__badges">
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
                    <?php if ($is_new) { ?><span class="community-badge community-badge--new">NEW</span><?php } ?>
                    <?php if ($is_hot) { ?><span class="community-badge community-badge--hot">HOT</span><?php } ?>
                <?php } ?>
            </div>
                <?php
            }
            ?>
            <h2 class="community-post__title<?php echo $is_ai_post ? ' talk-ai-msg__title' : ''; ?>"><?php echo $item['subject']; ?></h2>
            <?php if ($snippet !== '') { ?>
            <p class="community-post__excerpt"><?php echo $snippet; ?></p>
            <?php } ?>
            <div class="community-post__foot">
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
                <div class="community-post__stats">
                    <span class="community-post__stat" title="조회"><span aria-hidden="true">👁</span> <?php echo number_format($hit_num); ?></span>
                    <span class="community-post__stat" title="추천"><span aria-hidden="true">👍</span> <?php echo number_format($good_num); ?></span>
                    <span class="community-post__stat" title="댓글"><span aria-hidden="true">💬</span> <?php echo number_format($comment_num); ?></span>
                </div>
            </div>
        </div>
    </a>
</article>
