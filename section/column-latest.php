<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);

include_once G5_PATH.'/components/eottae/column-home-feed.php';

echo eottae_column_home_feed_html();
