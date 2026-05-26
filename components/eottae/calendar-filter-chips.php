<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_calendar_filter_chips_html')) {
    function eottae_calendar_filter_chips_html($active_category = '', array $params = array())
    {
        $active_category = preg_replace('/[^a-z_]/', '', (string) $active_category);
        $categories = eottae_calendar_category_options();
        $base_params = $params;
        unset($base_params['category']);

        ob_start();
        ?>
        <nav class="sebu-cal-filter" aria-label="일정 분류 필터">
            <div class="sebu-cal-filter__scroll">
                <?php
                $all_params = $base_params;
                unset($all_params['category']);
                $all_href = eottae_calendar_list_url($all_params);
                ?>
                <a href="<?php echo $all_href; ?>" class="sebu-cal-filter__chip<?php echo $active_category === '' ? ' is-active' : ''; ?>">전체</a>
                <?php foreach ($categories as $code => $label) {
                    $chip_params = $base_params;
                    $chip_params['category'] = $code;
                    $chip_href = eottae_calendar_list_url($chip_params);
                    ?>
                <a href="<?php echo $chip_href; ?>" class="sebu-cal-filter__chip sebu-cal-filter__chip--<?php echo get_text($code); ?><?php echo $active_category === $code ? ' is-active' : ''; ?>"><?php echo get_text($label); ?></a>
                <?php } ?>
            </div>
        </nav>
        <?php

        return (string) ob_get_clean();
    }
}
