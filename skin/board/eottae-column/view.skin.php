<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$wr_id = isset($view['wr_id']) ? (int) $view['wr_id'] : 0;
if ($wr_id > 0 && function_exists('eottae_column_view_url')) {
    goto_url(eottae_column_view_url($wr_id));
}
goto_url(function_exists('eottae_column_list_url') ? eottae_column_list_url() : G5_URL.'/column/');
