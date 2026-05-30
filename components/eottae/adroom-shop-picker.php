<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_adroom_render_shop_picker')) {
    /**
     * @param array<int, array<string, mixed>> $shops
     * @param string $selected_bo
     * @param int $selected_wr_id
     */
    function eottae_adroom_render_shop_picker(array $shops, $selected_bo = '', $selected_wr_id = 0, $shop_required = true)
    {
        $selected_bo = preg_replace('/[^a-z0-9_]/', '', (string) $selected_bo);
        $selected_wr_id = (int) $selected_wr_id;
        $selected_key = $selected_bo !== '' && $selected_wr_id > 0 ? $selected_bo.':'.$selected_wr_id : '';
        $shop_required = (bool) $shop_required;
        $promo_active = function_exists('eottae_adroom_promotion_active') && eottae_adroom_promotion_active();

        ob_start();
        ?>
        <section class="adroom-shop-picker" id="adroom-shop-picker">
            <h3 class="adroom-shop-picker__title">연동 업체<?php if ($shop_required) { ?> <span class="adroom-required">*</span><?php } else { ?> <span class="adroom-shop-picker__optional">선택</span><?php } ?></h3>
            <p class="adroom-shop-picker__desc"><?php echo $promo_active ? '내 계정에 연결된 업체 중에서 선택합니다. 업체가 없어도 광고만 먼저 등록할 수 있습니다.' : '내 계정에 연결된 업체 중에서 광고와 함께 노출할 업체를 선택하세요.'; ?></p>

            <?php if (empty($shops)) { ?>
            <div class="adroom-shop-picker__empty">
                <p><strong>현재 로그인 계정에 연결된 업체가 없습니다.</strong></p>
                <p>업체 목록·지도 검색에 보이는 업체와 광고방 연동 업체는 다릅니다. 광고방에서는 <strong>내 계정으로 등록했거나 관리 권한이 부여된 업체</strong>만 선택할 수 있습니다.</p>
                <ul class="adroom-shop-picker__empty-list">
                    <li>업체 등록 시 사용한 계정으로 로그인했는지 확인해 주세요.</li>
                    <li>관리자가 대신 등록한 경우, 업체 수정 화면에서 <strong>업체 관리 회원</strong>으로 본인 계정을 지정해야 합니다.</li>
                </ul>
                <?php if ($promo_active) { ?>
                <p class="adroom-shop-picker__empty-note">업체 연동 없이도 광고 등록은 가능합니다. 연동하면 지도·연락처가 함께 표시됩니다.</p>
                <?php } ?>
                <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=<?php echo defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop'; ?>" class="adroom-btn adroom-btn--primary">업체 등록하기</a>
            </div>
            <?php } else { ?>
            <input type="hidden" name="eottae_adroom_shop_bo_table" id="eottae_adroom_shop_bo_table" value="<?php echo get_text($selected_bo); ?>">
            <input type="hidden" name="eottae_adroom_shop_wr_id" id="eottae_adroom_shop_wr_id" value="<?php echo (int) $selected_wr_id; ?>">
            <input type="hidden" name="wr_1" value="<?php echo get_text($selected_bo); ?>">
            <input type="hidden" name="wr_2" value="<?php echo (int) $selected_wr_id; ?>">

            <label class="adroom-shop-picker__search-label" for="adroom-shop-picker-search">업체 검색</label>
            <input type="search" id="adroom-shop-picker-search" class="adroom-shop-picker__search" placeholder="업체명, 지역, 주소 검색" autocomplete="off">

            <p class="adroom-shop-picker__result-hint" id="adroom-shop-picker-count" aria-live="polite"><?php echo number_format(count($shops)); ?>개 업체</p>

            <div class="adroom-shop-picker__grid" role="listbox" aria-label="연동 업체 선택">
                <?php foreach ($shops as $shop) {
                    $key = ($shop['bo_table'] ?? '').':'.(int) ($shop['wr_id'] ?? 0);
                    $is_selected = ($key === $selected_key);
                    $thumb_url = (string) ($shop['thumb_url'] ?? '');
                    $search_text = (string) ($shop['search_text'] ?? '');
                    ?>
                <button type="button"
                    class="adroom-shop-picker__item<?php echo $is_selected ? ' is-selected' : ''; ?>"
                    role="option"
                    aria-selected="<?php echo $is_selected ? 'true' : 'false'; ?>"
                    data-bo-table="<?php echo get_text($shop['bo_table'] ?? ''); ?>"
                    data-wr-id="<?php echo (int) ($shop['wr_id'] ?? 0); ?>"
                    data-name="<?php echo get_text($shop['name'] ?? ''); ?>"
                    data-region="<?php echo get_text($shop['region'] ?? ''); ?>"
                    data-address="<?php echo get_text($shop['address'] ?? ''); ?>"
                    data-search="<?php echo get_text($search_text); ?>">
                    <span class="adroom-shop-picker__thumb-wrap">
                        <?php if ($thumb_url !== '') { ?>
                        <img src="<?php echo get_text($thumb_url); ?>" alt="" class="adroom-shop-picker__thumb-img" loading="lazy" decoding="async">
                        <?php } else { ?>
                        <span class="adroom-shop-picker__thumb adroom-shop-picker__thumb--empty" aria-hidden="true"></span>
                        <?php } ?>
                    </span>
                    <span class="adroom-shop-picker__info">
                        <strong class="adroom-shop-picker__name"><?php echo get_text($shop['name'] ?? ''); ?></strong>
                        <?php if (!empty($shop['board_label'])) { ?><span class="adroom-shop-picker__board"><?php echo get_text($shop['board_label']); ?></span><?php } ?>
                        <?php if (!empty($shop['region'])) { ?><span class="adroom-shop-picker__meta"><?php echo get_text($shop['region']); ?></span><?php } ?>
                        <?php if (!empty($shop['address'])) { ?><span class="adroom-shop-picker__addr"><?php echo get_text($shop['address']); ?></span><?php } ?>
                    </span>
                </button>
                <?php } ?>
            </div>
            <p class="adroom-shop-picker__no-match" id="adroom-shop-picker-no-match" hidden>내 업체 중 검색어와 일치하는 항목이 없습니다. 업체 목록 전체 검색이 아니라, 현재 계정에 연결된 업체만 표시됩니다.</p>
            <?php } ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
