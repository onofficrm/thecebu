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

        ob_start();
        ?>
        <article class="shop-card<?php echo $status_class; ?>">
            <a href="<?php echo $href; ?>" class="shop-card__link">
                <div class="shop-card__media">
                    <?php if ($thumb) { ?>
                    <img src="<?php echo $thumb; ?>" alt="<?php echo $shop['name']; ?>" class="shop-card__img" loading="lazy">
                    <?php } else { ?>
                    <div class="shop-card__img shop-card__img--empty" aria-hidden="true"></div>
                    <?php } ?>
                    <?php if ($shop['status']) { ?>
                    <span class="shop-card__status"><?php echo $shop['status']; ?></span>
                    <?php } ?>
                </div>
                <div class="shop-card__body">
                    <?php if ($shop['category']) { ?><span class="shop-card__cate"><?php echo $shop['category']; ?></span><?php } ?>
                    <h3 class="shop-card__title"><?php echo $shop['name'] ?: get_text($row['subject']); ?></h3>
                    <?php if ($shop['region']) { ?><p class="shop-card__region"><?php echo $shop['region']; ?></p><?php } ?>
                    <?php if ($shop['address']) { ?><p class="shop-card__addr"><?php echo $shop['address']; ?></p><?php } ?>
                </div>
            </a>
            <?php
            eottae_render_inquiry_buttons('card', array(
                'phone'         => $shop['phone'],
                'inquiry_code'  => $shop['inquiry_code'],
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

if (!function_exists('eottae_shop_card_thumb')) {
    function eottae_shop_card_thumb($row, $bo_table = '')
    {
        if (!is_array($row)) {
            return '';
        }

        if ($bo_table === '') {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }

        if (function_exists('get_list_thumbnail')) {
            $table = !empty($row['bo_table']) ? $row['bo_table'] : $bo_table;
            if (function_exists('eottae_shop_storage_bo_table')) {
                $table = eottae_shop_storage_bo_table($table);
            }
            $t = get_list_thumbnail($table, $row['wr_id'], 400, 400, false, true);
            if (!empty($t['src'])) {
                return $t['src'];
            }
        }

        if (!empty($row['file']['count']) && isset($row['file'][0]['path'], $row['file'][0]['file'])) {
            return $row['file'][0]['path'].'/'.$row['file'][0]['file'];
        }

        if (function_exists('eottae_shop_listing_thumb_url')) {
            $thumb = eottae_shop_listing_thumb_url($bo_table, (int) $row['wr_id'], $row);
            if ($thumb !== '') {
                return $thumb;
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
        $snippet = eottae_shop_list_snippet(isset($row['wr_content']) ? $row['wr_content'] : '');
        $is_recommended = $summary['average'] >= 4.5 && $summary['count'] > 0;
        $is_ad = isset($row['wr_link2']) && stripos((string) $row['wr_link2'], 'ad') !== false;
        $distance_label = '';
        if (isset($row['_eottae_distance_km'])) {
            $distance_label = eottae_shop_format_distance_km($row['_eottae_distance_km']);
        }

        ob_start();
        ?>
        <article class="shop-list-card" data-shop-card data-wr-id="<?php echo (int) $shop['wr_id']; ?>" data-lat="<?php echo htmlspecialchars($shop['lat'], ENT_QUOTES, 'UTF-8'); ?>" data-lng="<?php echo htmlspecialchars($shop['lng'], ENT_QUOTES, 'UTF-8'); ?>">
            <a href="<?php echo $href; ?>" class="shop-list-card__thumb-wrap">
                <?php if ($thumb) { ?>
                <img src="<?php echo $thumb; ?>" alt="<?php echo $shop['name']; ?>" class="shop-list-card__thumb" loading="lazy">
                <?php } else { ?>
                <div class="shop-list-card__thumb shop-list-card__thumb--empty" aria-hidden="true"></div>
                <?php } ?>
                <?php if ($is_ad) { ?>
                <span class="shop-list-card__badge shop-list-card__badge--ad">광고</span>
                <?php } elseif ($is_recommended) { ?>
                <span class="shop-list-card__badge shop-list-card__badge--pick">추천</span>
                <?php } ?>
            </a>
            <div class="shop-list-card__body">
                <div class="shop-list-card__head">
                    <h3 class="shop-list-card__title"><a href="<?php echo $href; ?>"><?php echo $shop['name'] ?: get_text($row['subject']); ?></a></h3>
                    <div class="shop-list-card__tags">
                        <?php if ($shop['category']) { ?><span class="shop-list-card__tag shop-list-card__tag--cate"><?php echo $shop['category']; ?></span><?php } ?>
                        <?php if ($distance_label !== '') { ?>
                        <span class="shop-list-card__tag shop-list-card__distance" data-shop-distance data-lat="<?php echo htmlspecialchars($shop['lat'], ENT_QUOTES, 'UTF-8'); ?>" data-lng="<?php echo htmlspecialchars($shop['lng'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $distance_label; ?></span>
                        <?php } elseif ($shop['region']) { ?>
                        <span class="shop-list-card__tag shop-list-card__tag--region"><?php echo $shop['region']; ?></span>
                        <?php } ?>
                    </div>
                </div>
                <?php if ($snippet !== '') { ?>
                <p class="shop-list-card__desc"><?php echo $snippet; ?></p>
                <?php } ?>
                <p class="shop-list-card__rating">
                    <span class="shop-list-card__stars">★ <?php echo number_format($summary['average'], 1); ?></span>
                    <span class="shop-list-card__reviews">리뷰 <?php echo number_format($summary['count']); ?></span>
                </p>
                <?php
                eottae_render_inquiry_buttons('list', array(
                    'phone'         => $shop['phone'],
                    'inquiry_code'  => $shop['inquiry_code'],
                    'lat'           => $shop['lat'],
                    'lng'           => $shop['lng'],
                    'address'       => $shop['address'],
                ));
                ?>
            </div>
        </article>
        <?php

        return ob_get_clean();
    }
}
