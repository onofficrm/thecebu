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
    $shop_content_raw = '';

    if (function_exists('eottae_board_write_content_for_editor')) {
        $shop_content_raw = eottae_board_write_content_for_editor('', $view, 'u');
    } else {
        $shop_content_raw = isset($view['wr_content']) ? (string) $view['wr_content'] : '';
        if ($shop_content_raw !== '' && function_exists('html_purifier')) {
            $shop_content_raw = html_purifier($shop_content_raw);
        }
    }

    if ($shop_content_use_editor) {
        eottae_shop_enqueue_content_editor_assets();
        if (function_exists('editor_html')) {
            $shop_content_editor_html = '<div class="eottae-board-editor shop-detail-page__content-editor">'
                .editor_html('shop_wr_content', $shop_content_raw, true)
                .'</div>';
        } else {
            $shop_content_editor_html = '<textarea id="shop_wr_content" name="wr_content" class="smarteditor2" maxlength="65536" style="width:100%;height:320px">'
                .$shop_content_raw
                .'</textarea>';
        }
    } else {
        $shop_content_editor_html = '<textarea id="shop_wr_content" name="wr_content" rows="12" maxlength="65536" class="shop-detail-page__content-textarea">'
            .htmlspecialchars(strip_tags($shop_content_raw), ENT_NOQUOTES, 'UTF-8')
            .'</textarea>';
    }
}

if ($shop_map_coords['lat'] !== '' && $shop_map_coords['lng'] !== '') {
    eottae_enqueue_google_maps();
}

$eottae_shop_list_href = function_exists('eottae_shop_resolve_list_href')
    ? eottae_shop_resolve_list_href(isset($list_href) ? $list_href : '', $bo_table, isset($qstr) ? $qstr : '')
    : (function_exists('eottae_shop_list_url') ? eottae_shop_list_url() : G5_BBS_URL.'/board.php?bo_table='.$bo_table);
