<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_board_write_enqueue_mobile_css')) {
    /**
     * 게시판 글쓰기·수정 — 모바일 입력·에디터·하단 버튼 보강
     */
    function eottae_board_write_enqueue_mobile_css()
    {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;

        $path = G5_PATH.'/css/eottae-board-write-mobile.css';
        if (!is_file($path)) {
            return;
        }

        add_stylesheet(
            '<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-board-write-mobile.css?ver='.(int) filemtime($path).'">',
            99
        );
    }
}

if (!function_exists('eottae_board_write_is_target_table')) {
    /**
     * 모바일 글쓰기 UI 강화 대상 게시판
     */
    function eottae_board_write_is_target_table($bo_table)
    {
        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '') {
            return false;
        }

        if (function_exists('eottae_is_job_board') && eottae_is_job_board($bo_table)) {
            return true;
        }
        if (function_exists('eottae_is_estate_board') && eottae_is_estate_board($bo_table)) {
            return true;
        }
        if (function_exists('eottae_adroom_board_table') && $bo_table === eottae_adroom_board_table()) {
            return true;
        }

        return false;
    }
}
