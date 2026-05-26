<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$shop_map_coords = eottae_shop_resolve_map_coords($shop);
$shop_map['lat'] = $shop_map_coords['lat'];
$shop_map['lng'] = $shop_map_coords['lng'];

$shop_can_edit_content = function_exists('eottae_shop_user_can_manage') && eottae_shop_user_can_manage($view, $bo_table);
$shop_content_use_editor = $shop_can_edit_content && function_exists('eottae_shop_content_editor_enabled') && eottae_shop_content_editor_enabled($board);
$shop_content_editor_html = '';
$shop_content_token = '';

if ($shop_can_edit_content) {
    $shop_content_token = eottae_shop_content_token(false);
    $shop_content_raw = isset($view['wr_content']) ? $view['wr_content'] : '';

    if ($shop_content_use_editor) {
        eottae_shop_enqueue_content_editor_assets();
        if (function_exists('get_text') && function_exists('html_purifier')) {
            $shop_content_raw = get_text(html_purifier($shop_content_raw), 0);
        }
        $shop_content_editor_html = '<textarea id="shop_wr_content" name="wr_content" maxlength="65536" style="width:100%;height:320px">'.htmlspecialchars($shop_content_raw, ENT_NOQUOTES, 'UTF-8').'</textarea>';
    } else {
        $shop_content_editor_html = '<textarea id="shop_wr_content" name="wr_content" rows="12" maxlength="65536" class="shop-detail-page__content-textarea">'.htmlspecialchars(strip_tags($shop_content_raw), ENT_NOQUOTES, 'UTF-8').'</textarea>';
    }
}

if ($shop_map_coords['lat'] !== '' && $shop_map_coords['lng'] !== '') {
    eottae_enqueue_google_maps();
}
