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

if (!function_exists('eottae_review_card_edit_form_html')) {
    function eottae_review_card_edit_form_html(array $review, array $opts = array())
    {
        $shop_wr_id = (int) ($opts['shop_wr_id'] ?? $review['shop_id'] ?? 0);
        $manage_token = (string) ($opts['manage_token'] ?? '');
        $review_wr_id = (int) ($review['wr_id'] ?? 0);
        $rating = (int) round((float) ($review['rating'] ?? 5));
        if ($rating < 1) {
            $rating = 5;
        }

        ob_start();
        ?>
        <form class="review-card__edit-form" method="post" action="/proc/eottae-review-update.php" data-review-edit-form hidden>
            <input type="hidden" name="review_wr_id" value="<?php echo $review_wr_id; ?>">
            <input type="hidden" name="shop_wr_id" value="<?php echo $shop_wr_id; ?>">
            <input type="hidden" name="eottae_review_token" value="<?php echo get_text($manage_token); ?>">
            <input type="hidden" name="rating" value="<?php echo $rating; ?>" data-review-rating-input>

            <div class="review-write-form__stars review-card__edit-stars" data-review-stars>
                <?php for ($s = 1; $s <= 5; $s++) { ?>
                <button type="button" class="review-write-form__star<?php echo $s <= $rating ? ' is-active' : ''; ?>" data-star="<?php echo $s; ?>" aria-label="<?php echo $s; ?>점">★</button>
                <?php } ?>
            </div>

            <div class="eottae-field">
                <label class="sound_only" for="review-edit-content-<?php echo $review_wr_id; ?>">리뷰 수정</label>
                <textarea id="review-edit-content-<?php echo $review_wr_id; ?>" name="content" required minlength="10" maxlength="1000" rows="4"><?php echo get_text(strip_tags((string) ($review['content'] ?? ''))); ?></textarea>
            </div>

            <div class="review-card__edit-actions">
                <button type="submit" class="review-card__edit-save">저장</button>
                <button type="button" class="review-card__edit-cancel" data-review-edit-cancel>취소</button>
            </div>
        </form>
        <?php

        return ob_get_clean();
    }
}

if (!function_exists('eottae_review_card_html')) {
    function eottae_review_card_html($review, $opts = array())
    {
        if (!is_array($review) || empty($review['wr_id'])) {
            return '';
        }

        include_once G5_LIB_PATH.'/eottae-review-manage.lib.php';

        $show_reply_btn = !empty($opts['show_reply_btn']);
        $reply_token = isset($opts['reply_token']) ? (string) $opts['reply_token'] : '';
        $shop_wr_id = isset($opts['shop_wr_id']) ? (int) $opts['shop_wr_id'] : (int) ($review['shop_id'] ?? 0);
        $show_super_manage = !empty($opts['show_super_manage']);
        $show_biz_delete_request = !empty($opts['show_biz_delete_request']);
        $show_author_manage = !empty($opts['show_author_manage']);
        $current_mb_id = isset($opts['current_mb_id']) ? (string) $opts['current_mb_id'] : '';
        $manage_token = isset($opts['manage_token']) ? (string) $opts['manage_token'] : '';
        $delete_token = isset($opts['delete_token']) ? (string) $opts['delete_token'] : '';
        $delete_pending = !empty($opts['delete_pending']);

        $is_author = $show_author_manage && eottae_review_user_owns_review($review, $current_mb_id);
        $can_edit = $show_super_manage || $is_author;
        $can_delete = $show_super_manage || $is_author;
        $author_token = $manage_token !== '' ? $manage_token : $delete_token;

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
        <article class="review-card" id="review-<?php echo (int) $review['wr_id']; ?>" data-review-id="<?php echo (int) $review['wr_id']; ?>">
            <header class="review-card__head">
                <div class="review-card__author-wrap">
                    <strong class="review-card__author"><?php echo $review['author']; ?></strong>
                    <?php if (!empty($review['photo_count'])) { ?>
                    <span class="review-card__badge">사진리뷰</span>
                    <?php } ?>
                    <?php if ($show_super_manage) { ?>
                    <span class="review-card__badge review-card__badge--admin">관리</span>
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
            <div class="review-card__body" data-review-body>
                <div class="review-card__content" data-review-content>
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
            </div>

            <?php if ($can_edit) {
                echo eottae_review_card_edit_form_html($review, array(
                    'shop_wr_id'    => $shop_wr_id,
                    'manage_token'  => $manage_token,
                ));
            } ?>

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

            <?php if ($delete_pending || $can_edit || $can_delete || $show_biz_delete_request) { ?>
            <footer class="review-card__foot review-card__foot--manage">
                <?php if ($delete_pending) { ?>
                <span class="review-card__delete-pending">삭제 검토 중 (관리자 확인)</span>
                <?php } else { ?>
                    <?php if ($can_edit) { ?>
                    <button type="button"
                        class="review-card__manage-btn review-card__manage-btn--edit"
                        data-review-edit-open
                        data-review-id="<?php echo (int) $review['wr_id']; ?>">수정</button>
                    <?php } ?>
                    <?php if ($can_delete) { ?>
                    <button type="button"
                        class="review-card__manage-btn review-card__manage-btn--delete"
                        data-review-author-delete="<?php echo $show_super_manage ? '0' : '1'; ?>"
                        data-review-super-delete="<?php echo $show_super_manage ? '1' : '0'; ?>"
                        data-review-id="<?php echo (int) $review['wr_id']; ?>"
                        data-shop-id="<?php echo $shop_wr_id; ?>"
                        data-delete-token="<?php echo get_text($delete_token !== '' ? $delete_token : $author_token); ?>"
                        data-manage-token="<?php echo get_text($author_token); ?>">삭제</button>
                    <?php } ?>
                    <?php if ($show_biz_delete_request && !$can_delete) { ?>
                    <button type="button"
                        class="review-card__manage-btn review-card__manage-btn--request"
                        data-review-delete-request
                        data-review-id="<?php echo (int) $review['wr_id']; ?>"
                        data-shop-id="<?php echo $shop_wr_id; ?>"
                        data-delete-token="<?php echo get_text($delete_token); ?>">삭제 요청</button>
                    <?php } ?>
                <?php } ?>
            </footer>
            <?php } ?>
        </article>
        <?php

        return ob_get_clean();
    }
}
