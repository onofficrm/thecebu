<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_shop_card_html')) {
    function eottae_shop_card_html($row, $bo_table = '')
    {
        if (!is_array($row)) {
            return '';
        }

        if ($bo_table === '') {
            $bo_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
        }

        $shop = eottae_shop_from_write($row);
        $thumb = '';
        if (!empty($row['file']['count']) && isset($row['file'][0]['path'], $row['file'][0]['file'])) {
            $thumb = $row['file'][0]['path'].'/'.$row['file'][0]['file'];
        } elseif (function_exists('get_list_thumbnail') && !empty($row['bo_table'])) {
            $t = get_list_thumbnail($row['bo_table'], $row['wr_id'], 400, 300);
            if (!empty($t['src'])) {
                $thumb = $t['src'];
            }
        }

        $href = isset($row['href']) ? $row['href'] : G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$shop['wr_id'];
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
