<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_shop_card_html')) {
    function eottae_shop_card_html($row, $bo_table = '', $layout = 'grid')
    {
        if (!is_array($row)) {
            return '';
        }

        if ($layout === 'list') {
            return eottae_shop_list_card_html($row, $bo_table);
        }

        if ($bo_table === '') {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }

        $shop = eottae_shop_from_write($row, $bo_table);
        $thumb = eottae_shop_card_thumb($row, $bo_table);
        if ($thumb !== '' && function_exists('eottae_map_public_url')) {
            $thumb = eottae_map_public_url($thumb);
        }
        $href = function_exists('eottae_shop_view_url')
            ? eottae_shop_view_url($shop['wr_id'], $bo_table)
            : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$shop['wr_id'];
        $status_class = $shop['status'] === '영업중' ? ' shop-card--open' : '';
        $event_badges = eottae_shop_card_event_badges_html($row, $bo_table);
        $language_badges = function_exists('eottae_lang_short_badges_html')
            ? eottae_lang_short_badges_html($shop['available_languages'], 'shop-card__lang-badges')
            : '';

        ob_start();
        ?>
        <article class="shop-card<?php echo $status_class; ?>" data-list-translation-item data-bo-table="<?php echo get_text($bo_table); ?>" data-wr-id="<?php echo (int) $shop['wr_id']; ?>">
            <a href="<?php echo $href; ?>" class="shop-card__link">
                <div class="shop-card__media">
                    <?php if ($thumb) { ?>
                    <img src="<?php echo $thumb; ?>" alt="<?php echo $shop['name']; ?>" class="shop-card__img" loading="lazy">
                    <?php } else { ?>
                    <div class="shop-card__img shop-card__img--empty" aria-hidden="true"></div>
                    <?php } ?>
                    <?php if ($shop['status']) { ?>
                    <span class="shop-card__status" data-translation-extra="status"><?php echo $shop['status']; ?></span>
                    <?php } ?>
                    <?php echo $event_badges; ?>
                </div>
                <div class="shop-card__body">
                    <?php if ($shop['category']) { ?><span class="shop-card__cate"><?php echo $shop['category']; ?></span><?php } ?>
                    <h3 class="shop-card__title" data-translation-list-title><?php echo $shop['name'] ?: get_text($row['subject']); ?></h3>
                    <?php echo $language_badges; ?>
                    <?php if ($shop['region']) { ?><p class="shop-card__region"><?php echo $shop['region']; ?></p><?php } ?>
                    <?php if ($shop['address']) { ?><p class="shop-card__addr"><?php echo $shop['address']; ?></p><?php } ?>
                </div>
            </a>
            <?php
            eottae_render_inquiry_buttons('card', array(
                'phone'         => $shop['phone'],
                'inquiry_code'  => $shop['inquiry_code'],
                'owner_mb_id'   => function_exists('eottae_shop_owner_mb_id_from_write') ? eottae_shop_owner_mb_id_from_write($row) : '',
                'shop_name'     => $shop['name'],
                'lat'           => $shop['lat'],
                'lng'           => $shop['lng'],
                'address'       => $shop['address'],
            ));
            ?>
        </article>
        <?php

        return ob_get_clean();
    }
}

if (!function_exists('eottae_shop_card_event_badges_html')) {
    function eottae_shop_card_event_badges_html($row, $bo_table = '')
    {
        static $assets_loaded = false;
        if (!$assets_loaded && function_exists('eottae_event_board_load_assets')) {
            eottae_event_board_load_assets();
            $assets_loaded = true;
        }

        if (!is_array($row)) {
            return '';
        }

        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($wr_id < 1) {
            return '';
        }

        if ($bo_table === '') {
            $bo_table = !empty($row['bo_table']) ? (string) $row['bo_table'] : (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop');
        }

        if (!function_exists('eottae_shop_event_thumb_badges_html') && is_file(G5_LIB_PATH.'/eottae-event.lib.php')) {
            include_once G5_LIB_PATH.'/eottae-event.lib.php';
        }

        if (!function_exists('eottae_shop_event_thumb_badges_html')) {
            return '';
        }

        return eottae_shop_event_thumb_badges_html($bo_table, $wr_id);
    }
}

if (!function_exists('eottae_shop_card_thumb')) {
    /**
     * 목록 카드 썸네일 — 지도 마커·listing과 동일 우선순위 (지도 전용 → GNUBoard thumb → 첨부 → 대표)
     */
    function eottae_shop_card_thumb($row, $bo_table = '')
    {
        if (!is_array($row)) {
            return '';
        }

        if ($bo_table === '') {
            $bo_table = !empty($row['bo_table']) ? (string) $row['bo_table'] : (defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop');
        }

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
        $wr_id = (int) ($row['wr_id'] ?? 0);
        if ($wr_id < 1) {
            return '';
        }

        $storage_bo = function_exists('eottae_shop_storage_bo_table')
            ? eottae_shop_storage_bo_table($bo_table)
            : $bo_table;

        if (function_exists('eottae_shop_map_thumb_get')) {
            $map_thumb = eottae_shop_map_thumb_get($storage_bo, $wr_id);
            if (!empty($map_thumb['url'])) {
                return $map_thumb['url'];
            }
        }

        if (!function_exists('get_list_thumbnail')) {
            include_once G5_LIB_PATH.'/thumbnail.lib.php';
        }
        if (function_exists('get_list_thumbnail')) {
            $t = get_list_thumbnail($storage_bo, $wr_id, 400, 400, false, true);
            if (!empty($t['src'])) {
                return $t['src'];
            }
        }

        if (!empty($row['file']['count']) && isset($row['file'][0]['path'], $row['file'][0]['file'])) {
            return $row['file'][0]['path'].'/'.$row['file'][0]['file'];
        }

        if (function_exists('eottae_shop_representative_image_url')) {
            $representative = eottae_shop_representative_image_url($storage_bo, $wr_id);
            if ($representative !== '') {
                return $representative;
            }
        }

        return '';
    }
}

if (!function_exists('eottae_shop_list_card_html')) {
    function eottae_shop_list_card_html($row, $bo_table = '')
    {
        if ($bo_table === '') {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }

        $shop = eottae_shop_from_write($row, $bo_table);
        $thumb = eottae_shop_card_thumb($row, $bo_table);
        if ($thumb !== '' && function_exists('eottae_map_public_url')) {
            $thumb = eottae_map_public_url($thumb);
        }
        $href = function_exists('eottae_shop_view_url')
            ? eottae_shop_view_url($shop['wr_id'], $bo_table)
            : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$shop['wr_id'];
        $summary = eottae_get_shop_review_summary((int) $shop['wr_id']);
        $shop_seo = function_exists('eottae_shop_seo_get') ? eottae_shop_seo_get($bo_table, (int) $shop['wr_id']) : array();
        $shop_intro = trim(get_text($shop_seo['meta_intro'] ?? ''));
        $snippet = $shop_intro !== ''
            ? (function_exists('cut_str') ? cut_str($shop_intro, 110, '…') : $shop_intro)
            : eottae_shop_list_snippet(isset($row['wr_content']) ? $row['wr_content'] : '');
        $snippet_is_intro = $shop_intro !== '';
        $save_count = isset($row['_eottae_save_count']) ? (int) $row['_eottae_save_count'] : 0;
        $badges = eottae_shop_list_card_badges($row, $summary, $save_count);
        $latest_review = isset($row['_eottae_latest_review']) ? trim((string) $row['_eottae_latest_review']) : '';
        $show_review_preview = $summary['count'] >= 3 && $latest_review !== '';
        $distance_label = '';
        if (isset($row['_eottae_distance_km'])) {
            $distance_label = eottae_shop_format_distance_km($row['_eottae_distance_km']);
        }
        $status_label = trim((string) $shop['status']);
        $status_open = $status_label === '영업중';
        $event_badges = eottae_shop_card_event_badges_html($row, $bo_table);
        $language_badges = function_exists('eottae_lang_short_badges_html')
            ? eottae_lang_short_badges_html($shop['available_languages'], 'shop-list-card__lang-badges')
            : '';

        ob_start();
        ?>
        <article class="shop-list-card" data-shop-card data-list-translation-item data-bo-table="<?php echo get_text($bo_table); ?>" data-wr-id="<?php echo (int) $shop['wr_id']; ?>" data-lat="<?php echo htmlspecialchars($shop['lat'], ENT_QUOTES, 'UTF-8'); ?>" data-lng="<?php echo htmlspecialchars($shop['lng'], ENT_QUOTES, 'UTF-8'); ?>">
            <a href="<?php echo $href; ?>" class="shop-list-card__thumb-wrap">
                <?php if ($thumb) { ?>
                <img src="<?php echo $thumb; ?>" alt="<?php echo $shop['name']; ?>" class="shop-list-card__thumb" loading="lazy">
                <?php } else { ?>
                <div class="shop-list-card__thumb shop-list-card__thumb--empty" aria-hidden="true"></div>
                <?php } ?>
                <?php if (!empty($badges['is_featured'])) { ?>
                <span class="shop-list-card__badge shop-list-card__badge--featured">최우수업체</span>
                <?php } else { ?>
                    <?php if ($badges['is_recommended']) { ?>
                <span class="shop-list-card__badge shop-list-card__badge--pick">추천</span>
                    <?php } ?>
                    <?php if ($badges['is_popular']) { ?>
                <span class="shop-list-card__badge shop-list-card__badge--hot">인기</span>
                    <?php } ?>
                <?php } ?>
                <?php echo $event_badges; ?>
            </a>
            <div class="shop-list-card__body">
                <div class="shop-list-card__head">
                    <h3 class="shop-list-card__title"><a href="<?php echo $href; ?>" data-translation-list-title><?php echo $shop['name'] ?: get_text($row['subject']); ?></a></h3>
                    <div class="shop-list-card__meta">
                        <p class="shop-list-card__rating">
                            <span class="shop-list-card__stars">★ <?php echo number_format($summary['average'], 1); ?></span>
                            <span class="shop-list-card__reviews">리뷰 <?php echo number_format($summary['count']); ?></span>
                        </p>
                        <div class="shop-list-card__tags">
                            <?php if ($distance_label !== '') { ?>
                            <span class="shop-list-card__tag shop-list-card__distance" data-shop-distance data-lat="<?php echo htmlspecialchars($shop['lat'], ENT_QUOTES, 'UTF-8'); ?>" data-lng="<?php echo htmlspecialchars($shop['lng'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $distance_label; ?></span>
                            <?php } elseif ($shop['region']) { ?>
                            <span class="shop-list-card__tag shop-list-card__tag--region"><?php echo $shop['region']; ?></span>
                            <?php } ?>
                            <?php if ($status_label !== '') { ?>
                            <span class="shop-list-card__tag shop-list-card__tag--status<?php echo $status_open ? ' shop-list-card__tag--status-open' : ''; ?>" data-translation-extra="status"><?php echo $status_label; ?></span>
                            <?php } ?>
                            <?php if ($shop['category']) { ?><span class="shop-list-card__tag shop-list-card__tag--cate"><?php echo $shop['category']; ?></span><?php } ?>
                            <?php echo $language_badges; ?>
                        </div>
                    </div>
                </div>
                <?php if ($snippet !== '') { ?>
                <p class="shop-list-card__desc"<?php echo $snippet_is_intro ? ' data-translation-extra="intro"' : ' data-translation-list-snippet'; ?>><?php echo $snippet; ?></p>
                <?php } ?>
                <?php if ($show_review_preview) { ?>
                <p class="shop-list-card__review-preview"><span class="shop-list-card__review-quote" aria-hidden="true">“</span><?php echo $latest_review; ?><span class="shop-list-card__review-quote" aria-hidden="true">”</span></p>
                <?php } ?>
                <?php
                eottae_render_inquiry_buttons('list', array(
                    'phone'         => $shop['phone'],
                    'inquiry_code'  => $shop['inquiry_code'],
                    'owner_mb_id'   => function_exists('eottae_shop_owner_mb_id_from_write') ? eottae_shop_owner_mb_id_from_write($row) : '',
                    'shop_name'     => $shop['name'],
                    'lat'           => $shop['lat'],
                    'lng'           => $shop['lng'],
                    'address'       => $shop['address'],
                    'share_url'     => $href,
                ));
                ?>
            </div>
        </article>
        <?php

        return ob_get_clean();
    }
}
