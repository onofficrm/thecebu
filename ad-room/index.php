<?php
/**
 * 업체 광고방
 * URL: /ad-room/
 */
chdir(dirname(__DIR__));
$_GET['bo_table'] = 'adroom';
$_REQUEST['bo_table'] = 'adroom';
include_once('./bbs/board.php');
