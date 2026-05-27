<?php
/**
 * 골프조인 상세
 * URL: /golf-join/view.php?join_id=101
 *      /golf-join/101 (Rewrite)
 */
chdir(dirname(__DIR__));

if (!isset($_GET['join_id']) || (int) $_GET['join_id'] < 1) {
    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    if (preg_match('#/golf-join/([0-9]+)#', $uri, $m)) {
        $_GET['join_id'] = (int) $m[1];
    }
}

include_once('./page/eottae-golf-join-detail.php');
