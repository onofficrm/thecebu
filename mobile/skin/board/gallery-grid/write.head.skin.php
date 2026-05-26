<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (function_exists('eottae_gallery_write_prepare')) {
    eottae_gallery_write_prepare();
}

if (function_exists('eottae_load_media_board_assets')) {
    eottae_load_media_board_assets();
}
