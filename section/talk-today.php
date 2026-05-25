<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH.'/components/eottae/talk-home-feed.php';

echo eottae_talkroom_home_feed_html(8);
