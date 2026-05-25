<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_review_stars_html')) {
    function eottae_review_stars_html($rating, $size = 'md')
    {
        $rating = max(0, min(5, (float) $rating));
        $full = (int) floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;
        $class = 'review-card__stars review-card__stars--'.$size;
        $html = '<span class="'.$class.'" aria-label="별점 '.$rating.'점">';

        for ($i = 0; $i < $full; $i++) {
            $html .= '<span class="review-card__star is-filled">★</span>';
        }
        if ($half) {
            $html .= '<span class="review-card__star is-half">★</span>';
        }
        for ($i = 0; $i < $empty; $i++) {
            $html .= '<span class="review-card__star">★</span>';
        }

        $html .= '</span>';

        return $html;
    }
}

if (!function_exists('eottae_review_card_html')) {
    function eottae_review_card_html($review, $opts = array())
    {
        if (!is_array($review) || empty($review['wr_id'])) {
            return '';
        }

        $show_reply_btn = !empty($opts['show_reply_btn']);
        $reply_token = isset($opts['reply_token']) ? (string) $opts['reply_token'] : '';
        $shop_wr_id = isset($opts['shop_wr_id']) ? (int) $opts['shop_wr_id'] : (int) ($review['shop_id'] ?? 0);
        $show_super_delete = !empty($opts['show_super_delete']);
        $show_biz_delete_request = !empty($opts['show_biz_delete_request']);
        $delete_token = isset($opts['delete_token']) ? (string) $opts['delete_token'] : '';
        $delete_pending = !empty($opts['delete_pending']);
        $show_delete_actions = ($show_super_delete || ($show_biz_delete_request && !$delete_pending)) && $delete_token !== '' && $shop_wr_id > 0;
        $has_biz_reply = false;
        if (!empty($review['replies'])) {
            foreach ($review['replies'] as $reply_row) {
                if (!empty($reply_row['is_biz'])) {
                    $has_biz_reply = true;
                    break;
                }
            }
        }
        $show_reply_form = $show_reply_btn && !$has_biz_reply && $reply_token !== '' && $shop_wr_id > 0;

        ob_start();
        ?>
        <article class="review-card" id="review-<?php echo (int) $review['wr_id']; ?>">
            <header class="review-card__head">
                <div class="review-card__author-wrap">
                    <strong class="review-card__author"><?php echo $review['author']; ?></strong>
                    <?php if (!empty($review['photo_count'])) { ?>
                    <span class="review-card__badge">사진리뷰</span>
                    <?php } ?>
                </div>
                <time class="review-card__date" datetime="<?php echo $review['datetime']; ?>">
                    <?php echo date('Y.m.d', $review['datetime_ts'] ?: time()); ?>
                </time>
            </header>
            <div class="review-card__rating">
                <?php echo eottae_review_stars_html($review['rating']); ?>
                <span class="review-card__rating-num"><?php echo number_format($review['rating'], 1); ?></span>
            </div>
            <?php if (!empty($review['shop_id'])) { ?>
            <p class="review-card__shop">
                <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>&wr_id=<?php echo (int) $review['shop_id']; ?>">
                    <?php echo $review['shop_name'] ? get_text($review['shop_name']) : '업체 보기'; ?>
                </a>
            </p>
            <?php } ?>
            <div class="review-card__content">
                <?php echo nl2br(get_text(strip_tags($review['content']))); ?>
            </div>
            <?php if (!empty($review['photos'])) { ?>
            <div class="review-card__photos">
                <?php foreach ($review['photos'] as $photo) { ?>
                <a href="<?php echo $photo; ?>" class="review-card__photo" target="_blank" rel="noopener noreferrer">
                    <img src="<?php echo $photo; ?>" alt="리뷰 사진" loading="lazy">
                </a>
                <?php } ?>
            </div>
            <?php } ?>
            <?php if (!empty($review['replies'])) { ?>
            <div class="review-card__replies">
                <?php foreach ($review['replies'] as $reply) { ?>
                <div class="review-card__reply">
                    <?php if (!empty($reply['is_biz'])) { ?>
                    <span class="review-card__reply-badge">사업자</span>
                    <?php } ?>
                    <strong><?php echo $reply['author']; ?></strong>
                    <p><?php echo $reply['content']; ?></p>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php if ($show_reply_form) { ?>
            <footer class="review-card__foot">
                <form class="review-card__reply-form" method="post" action="/proc/eottae-review-reply.php" data-review-reply-form>
                    <input type="hidden" name="review_wr_id" value="<?php echo (int) $review['wr_id']; ?>">
                    <input type="hidden" name="shop_wr_id" value="<?php echo $shop_wr_id; ?>">
                    <input type="hidden" name="eottae_review_reply_token" value="<?php echo get_text($reply_token); ?>">
                    <label class="sound_only" for="review-reply-<?php echo (int) $review['wr_id']; ?>">사업자 답변</label>
                    <textarea id="review-reply-<?php echo (int) $review['wr_id']; ?>" name="content" required minlength="2" maxlength="500" rows="3" placeholder="고객 리뷰에 답변을 남겨 주세요."></textarea>
                    <button type="submit" class="review-card__reply-submit">답변 등록</button>
                </form>
            </footer>
            <?php } ?>
            <?php if ($delete_pending || $show_delete_actions) { ?>
            <footer class="review-card__foot review-card__foot--admin">
                <?php if ($delete_pending) { ?>
                <span class="review-card__delete-pending">삭제 검토 중</span>
                <?php } elseif ($show_super_delete) { ?>
                <button type="button"
                    class="review-card__delete-btn review-card__delete-btn--super"
                    data-review-super-delete
                    data-review-id="<?php echo (int) $review['wr_id']; ?>"
                    data-shop-id="<?php echo $shop_wr_id; ?>"
                    data-delete-token="<?php echo get_text($delete_token); ?>">리뷰 삭제</button>
                <?php } elseif ($show_biz_delete_request) { ?>
                <button type="button"
                    class="review-card__delete-btn review-card__delete-btn--request"
                    data-review-delete-request
                    data-review-id="<?php echo (int) $review['wr_id']; ?>"
                    data-shop-id="<?php echo $shop_wr_id; ?>"
                    data-delete-token="<?php echo get_text($delete_token); ?>">삭제 요청</button>
                <?php } ?>
            </footer>
            <?php } ?>
        </article>
        <?php

        return ob_get_clean();
    }
}
