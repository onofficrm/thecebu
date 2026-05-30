<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_gnb_nav_item_label')) {
    function eottae_gnb_nav_item_label($label)
    {
        if (is_callable($label)) {
            return (string) call_user_func($label);
        }

        return (string) $label;
    }
}

if (!function_exists('eottae_gnb_render_nav_items')) {
    /**
     * @param array<int, array<string, mixed>> $items
     * @param string                           $context desktop|mobile
     */
    function eottae_gnb_render_nav_items(array $items, $context = 'desktop')
    {
        if ($context === 'desktop') {
            eottae_gnb_render_desktop_nav_primary($items);

            return;
        }

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
            $label = eottae_gnb_nav_item_label($item['label'] ?? '');

            eottae_gnb_render_mobile_nav_item($item, $children, $href, $label, $active);
        }
    }
}

if (!function_exists('eottae_gnb_render_desktop_nav_primary')) {
    /**
     * @param array<int, array<string, mixed>> $items
     */
    function eottae_gnb_render_desktop_nav_primary(array $items)
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
            $label = eottae_gnb_nav_item_label($item['label'] ?? '');
            $key = isset($item['key']) ? (string) $item['key'] : '';

            $link_class = function_exists('eottae_gnb_nav_link_classes')
                ? eottae_gnb_nav_link_classes($item, 'desktop', $active)
                : 'eottae-gnb-header__nav-link'.($active ? ' is-active' : '');

            if ($children) {
                $link_class .= ' eottae-gnb-header__nav-link--parent';
            }
            ?>
            <a
                href="<?php echo $href; ?>"
                class="<?php echo $link_class; ?>"
                <?php echo function_exists('eottae_i18n_text_attrs') ? eottae_i18n_text_attrs($label) : ''; ?>
                <?php echo $key !== '' ? ' data-mega-key="'.get_text($key).'"' : ''; ?>
                <?php echo $children ? ' aria-haspopup="true"' : ''; ?>
            >
                <span class="eottae-gnb-header__nav-caret" aria-hidden="true"></span>
                <?php echo get_text($label); ?>
            </a>
            <?php
        }
    }
}

if (!function_exists('eottae_gnb_render_desktop_mega_panel')) {
    /**
     * @param array<int, array<string, mixed>> $items
     */
    function eottae_gnb_render_desktop_mega_panel(array $items)
    {
        if (!function_exists('eottae_gnb_nav_item_active')) {
            include_once G5_LIB_PATH.'/eottae-gnb-menu.lib.php';
        }

        $columns = array();
        foreach ($items as $item) {
            if (!empty($item['desktop_action'])) {
                continue;
            }
            if (empty($item['children']) || !is_array($item['children'])) {
                continue;
            }
            $columns[] = $item;
        }

        if (!$columns) {
            return;
        }
        ?>
        <div class="eottae-gnb-header__mega-panel" id="eottaeGnbMegaPanel" data-eottae-gnb-mega aria-label="전체 서브메뉴" aria-hidden="true">
            <div class="eottae-gnb-header__mega-inner">
                <?php foreach ($columns as $item) {
                    $key = isset($item['key']) ? (string) $item['key'] : '';
                    $parent_active = eottae_gnb_nav_item_active($item);
                    $parent_href = eottae_gnb_nav_item_href($item);
                    $parent_label = eottae_gnb_nav_item_label($item['label'] ?? '');
                    ?>
                <div class="eottae-gnb-header__mega-col"<?php echo $key !== '' ? ' data-mega-key="'.get_text($key).'"' : ''; ?>>
                    <a href="<?php echo $parent_href; ?>" class="eottae-gnb-header__mega-col-title<?php echo $parent_active ? ' is-active' : ''; ?>"<?php echo function_exists('eottae_i18n_text_attrs') ? eottae_i18n_text_attrs($parent_label) : ''; ?>>
                        <?php echo get_text($parent_label); ?>
                    </a>
                    <ul class="eottae-gnb-header__mega-list">
                        <?php foreach ($item['children'] as $child) {
                            $child_active = eottae_gnb_nav_item_active($child);
                            $child_href = eottae_gnb_nav_item_href($child);
                            $child_label = eottae_gnb_nav_item_label($child['label'] ?? '');
                            ?>
                        <li>
                            <a href="<?php echo $child_href; ?>" class="eottae-gnb-header__mega-link<?php echo $child_active ? ' is-active' : ''; ?>"<?php echo function_exists('eottae_i18n_text_attrs') ? eottae_i18n_text_attrs($child_label) : ''; ?>>
                                <?php echo get_text($child_label); ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php
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
            <a href="<?php echo $href; ?>" class="<?php echo $link_class; ?>"<?php echo function_exists('eottae_i18n_text_attrs') ? eottae_i18n_text_attrs($label) : ''; ?>>
                <?php echo get_text($label); ?>
            </a>
            <?php

            return;
        }
        ?>
        <details class="eottae-gnb-header__mobile-group"<?php echo $active ? ' open' : ''; ?>>
            <summary class="<?php echo $link_class; ?> eottae-gnb-header__mobile-summary">
                <span<?php echo function_exists('eottae_i18n_text_attrs') ? eottae_i18n_text_attrs($label) : ''; ?>><?php echo get_text($label); ?></span>
            </summary>
            <div class="eottae-gnb-header__mobile-children">
                <?php foreach ($children as $child) {
                    $child_active = eottae_gnb_nav_item_active($child);
                    $child_href = eottae_gnb_nav_item_href($child);
                    $child_label = eottae_gnb_nav_item_label($child['label'] ?? '');
                    ?>
                <a href="<?php echo $child_href; ?>" class="eottae-gnb-header__mobile-child-link<?php echo $child_active ? ' is-active' : ''; ?>"<?php echo function_exists('eottae_i18n_text_attrs') ? eottae_i18n_text_attrs($child_label) : ''; ?>>
                    <?php echo get_text($child_label); ?>
                </a>
                <?php } ?>
            </div>
        </details>
        <?php
    }
}
