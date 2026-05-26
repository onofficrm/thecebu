<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (function_exists('eottae_gallery_board_ensure_settings')) {
    eottae_gallery_board_ensure_settings();
}

if (function_exists('eottae_is_gallery_board') && eottae_is_gallery_board($board)) {
    global $board;
    $board['bo_upload_size'] = max((int) ($board['bo_upload_size'] ?? 0), eottae_gallery_upload_size());
}
