<?php
chdir(dirname(__DIR__));
if (!isset($_GET['bo_table'])) {
    $_GET['bo_table'] = 'plaza';
}
if (!isset($_REQUEST['bo_table'])) {
    $_REQUEST['bo_table'] = 'plaza';
}
include_once('./bbs/board.php');
