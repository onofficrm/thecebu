<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-my-reviews.php'));
}

eottae_load_component('review-card');
$reviews = eottae_get_member_reviews($member['mb_id'], 30);

g5_page_start('내 리뷰');
?>

<main class="mypage-subpage">
    <h1 class="mypage-subpage__title">내 리뷰</h1>

    <?php if (empty($reviews)) { ?>
    <div class="empty-state">
        <p class="empty-state__title">작성한 리뷰가 없습니다</p>
        <p><a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>">내주변 업소</a>에서 리뷰를 남겨 보세요.</p>
    </div>
    <?php } else { ?>
    <div class="review-summary__list">
        <?php foreach ($reviews as $review) {
            $review['photos'] = eottae_get_review_photos(eottae_review_table(), $review['wr_id']);
            $review['replies'] = eottae_get_review_replies($review['wr_id']);
            echo eottae_review_card_html($review);
        } ?>
    </div>
    <?php } ?>
</main>

<?php
g5_page_end();
