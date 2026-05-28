<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_gnb_render_nav_items')) {
    /**
     * @param array<int, array<string, mixed>> $items
     * @param string                           $context desktop|mobile
     */
    function eottae_gnb_render_nav_items(array $items, $context = 'desktop')
    {
        if (!function_exists('eottae_gnb_nav_item_active')) {
            include_once G5_LIB_PATH.'/eottae-gnb-menu.lib.php';
        }

        foreach ($items as $item) {
            if (!empty($item['desktop_action'])) {
                continue;
            }

            $children = !empty($item['children']) && is_array($item['children']) ? $item['children'] : array();
            $active = eottae_gnb_nav_item_active($item);
            $href = eottae_gnb_nav_item_href($item);
            $label = isset($item['label']) ? $item['label'] : '';
            if (is_callable($label)) {
                $label = (string) call_user_func($label);
            }

            if ($context === 'mobile') {
                eottae_gnb_render_mobile_nav_item($item, $children, $href, $label, $active);

                continue;
            }

            if ($children) {
                ?>
                <div class="eottae-gnb-header__nav-item has-children<?php echo $active ? ' is-active' : ''; ?>">
                    <a href="<?php echo $href; ?>" class="<?php echo eottae_gnb_nav_link_classes($item, 'desktop', $active); ?> eottae-gnb-header__nav-link--parent">
                        <?php echo get_text($label); ?>
                        <span class="eottae-gnb-header__nav-caret" aria-hidden="true"></span>
                    </a>
                    <div class="eottae-gnb-header__submenu" role="menu">
                        <?php foreach ($children as $child) {
                            $child_active = eottae_gnb_nav_item_active($child);
                            $child_href = eottae_gnb_nav_item_href($child);
                            $child_label = isset($child['label']) ? $child['label'] : '';
                            ?>
                        <a href="<?php echo $child_href; ?>" class="eottae-gnb-header__submenu-link<?php echo $child_active ? ' is-active' : ''; ?>" role="menuitem">
                            <?php echo get_text($child_label); ?>
                        </a>
                        <?php } ?>
                    </div>
                </div>
                <?php

                continue;
            }

            $link_class = function_exists('eottae_gnb_nav_link_classes')
                ? eottae_gnb_nav_link_classes($item, 'desktop', $active)
                : 'eottae-gnb-header__nav-link'.($active ? ' is-active' : '');
            ?>
            <a href="<?php echo $href; ?>" class="<?php echo $link_class; ?>">
                <?php echo get_text($label); ?>
            </a>
            <?php
        }
    }
}

if (!function_exists('eottae_gnb_render_mobile_nav_item')) {
    function eottae_gnb_render_mobile_nav_item(array $item, array $children, $href, $label, $active)
    {
        $link_class = function_exists('eottae_gnb_nav_link_classes')
            ? eottae_gnb_nav_link_classes($item, 'mobile', $active)
            : 'eottae-gnb-header__mobile-link'.($active ? ' is-active' : '');

        if (!$children) {
            ?>
            <a href="<?php echo $href; ?>" class="<?php echo $link_class; ?>">
                <?php echo get_text($label); ?>
            </a>
            <?php

            return;
        }
        ?>
        <details class="eottae-gnb-header__mobile-group"<?php echo $active ? ' open' : ''; ?>>
            <summary class="<?php echo $link_class; ?> eottae-gnb-header__mobile-summary">
                <span><?php echo get_text($label); ?></span>
            </summary>
            <div class="eottae-gnb-header__mobile-children">
                <?php foreach ($children as $child) {
                    $child_active = eottae_gnb_nav_item_active($child);
                    $child_href = eottae_gnb_nav_item_href($child);
                    $child_label = isset($child['label']) ? $child['label'] : '';
                    ?>
                <a href="<?php echo $child_href; ?>" class="eottae-gnb-header__mobile-child-link<?php echo $child_active ? ' is-active' : ''; ?>">
                    <?php echo get_text($child_label); ?>
                </a>
                <?php } ?>
            </div>
        </details>
        <?php
    }
}
