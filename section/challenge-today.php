<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH.'/components/eottae/challenge-home-feed.php';

echo eottae_challenge_home_feed_html(3);
