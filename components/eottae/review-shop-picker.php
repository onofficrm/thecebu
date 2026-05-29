<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_review_board') || !eottae_is_review_board($bo_table ?? '')) {
    return;
}

include_once G5_LIB_PATH.'/eottae-review-board.lib.php';

$review_shop_wr_id = 0;
$review_shop_bo = '';
$review_shop_name = '';
$review_rating = 0;
$review_selected_shop = null;

if (isset($write) && is_array($write)) {
    $review_shop_wr_id = (int) ($write['wr_1'] ?? 0);
    $review_shop_bo = eottae_review_board_normalize_shop_bo($write['wr_6'] ?? '');
    $review_shop_name = get_text($write['wr_3'] ?? '');
    $review_rating = (int) ($write['wr_2'] ?? 0);
}

if ($review_shop_wr_id < 1 && $w !== 'u') {
    $prefill_id = isset($_GET['shop_id']) ? (int) $_GET['shop_id'] : 0;
    $prefill_bo = isset($_GET['shop_bo']) ? eottae_review_board_normalize_shop_bo($_GET['shop_bo']) : '';
    if ($prefill_id > 0) {
        $review_selected_shop = eottae_review_board_fetch_shop($prefill_id, $prefill_bo);
        if ($review_selected_shop) {
            $review_shop_wr_id = (int) $review_selected_shop['wr_id'];
            $review_shop_bo = (string) $review_selected_shop['bo_table'];
            $review_shop_name = (string) $review_selected_shop['name'];
        }
    }
} elseif ($review_shop_wr_id > 0) {
    $review_selected_shop = eottae_review_board_fetch_shop($review_shop_wr_id, $review_shop_bo);
    if ($review_selected_shop) {
        $review_shop_name = (string) $review_selected_shop['name'];
        $review_shop_bo = (string) $review_selected_shop['bo_table'];
    }
}

$review_search_url = G5_URL.'/proc/eottae-review-shop-search.php';
?>

<section class="review-shop-picker" id="reviewShopPicker" data-review-shop-picker data-search-url="<?php echo get_text($review_search_url); ?>">
    <div class="review-shop-picker__head">
        <h2 class="review-shop-picker__title">연결 업체 <span class="review-shop-picker__optional">(선택)</span></h2>
        <p class="review-shop-picker__desc">등록된 업체를 검색해 연결할 수 있습니다. 목록에 없는 업체도 제목·내용만으로 리뷰를 올릴 수 있습니다.</p>
    </div>

    <input type="hidden" name="eottae_review_shop_wr_id" id="eottae_review_shop_wr_id" value="<?php echo (int) $review_shop_wr_id; ?>">
    <input type="hidden" name="eottae_review_shop_bo_table" id="eottae_review_shop_bo_table" value="<?php echo get_text($review_shop_bo); ?>">
    <input type="hidden" name="eottae_review_shop_name" id="eottae_review_shop_name" value="<?php echo get_text($review_shop_name); ?>">
    <input type="hidden" name="wr_1" value="<?php echo (int) $review_shop_wr_id; ?>">
    <input type="hidden" name="wr_6" value="<?php echo get_text($review_shop_bo); ?>">

    <div class="review-shop-picker__selected<?php echo $review_selected_shop ? '' : ' is-empty'; ?>" id="reviewShopPickerSelected"<?php echo $review_selected_shop ? '' : ' hidden'; ?>>
        <div class="review-shop-picker__selected-card">
            <?php if ($review_selected_shop && !empty($review_selected_shop['thumb_url'])) { ?>
            <img src="<?php echo get_text($review_selected_shop['thumb_url']); ?>" alt="" class="review-shop-picker__selected-thumb" loading="lazy" decoding="async">
            <?php } ?>
            <div class="review-shop-picker__selected-body">
                <strong class="review-shop-picker__selected-name" id="reviewShopPickerSelectedName"><?php echo get_text($review_shop_name); ?></strong>
                <span class="review-shop-picker__selected-meta" id="reviewShopPickerSelectedMeta"><?php
                    if ($review_selected_shop) {
                        $meta = array();
                        if (!empty($review_selected_shop['board_label'])) {
                            $meta[] = get_text($review_selected_shop['board_label']);
                        }
                        if (!empty($review_selected_shop['region'])) {
                            $meta[] = get_text($review_selected_shop['region']);
                        }
                        echo implode(' · ', $meta);
                    }
                ?></span>
            </div>
            <button type="button" class="review-shop-picker__clear" id="reviewShopPickerClear" aria-label="업체 연결 해제">연결 해제</button>
        </div>
    </div>

    <div class="review-shop-picker__search-wrap" id="reviewShopPickerSearchWrap"<?php echo $review_selected_shop ? ' hidden' : ''; ?>>
        <label class="review-shop-picker__search-label" for="reviewShopPickerSearch">업체 검색</label>
        <input type="search" id="reviewShopPickerSearch" class="review-shop-picker__search" placeholder="업체명, 지역, 주소로 검색" autocomplete="off">
        <p class="review-shop-picker__hint" id="reviewShopPickerHint">검색어를 입력하면 등록된 업체가 표시됩니다.</p>
        <div class="review-shop-picker__results" id="reviewShopPickerResults" role="listbox" aria-label="업체 검색 결과" hidden></div>
        <p class="review-shop-picker__empty" id="reviewShopPickerEmpty" hidden>검색 결과가 없습니다. 업체 연결 없이 리뷰를 작성해 주세요.</p>
    </div>

    <div class="review-shop-picker__rating">
        <span class="review-shop-picker__rating-label">별점 <span class="review-shop-picker__optional">(선택)</span></span>
        <div class="review-shop-picker__stars" role="radiogroup" aria-label="별점 선택" data-review-board-stars>
            <?php for ($s = 1; $s <= 5; $s += 1) { ?>
            <button type="button" class="review-shop-picker__star<?php echo $review_rating >= $s ? ' is-active' : ''; ?>" data-star="<?php echo $s; ?>" aria-label="<?php echo $s; ?>점">★</button>
            <?php } ?>
        </div>
        <input type="hidden" name="wr_2" id="wr_2" value="<?php echo (int) $review_rating; ?>">
        <input type="hidden" name="wr_3" id="wr_3" value="<?php echo get_text($review_shop_name); ?>">
    </div>
</section>
