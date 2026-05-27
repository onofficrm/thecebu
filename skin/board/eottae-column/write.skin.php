<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

$wr_id = isset($wr_id) ? (int) $wr_id : 0;
$url = function_exists('eottae_column_write_url') ? eottae_column_write_url($wr_id) : G5_URL.'/page/eottae-column-write.php';
goto_url($url);
