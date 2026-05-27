<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';

$join_id = isset($_GET['join_id']) ? (int) $_GET['join_id'] : 0;
goto_url(eottae_golf_join_detail_url($join_id).'#golf-join-applicants');
