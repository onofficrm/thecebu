<?php
/**
 * 서브페이지 공통 부트스트랩
 * - 직접 URL 접근: /page/about.php
 * - include 경로: dirname 기준 프로젝트 루트 _common.php 로드
 */
if (isset($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === '_init.php') {
    exit;
}

if (!defined('_GNUBOARD_')) {
    chdir(dirname(__FILE__).'/..');
    include_once(dirname(__FILE__).'/../_common.php');
}

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * head/tail include 시 그누보드 전역을 함수 스코프로 가져옴
 */
function g5_page_prepare_board()
{
    global $board;

    if (!isset($board) || !is_array($board)) {
        $board = array('bo_use_dhtml_editor' => 0);
    }
}

/**
 * 서브페이지 시작 (head.php)
 * @param string $title 브라우저·container_title용
 */
function g5_page_start($title)
{
    global $g5, $config, $member, $is_member, $is_admin, $board, $bo_table, $sca;
    global $g5_css_brand, $begin_time, $g5_debug;

    g5_page_prepare_board();
    $g5['title'] = $title;
    include_once(G5_PATH.'/head.php');
}

/**
 * 서브페이지 종료 (tail.php)
 */
function g5_page_end()
{
    global $g5, $config, $member, $is_member, $is_admin, $board, $bo_table, $sca;
    global $g5_css_brand, $begin_time, $g5_debug;

    g5_page_prepare_board();
    include_once(G5_PATH.'/tail.php');
}
