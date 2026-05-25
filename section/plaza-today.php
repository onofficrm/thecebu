<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH.'/components/eottae/plaza-home-feed.php';

echo eottae_plaza_home_feed_html(5);
