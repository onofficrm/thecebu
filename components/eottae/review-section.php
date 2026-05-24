<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_review_section_html')) {
    function eottae_review_section_html($shop_wr_id, $shop_name = '')
    {
        global $is_member, $member;

        eottae_load_component('review-card');

        $shop_wr_id = (int) $shop_wr_id;
        $shop_name = $shop_name !== '' ? get_text($shop_name) : '';
        $summary = eottae_get_shop_review_summary($shop_wr_id);
        $reviews = eottae_get_shop_reviews($shop_wr_id, 10);
        $is_biz = $is_member && eottae_is_business_member($member);
        $already_reviewed = $is_member && eottae_user_reviewed_shop($member['mb_id'], $shop_wr_id);
        $return_url = G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.$shop_wr_id;
        $login_url = eottae_login_url($return_url);
        $token = eottae_review_token(true);
        $reply_token = eottae_review_reply_token(true);
        $owns_shop = $is_member && $is_biz && eottae_business_owns_shop($member['mb_id'], $shop_wr_id);
        $show_biz_reply = $owns_shop;

        ob_start();
        ?>
        <section class="review-summary shop-detail-page__reviews" id="shop-reviews" data-shop-id="<?php echo $shop_wr_id; ?>">
            <div class="review-summary__head">
                <h2 class="review-summary__title">리뷰</h2>
                <?php if ($is_member && !$already_reviewed && !$is_biz) { ?>
                <button type="button" class="review-summary__write-btn" data-review-open="1">리뷰 작성</button>
                <?php } elseif (!$is_member) { ?>
                <a href="<?php echo $login_url; ?>" class="review-summary__write-btn">로그인 후 리뷰 작성</a>
                <?php } ?>
            </div>

            <div class="review-summary__stats">
                <div class="review-summary__average">
                    <span class="review-summary__average-num"><?php echo number_format($summary['average'], 1); ?></span>
                    <?php echo eottae_review_stars_html($summary['average'], 'lg'); ?>
                    <p class="review-summary__count"><?php echo number_format($summary['count']); ?>개의 리뷰</p>
                </div>
                <div class="review-summary__bars">
                    <?php for ($star = 5; $star >= 1; $star--) {
                        $cnt = isset($summary['distribution'][$star]) ? (int) $summary['distribution'][$star] : 0;
                        $pct = $summary['count'] > 0 ? round(($cnt / $summary['count']) * 100) : 0;
                        ?>
                    <div class="review-summary__bar-row">
                        <span class="review-summary__bar-label"><?php echo $star; ?>점</span>
                        <span class="review-summary__bar-track"><span class="review-summary__bar-fill" style="width:<?php echo $pct; ?>%"></span></span>
                        <span class="review-summary__bar-count"><?php echo $cnt; ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <?php if (empty($reviews)) { ?>
            <div class="empty-state review-summary__empty">
                <p class="empty-state__title">아직 리뷰가 없습니다</p>
                <p>첫 번째 리뷰를 남겨 주세요.</p>
            </div>
            <?php } else { ?>
            <div class="review-summary__list">
                <?php foreach ($reviews as $review) {
                    echo eottae_review_card_html($review, array(
                        'show_reply_btn' => $show_biz_reply,
                        'reply_token' => $reply_token,
                        'shop_wr_id' => $shop_wr_id,
                    ));
                } ?>
            </div>
            <?php } ?>

            <p class="review-summary__point-note">리뷰 작성 시 <?php echo number_format(defined('EOTTae_REVIEW_POINT_BASE') ? EOTTae_REVIEW_POINT_BASE : 30); ?>P가 지급됩니다. 사진 첨부 시 추가 <?php echo number_format(defined('EOTTae_REVIEW_POINT_PHOTO') ? EOTTae_REVIEW_POINT_PHOTO : 20); ?>P.</p>
        </section>

        <div class="eottae-review-modal" id="eottaeReviewModal" aria-hidden="true">
            <div class="eottae-review-modal__panel review-write-form">
                <button type="button" class="eottae-review-modal__close" data-review-close aria-label="닫기">&times;</button>
                <h3 class="review-write-form__title">리뷰 작성</h3>
                <p class="review-write-form__shop"><?php echo $shop_name; ?></p>
                <form class="review-write-form__form" id="eottaeReviewForm" method="post" action="/proc/eottae-review-submit.php" enctype="multipart/form-data">
                    <input type="hidden" name="shop_wr_id" value="<?php echo $shop_wr_id; ?>">
                    <input type="hidden" name="shop_name" value="<?php echo $shop_name; ?>">
                    <input type="hidden" name="eottae_review_token" value="<?php echo $token; ?>">
                    <input type="hidden" name="rating" id="eottaeReviewRating" value="5">

                    <div class="review-write-form__stars" role="radiogroup" aria-label="별점 선택">
                        <?php for ($s = 1; $s <= 5; $s++) { ?>
                        <button type="button" class="review-write-form__star" data-star="<?php echo $s; ?>" aria-label="<?php echo $s; ?>점">★</button>
                        <?php } ?>
                    </div>

                    <div class="eottae-field">
                        <label for="review_content">리뷰 내용</label>
                        <textarea id="review_content" name="content" required minlength="10" maxlength="1000" placeholder="이 업체 이용 경험을 10자 이상 작성해 주세요."></textarea>
                    </div>

                    <div class="eottae-field">
                        <label for="review_photo">사진 (선택, 1장)</label>
                        <input type="file" id="review_photo" name="photo" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <button type="submit" class="review-write-form__submit">리뷰 등록</button>
                </form>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}
