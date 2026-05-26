<?php
/**
 * 빌더/외부 페이지용 회원 스포트라이트 위젯
 *
 * 사용 예:
 *   include_once G5_PATH.'/widgets/member-growth-spotlight.php';
 */
if (!defined('_GNUBOARD_')) {
    $widget_root = realpath(__DIR__.'/..');
    if ($widget_root && is_file($widget_root.'/common.php')) {
        chdir($widget_root);
        include_once $widget_root.'/common.php';
    }
}

if (!defined('_GNUBOARD_')) {
    return;
}

include_once G5_PATH.'/components/eottae/member-growth-home.php';

echo eottae_member_growth_home_spotlight_html();
