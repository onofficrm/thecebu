<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (empty($is_report_board_list)) {
    return;
}

$report_filter_is_admin = !empty($GLOBALS['eottae_report_list_is_admin']);
$report_filter_tabs = function_exists('eottae_report_list_filter_tabs')
    ? eottae_report_list_filter_tabs($bo_table, $report_filter_is_admin)
    : array();

if (!$report_filter_tabs) {
    return;
}
?>
<nav class="report-status-filter" aria-label="제보 상태 필터">
    <?php foreach ($report_filter_tabs as $tab) { ?>
    <a href="<?php echo get_text($tab['href']); ?>" class="report-status-filter__item<?php echo !empty($tab['active']) ? ' is-active' : ''; ?>">
        <?php echo get_text($tab['label']); ?>
    </a>
    <?php } ?>
</nav>
