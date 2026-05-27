<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_board_dhtml_editor_use')) {
    function eottae_board_dhtml_editor_use()
    {
        return !G5_IS_MOBILE || (defined('G5_IS_MOBILE_DHTML_USE') && G5_IS_MOBILE_DHTML_USE);
    }
}

if (!function_exists('eottae_board_dhtml_editor_enabled')) {
    function eottae_board_dhtml_editor_enabled($board = null)
    {
        global $config, $member;

        if (empty($config['cf_editor'])) {
            return false;
        }

        if (!eottae_board_dhtml_editor_use()) {
            return false;
        }

        if (!is_array($board)) {
            global $board;
        }

        if (!is_array($board) || empty($board['bo_table'])) {
            return false;
        }

        if (empty($board['bo_use_dhtml_editor'])) {
            return false;
        }

        $html_level = isset($board['bo_html_level']) ? (int) $board['bo_html_level'] : 1;
        $mb_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

        return $mb_level >= $html_level;
    }
}

if (!function_exists('eottae_board_force_dhtml_editor')) {
    /**
     * 글쓰기·수정 화면 — 모든 게시판 DHTML 에디터 사용
     */
    function eottae_board_force_dhtml_editor()
    {
        global $board;

        if (!is_array($board) || empty($board['bo_table'])) {
            return;
        }

        $board['bo_use_dhtml_editor'] = 1;
    }
}

if (!function_exists('eottae_board_write_content_for_editor')) {
    /**
     * 수정 시 SmartEditor에 넣을 HTML (이스케이프된 plain text 방지)
     *
     * @param string               $content
     * @param array<string, mixed> $write
     * @param string               $w
     */
    function eottae_board_write_content_for_editor($content, $write, $w)
    {
        if ($w !== 'u' || !is_array($write) || empty($write['wr_content'])) {
            return $content;
        }

        $raw = (string) $write['wr_content'];
        if (function_exists('html_purifier')) {
            $raw = html_purifier($raw);
        }

        return $raw;
    }
}
