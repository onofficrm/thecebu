<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_SKIN_PATH.'/board/_inc/g5b-thumb.php');

include_once(G5_SKIN_PATH.'/board/_inc/g5b-youtube.php');

if (!function_exists('g5b_gallery_relative_time')) {
    function g5b_gallery_relative_time($datetime)
    {
        return function_exists('g5b_youtube_relative_time')
            ? g5b_youtube_relative_time($datetime)
            : '';
    }
}

/**
 * 글보기 사이드바용 다른 갤러리 글 목록
 */
if (!function_exists('g5b_gallery_get_related_writes')) {
function g5b_gallery_get_related_writes($bo_table, $exclude_wr_id, $limit = 20)
{
    if (function_exists('g5b_youtube_get_related_writes')) {
        return g5b_youtube_get_related_writes($bo_table, $exclude_wr_id, $limit);
    }

    global $g5;

    $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
    $exclude_wr_id = (int) $exclude_wr_id;
    $limit = max(1, min(30, (int) $limit));
    if ($bo_table === '') {
        return array();
    }

    $write_table = $g5['write_prefix'].$bo_table;
    $result = sql_query(" select wr_id, ca_name, wr_subject, wr_hit, wr_datetime, wr_name
        from {$write_table}
        where wr_is_comment = 0 and wr_id != '{$exclude_wr_id}'
        order by wr_id desc
        limit {$limit} ");
    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = $row;
    }

    return $items;
}
}

if (!function_exists('g5b_gallery_format_views')) {
function g5b_gallery_format_views($hit)
{
    $hit = (int) $hit;
    if ($hit <= 0) {
        return '';
    }

    return '조회수 '.number_format($hit).'회';
}
}

/**
 * 사이드바 갤러리 카드 HTML
 */
if (!function_exists('g5b_gallery_sidebar_item_html')) {
function g5b_gallery_sidebar_item_html($row, $bo_table, $current_wr_id = 0)
{
    if (!is_array($row) || empty($row['wr_id'])) {
        return '';
    }

    $wr_id = (int) $row['wr_id'];
    if ($current_wr_id > 0 && $wr_id === (int) $current_wr_id) {
        return '';
    }

    $href = get_pretty_url($bo_table, $wr_id);
    $title = isset($row['wr_subject']) ? get_text(strip_tags($row['wr_subject'])) : '';
    $author = isset($row['wr_name']) ? get_text(strip_tags($row['wr_name'])) : '';
    $views = g5b_gallery_format_views(isset($row['wr_hit']) ? $row['wr_hit'] : 0);
    $rel_time = g5b_gallery_relative_time(isset($row['wr_datetime']) ? $row['wr_datetime'] : '');
    $meta_parts = array();
    if ($author !== '') {
        $meta_parts[] = $author;
    }
    if ($views !== '') {
        $meta_parts[] = $views;
    }
    if ($rel_time !== '') {
        $meta_parts[] = $rel_time;
    }
    $meta_line = implode(' • ', $meta_parts);

    $thumb_html = g5b_list_thumb_html($bo_table, $wr_id, 336, 189, $title, false, false, true);

    return '<li class="board-gal-sidebar-item">'
        .'<a href="'.htmlspecialchars($href, ENT_QUOTES, 'UTF-8').'" class="board-gal-sidebar-item__link">'
        .'<span class="board-gal-sidebar-item__media">'.$thumb_html.'</span>'
        .'<span class="board-gal-sidebar-item__info">'
        .'<span class="board-gal-sidebar-item__title">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</span>'
        .($meta_line !== '' ? '<span class="board-gal-sidebar-item__meta">'.htmlspecialchars($meta_line, ENT_QUOTES, 'UTF-8').'</span>' : '')
        .'</span>'
        .'</a>'
        .'</li>';
}
}
