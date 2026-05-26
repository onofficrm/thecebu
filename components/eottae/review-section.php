<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_review_cards_html')) {
    function eottae_review_cards_html($reviews, $opts = array())
    {
        if (!is_array($reviews) || empty($reviews)) {
            return '';
        }

        eottae_load_component('review-card');
        $html = '';
        foreach ($reviews as $review) {
            $html .= eottae_review_card_html($review, $opts);
        }

        return $html;
    }
}

if (!function_exists('eottae_review_write_form_html')) {
    /**
     * @param array{variant?:string,form_id?:string,rating_id?:string} $opts
     */
    function eottae_review_write_form_html($shop_wr_id, $shop_name, $token, array $opts = array())
    {
        $variant = isset($opts['variant']) ? (string) $opts['variant'] : 'inline';
        $suffix = $variant === 'modal' ? '' : '-inline';
        $form_id = isset($opts['form_id']) ? (string) $opts['form_id'] : 'eottaeReviewForm'.$suffix;
        $rating_id = isset($opts['rating_id']) ? (string) $opts['rating_id'] : 'eottaeReviewRating'.$suffix;
        $shop_wr_id = (int) $shop_wr_id;
        $shop_name = $shop_name !== '' ? get_text($shop_name) : '';

        ob_start();
        ?>
        <form class="review-write-form__form" id="<?php echo $form_id; ?>" method="post" action="/proc/eottae-review-submit.php" enctype="multipart/form-data" data-review-write-form>
            <input type="hidden" name="shop_wr_id" value="<?php echo $shop_wr_id; ?>">
            <input type="hidden" name="shop_name" value="<?php echo $shop_name; ?>">
            <input type="hidden" name="eottae_review_token" value="<?php echo get_text($token); ?>">
            <input type="hidden" name="rating" id="<?php echo $rating_id; ?>" value="5" data-review-rating-input>

            <div class="review-write-form__stars" role="radiogroup" aria-label="별점 선택" data-review-stars>
                <?php for ($s = 1; $s <= 5; $s++) { ?>
                <button type="button" class="review-write-form__star" data-star="<?php echo $s; ?>" aria-label="<?php echo $s; ?>점">★</button>
                <?php } ?>
            </div>

            <div class="eottae-field">
                <label for="review_content<?php echo $suffix; ?>">리뷰 내용</label>
                <textarea id="review_content<?php echo $suffix; ?>" name="content" required minlength="10" maxlength="1000" rows="4" placeholder="이 업체 이용 경험을 10자 이상 작성해 주세요."></textarea>
            </div>

            <div class="eottae-field">
                <label for="review_photo<?php echo $suffix; ?>">사진 (선택, 1장)</label>
                <input type="file" id="review_photo<?php echo $suffix; ?>" name="photo" accept="image/jpeg,image/png,image/webp">
            </div>

            <button type="submit" class="review-write-form__submit">리뷰 등록</button>
        </form>
        <?php

        return ob_get_clean();
    }
}

if (!function_exists('eottae_review_section_html')) {
    function eottae_review_section_html($shop_wr_id, $shop_name = '')
    {
        global $is_member, $member, $is_admin;

        eottae_load_component('review-card');
        include_once G5_LIB_PATH.'/eottae-review-delete.lib.php';
        include_once G5_LIB_PATH.'/eottae-review-manage.lib.php';
        eottae_review_delete_ensure_schema();

        $shop_wr_id = (int) $shop_wr_id;
        $shop_name = $shop_name !== '' ? get_text($shop_name) : '';
        $review_page_size = defined('EOTTae_REVIEW_LIST_PAGE_SIZE') ? (int) EOTTae_REVIEW_LIST_PAGE_SIZE : 10;
        $summary = eottae_get_shop_review_summary($shop_wr_id);
        $reviews = eottae_get_shop_reviews($shop_wr_id, $review_page_size, 0);
        $loaded_count = count($reviews);
        $has_more_reviews = (int) $summary['count'] > $loaded_count;
        $is_biz = $is_member && eottae_is_business_member($member);
        $already_reviewed = $is_member && eottae_user_reviewed_shop($member['mb_id'], $shop_wr_id);
        $can_write_review = $is_member && !$is_biz && !$already_reviewed;
        $return_url = G5_BBS_URL.'/board.php?bo_table='.EOTTae_SHOP_TABLE.'&wr_id='.$shop_wr_id;
        $login_url = eottae_login_url($return_url.'#shop-review-write');
        $token = eottae_review_token(true);
        $review_card_opts = eottae_review_manage_card_opts($shop_wr_id);
        $pending_delete_ids = ($review_card_opts['show_biz_delete_request'] || $review_card_opts['show_super_manage'])
            ? eottae_review_delete_pending_ids_for_shop($shop_wr_id)
            : array();

        ob_start();
        ?>
        <section class="review-summary shop-detail-page__reviews" id="shop-reviews" data-shop-id="<?php echo $shop_wr_id; ?>">
            <div class="review-summary__head">
                <h2 class="review-summary__title">리뷰</h2>
                <?php if ($can_write_review) { ?>
                <a href="#shop-review-write" class="review-summary__write-btn">리뷰 작성</a>
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
            <div class="review-summary__list" data-review-list>
                <?php
                foreach ($reviews as $review) {
                    $card_opts = $review_card_opts;
                    $card_opts['delete_pending'] = !empty($pending_delete_ids[(int) $review['wr_id']]);
                    echo eottae_review_card_html($review, $card_opts);
                }
                ?>
            </div>
            <?php if ($has_more_reviews) { ?>
            <div class="review-summary__more-wrap">
                <button type="button"
                    class="review-summary__more-btn"
                    data-review-load-more
                    data-shop-id="<?php echo $shop_wr_id; ?>"
                    data-offset="<?php echo $loaded_count; ?>"
                    data-limit="<?php echo $review_page_size; ?>"
                    data-total="<?php echo (int) $summary['count']; ?>">
                    리뷰 더보기 (<?php echo number_format($loaded_count); ?>/<?php echo number_format($summary['count']); ?>)
                </button>
            </div>
            <?php } ?>
            <?php } ?>

            <div class="review-summary__write-box" id="shop-review-write">
                <h3 class="review-summary__write-title">리뷰 작성</h3>
                <?php if ($can_write_review) { ?>
                <p class="review-summary__write-hint"><?php echo $shop_name; ?> 이용 후기를 남겨 주세요.</p>
                <?php echo eottae_review_write_form_html($shop_wr_id, $shop_name, $token, array('variant' => 'inline')); ?>
                <?php } elseif (!$is_member) { ?>
                <p class="review-summary__write-hint">리뷰를 작성하려면 <a href="<?php echo $login_url; ?>">로그인</a>해 주세요.</p>
                <?php } elseif ($is_biz) { ?>
                <p class="review-summary__write-hint">사업자 회원은 리뷰를 작성할 수 없습니다. 부적절한 리뷰는 각 리뷰의 「삭제 요청」으로 관리자에게 알릴 수 있습니다.</p>
                <?php } elseif ($already_reviewed) { ?>
                <p class="review-summary__write-hint">이 업체에는 이미 리뷰를 작성하셨습니다. 작성한 리뷰는 아래 목록에서 수정·삭제할 수 있습니다.</p>
                <?php } ?>
            </div>

            <p class="review-summary__point-note">리뷰 작성 시 <?php echo number_format(defined('EOTTae_REVIEW_POINT_BASE') ? EOTTae_REVIEW_POINT_BASE : 30); ?>P가 지급됩니다. 사진 첨부 시 추가 <?php echo number_format(defined('EOTTae_REVIEW_POINT_PHOTO') ? EOTTae_REVIEW_POINT_PHOTO : 20); ?>P.</p>
        </section>
        <?php

        return ob_get_clean();
    }
}
