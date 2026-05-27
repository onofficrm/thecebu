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
    function eottae_adroom_render_shop_picker(array $shops, $selected_bo = '', $selected_wr_id = 0)
    {
        $selected_bo = preg_replace('/[^a-z0-9_]/', '', (string) $selected_bo);
        $selected_wr_id = (int) $selected_wr_id;
        $selected_key = $selected_bo !== '' && $selected_wr_id > 0 ? $selected_bo.':'.$selected_wr_id : '';

        ob_start();
        ?>
        <section class="adroom-shop-picker" id="adroom-shop-picker">
            <h3 class="adroom-shop-picker__title">연동 업체 <span class="adroom-required">*</span></h3>
            <p class="adroom-shop-picker__desc">광고와 함께 노출할 내 업체를 선택하세요. 지도·연락처는 업체 정보와 연동됩니다.</p>

            <?php if (empty($shops)) { ?>
            <div class="adroom-shop-picker__empty">
                <p>등록된 업체가 없습니다. 먼저 업체를 등록한 뒤 광고를 작성해 주세요.</p>
                <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=<?php echo defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop'; ?>" class="adroom-btn adroom-btn--primary">업체 등록하기</a>
            </div>
            <?php } else { ?>
            <input type="hidden" name="eottae_adroom_shop_bo_table" id="eottae_adroom_shop_bo_table" value="<?php echo get_text($selected_bo); ?>">
            <input type="hidden" name="eottae_adroom_shop_wr_id" id="eottae_adroom_shop_wr_id" value="<?php echo (int) $selected_wr_id; ?>">
            <input type="hidden" name="wr_1" value="<?php echo get_text($selected_bo); ?>">
            <input type="hidden" name="wr_2" value="<?php echo (int) $selected_wr_id; ?>">

            <div class="adroom-shop-picker__grid" role="listbox" aria-label="연동 업체 선택">
                <?php foreach ($shops as $shop) {
                    $key = ($shop['bo_table'] ?? '').':'.(int) ($shop['wr_id'] ?? 0);
                    $is_selected = ($key === $selected_key);
                    ?>
                <button type="button"
                    class="adroom-shop-picker__item<?php echo $is_selected ? ' is-selected' : ''; ?>"
                    role="option"
                    aria-selected="<?php echo $is_selected ? 'true' : 'false'; ?>"
                    data-bo-table="<?php echo get_text($shop['bo_table'] ?? ''); ?>"
                    data-wr-id="<?php echo (int) ($shop['wr_id'] ?? 0); ?>"
                    data-name="<?php echo get_text($shop['name'] ?? ''); ?>"
                    data-region="<?php echo get_text($shop['region'] ?? ''); ?>"
                    data-address="<?php echo get_text($shop['address'] ?? ''); ?>">
                    <span class="adroom-shop-picker__thumb"<?php if (!empty($shop['thumb_url'])) { ?> style="background-image:url('<?php echo get_text($shop['thumb_url']); ?>')"<?php } ?>></span>
                    <span class="adroom-shop-picker__info">
                        <strong class="adroom-shop-picker__name"><?php echo get_text($shop['name'] ?? ''); ?></strong>
                        <?php if (!empty($shop['board_label'])) { ?><span class="adroom-shop-picker__board"><?php echo get_text($shop['board_label']); ?></span><?php } ?>
                        <?php if (!empty($shop['region'])) { ?><span class="adroom-shop-picker__meta"><?php echo get_text($shop['region']); ?></span><?php } ?>
                        <?php if (!empty($shop['address'])) { ?><span class="adroom-shop-picker__addr"><?php echo get_text($shop['address']); ?></span><?php } ?>
                    </span>
                </button>
                <?php } ?>
            </div>
            <?php } ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}
