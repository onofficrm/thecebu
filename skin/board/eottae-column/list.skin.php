<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (function_exists('eottae_column_list_url')) {
    goto_url(eottae_column_list_url());
}
goto_url(G5_URL.'/column/');
