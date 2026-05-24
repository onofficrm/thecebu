<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH.'/extend/eottae.config.php';
include_once G5_LIB_PATH.'/eottae.lib.php';

if (eottae_should_load_assets()) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae.css">', 5);
    add_javascript('<script src="'.G5_JS_URL.'/eottae.js" defer></script>', 20);
}

if (isset($board) && is_array($board) && isset($board['bo_skin'])) {
    $skin = (string) $board['bo_skin'];
    if (strpos($skin, 'eottae-') === 0) {
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/g5b-board.css">', 4);
    }
}
