<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_is_free_board')) {
    function eottae_is_free_board($bo_table)
    {
        if (!function_exists('eottae_free_board_table')) {
            return false;
        }

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);

        return $bo_table !== '' && $bo_table === eottae_free_board_table();
    }
}

if (!function_exists('eottae_free_board_skin')) {
    function eottae_free_board_skin()
    {
        return 'eottae-community';
    }
}

if (!function_exists('eottae_ensure_free_board_skin')) {
    /**
     * 자유게시판 DB 스킨·첨부 설정을 커뮤니티형(eottae-community)으로 맞춤
     */
    function eottae_ensure_free_board_skin()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        global $g5, $board;

        $bo_table = function_exists('eottae_free_board_table') ? eottae_free_board_table() : 'free';
        if ($bo_table === '' || empty($g5['board_table'])) {
            return;
        }

        $row = sql_fetch("
            select bo_skin, bo_mobile_skin, bo_upload_count
            from {$g5['board_table']}
            where bo_table = '".sql_escape_string($bo_table)."'
        ");
        if (!$row) {
            return;
        }

        $skin = eottae_free_board_skin();
        $limit = function_exists('eottae_community_photo_limit') ? eottae_community_photo_limit() : 7;
        $upload = max($limit, (int) ($row['bo_upload_count'] ?? 0));

        if ((string) $row['bo_skin'] === $skin
            && (string) $row['bo_mobile_skin'] === $skin
            && (int) $row['bo_upload_count'] >= $limit) {
            return;
        }

        sql_query("
            update {$g5['board_table']} set
                bo_skin = '".sql_escape_string($skin)."',
                bo_mobile_skin = '".sql_escape_string($skin)."',
                bo_upload_count = '{$upload}'
            where bo_table = '".sql_escape_string($bo_table)."'
        ", false);

        if (is_array($board) && isset($board['bo_table']) && $board['bo_table'] === $bo_table) {
            $board['bo_skin'] = $skin;
            $board['bo_mobile_skin'] = $skin;
            $board['bo_upload_count'] = $upload;
        }
    }
}

if (!function_exists('eottae_apply_free_board_skin_runtime')) {
    /**
     * common.php 스킨 경로 확정 후 extend에서 재적용 (기존 basic-clean → eottae-community)
     */
    function eottae_apply_free_board_skin_runtime()
    {
        global $board, $bo_table, $board_skin_path, $board_skin_url;

        if (empty($bo_table) || !eottae_is_free_board($bo_table)) {
            return;
        }

        eottae_ensure_free_board_skin();

        if (!is_array($board) || empty($board['bo_table'])) {
            return;
        }

        $skin = G5_IS_MOBILE ? (string) $board['bo_mobile_skin'] : (string) $board['bo_skin'];
        if ($skin !== eottae_free_board_skin()) {
            return;
        }

        $board_skin_path = get_skin_path('board', $skin);
        $board_skin_url = get_skin_url('board', $skin);
    }
}
