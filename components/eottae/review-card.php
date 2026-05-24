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
        $review_url = G5_BBS_URL.'/board.php?bo_table='.eottae_review_table().'&wr_id='.$review['wr_id'];

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
            <?php if ($show_reply_btn) { ?>
            <footer class="review-card__foot">
                <a href="<?php echo $review_url; ?>#bo_vc_w" class="review-card__reply-link">답변 작성</a>
            </footer>
            <?php } ?>
        </article>
        <?php

        return ob_get_clean();
    }
}
