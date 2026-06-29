<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_SKIN_PATH.'/board/_inc/g5b-fallback.php');

/**
 * YouTube URL → 영상 ID (11자, 영숫자·_- 만 허용)
 */
function g5b_youtube_id_from_url($url)
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    $patterns = array(
        '#(?:https?://)?(?:www\.|m\.)?youtube\.com/watch\?(?:[^&\s]*&)*v=([a-zA-Z0-9_-]{11})#i',
        '#(?:https?://)?(?:www\.|m\.)?youtube\.com/watch\?v=([a-zA-Z0-9_-]{11})#i',
        '#(?:https?://)?youtu\.be/([a-zA-Z0-9_-]{11})#i',
        '#(?:https?://)?(?:www\.)?youtube\.com/embed/([a-zA-Z0-9_-]{11})#i',
        '#(?:https?://)?(?:www\.)?youtube\.com/shorts/([a-zA-Z0-9_-]{11})#i',
    );

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $m)) {
            return g5b_youtube_sanitize_id($m[1]);
        }
    }

    return '';
}

if (!function_exists('onoff_extract_youtube_id')) {
    /**
     * @param string $url
     * @return string 11자 영상 ID 또는 빈 문자열
     */
    function onoff_extract_youtube_id($url)
    {
        return g5b_youtube_id_from_url($url);
    }
}

function g5b_youtube_sanitize_id($id)
{
    $id = trim((string) $id);
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $id)) {
        return $id;
    }
    return '';
}

/**
 * 글 데이터에서 ID 추출 (wr_1 → 본문 iframe fallback)
 */
function g5b_youtube_id_from_write($write)
{
    if (!empty($write['wr_1'])) {
        $id = g5b_youtube_id_from_url($write['wr_1']);
        if ($id) {
            return $id;
        }
        $id = g5b_youtube_sanitize_id($write['wr_1']);
        if ($id) {
            return $id;
        }
    }

    if (!empty($write['wr_content'])) {
        $content = $write['wr_content'];
        if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{11})#i', $content, $m)) {
            return g5b_youtube_sanitize_id($m[1]);
        }
        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#i', $content, $m)) {
            return g5b_youtube_sanitize_id($m[1]);
        }
        if (preg_match('#(?:v=|/vi/)([a-zA-Z0-9_-]{11})#i', $content, $m)) {
            return g5b_youtube_sanitize_id($m[1]);
        }
    }

    return '';
}

function g5b_youtube_thumb_url($video_id, $quality = 'hqdefault')
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }
    $allowed = array('default', 'mqdefault', 'hqdefault', 'sddefault', 'maxresdefault');
    if (!in_array($quality, $allowed, true)) {
        $quality = 'hqdefault';
    }
    return 'https://img.youtube.com/vi/'.$video_id.'/'.$quality.'.jpg';
}

function g5b_youtube_embed_url($video_id)
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }
    return 'https://www.youtube-nocookie.com/embed/'.$video_id;
}

/**
 * wr_3 재생 시간(초) → 정수
 */
function g5b_youtube_duration_seconds($write)
{
    if (!is_array($write) || !isset($write['wr_3'])) {
        return 0;
    }

    $raw = trim((string) $write['wr_3']);
    if ($raw === '' || !preg_match('/^\d+$/', $raw)) {
        return 0;
    }

    return max(0, (int) $raw);
}

/**
 * 초 → YouTube 스타일 표기 (예: 1:23:45, 12:49)
 */
function g5b_youtube_format_duration($seconds)
{
    $seconds = max(0, (int) $seconds);
    if ($seconds <= 0) {
        return '';
    }

    $h = (int) floor($seconds / 3600);
    $m = (int) floor(($seconds % 3600) / 60);
    $s = $seconds % 60;

    if ($h > 0) {
        return $h.':'.sprintf('%02d', $m).':'.sprintf('%02d', $s);
    }

    return $m.':'.sprintf('%02d', $s);
}

/**
 * YouTube watch 페이지에서 lengthSeconds 추출 (API 키 불필요)
 */
function g5b_youtube_fetch_duration_seconds($video_id)
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return 0;
    }

    $url = 'https://www.youtube.com/watch?v='.$video_id;
    $ctx = stream_context_create(array(
        'http' => array(
            'timeout' => 10,
            'ignore_errors' => true,
            'header' => "User-Agent: Mozilla/5.0 (compatible; thecebu/1.0)\r\nAccept-Language: ko-KR,ko;q=0.9\r\n",
        ),
        'ssl' => array(
            'verify_peer' => true,
            'verify_peer_name' => true,
        ),
    ));

    $html = @file_get_contents($url, false, $ctx);
    if ($html === false || $html === '') {
        return 0;
    }

    if (preg_match('/"lengthSeconds"\s*:\s*"(\d+)"/', $html, $m)) {
        return max(0, (int) $m[1]);
    }
    if (preg_match('/"approxDurationMs"\s*:\s*"(\d+)"/', $html, $m)) {
        return max(0, (int) round(((int) $m[1]) / 1000));
    }

    return 0;
}

/**
 * 글에 재생 시간 저장 (wr_3, 초 단위)
 */
function g5b_youtube_save_duration($bo_table, $wr_id, $video_id = '')
{
    global $g5;

    $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
    $wr_id = (int) $wr_id;
    if ($bo_table === '' || $wr_id < 1) {
        return false;
    }

    if ($video_id === '') {
        $write_table = $g5['write_prefix'].$bo_table;
        $row = sql_fetch(" select wr_1, wr_content from {$write_table} where wr_id = '{$wr_id}' and wr_is_comment = 0 limit 1 ");
        if (!is_array($row)) {
            return false;
        }
        $video_id = g5b_youtube_id_from_write($row);
    }

    $seconds = g5b_youtube_fetch_duration_seconds($video_id);
    if ($seconds <= 0) {
        return false;
    }

    $write_table = $g5['write_prefix'].$bo_table;
    sql_query(" update {$write_table} set wr_3 = '{$seconds}' where wr_id = '{$wr_id}' ");

    return true;
}

/**
 * 관련 영상 목록 (글보기 사이드바)
 */
function g5b_youtube_get_related_writes($bo_table, $exclude_wr_id, $limit = 20)
{
    global $g5;

    $bo_table = preg_replace('/[^a-z0-9_]/i', '', (string) $bo_table);
    $exclude_wr_id = (int) $exclude_wr_id;
    $limit = max(1, min(30, (int) $limit));
    if ($bo_table === '') {
        return array();
    }

    $write_table = $g5['write_prefix'].$bo_table;
    $result = sql_query(" select wr_id, ca_name, wr_subject, wr_hit, wr_datetime, wr_1, wr_2, wr_3, wr_name
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

/**
 * 썸네일 duration 배지 HTML
 */
function g5b_youtube_duration_badge_html($write, $class = 'board-yt-duration')
{
    $label = g5b_youtube_format_duration(g5b_youtube_duration_seconds($write));
    if ($label === '') {
        return '';
    }

    return '<span class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'">'
        .htmlspecialchars($label, ENT_QUOTES, 'UTF-8').'</span>';
}

/**
 * 사이드바 추천 영상 카드 HTML
 */
function g5b_youtube_sidebar_item_html($row, $bo_table, $current_wr_id = 0)
{
    if (!is_array($row) || empty($row['wr_id'])) {
        return '';
    }

    $wr_id = (int) $row['wr_id'];
    if ($current_wr_id > 0 && $wr_id === (int) $current_wr_id) {
        return '';
    }

    $yt_id = g5b_youtube_id_from_write($row);
    $href = get_pretty_url($bo_table, $wr_id);
    $title = isset($row['wr_subject']) ? get_text(strip_tags($row['wr_subject'])) : '';
    $channel = g5b_youtube_channel_label($row);
    $views = g5b_youtube_format_views(isset($row['wr_hit']) ? $row['wr_hit'] : 0);
    $rel_time = g5b_youtube_relative_time(isset($row['wr_datetime']) ? $row['wr_datetime'] : '');
    $meta_parts = array();
    if ($channel !== '') {
        $meta_parts[] = $channel;
    }
    if ($views !== '') {
        $meta_parts[] = $views;
    }
    if ($rel_time !== '') {
        $meta_parts[] = $rel_time;
    }
    $meta_line = implode(' • ', $meta_parts);

    $thumb_html = g5b_youtube_thumb_html($yt_id, $title);
    $duration_html = g5b_youtube_duration_badge_html($row, 'board-yt-duration board-yt-duration--sidebar');

    return '<li class="board-yt-sidebar-item">'
        .'<a href="'.htmlspecialchars($href, ENT_QUOTES, 'UTF-8').'" class="board-yt-sidebar-item__link">'
        .'<span class="board-yt-sidebar-item__media">'.$thumb_html.$duration_html.'</span>'
        .'<span class="board-yt-sidebar-item__info">'
        .'<span class="board-yt-sidebar-item__title">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</span>'
        .($meta_line !== '' ? '<span class="board-yt-sidebar-item__meta">'.htmlspecialchars($meta_line, ENT_QUOTES, 'UTF-8').'</span>' : '')
        .'</span>'
        .'</a>'
        .'</li>';
}

/**
 * Schema·외부 링크용 watch URL (영상 ID만 사용)
 */
function g5b_youtube_watch_url($video_id)
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }
    return 'https://www.youtube.com/watch?v='.$video_id;
}

/**
 * VideoObject embedUrl (www.youtube.com, 영상 ID만 사용)
 */
function g5b_youtube_schema_embed_url($video_id)
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }
    return 'https://www.youtube.com/embed/'.$video_id;
}

/**
 * VideoObject용 설명 (wr_2 → wr_content, 최대 200자)
 */
function g5b_youtube_schema_description($write, $max_len = 200)
{
    $text = '';
    if (!empty($write['wr_2'])) {
        $text = get_text(strip_tags($write['wr_2']));
    } elseif (!empty($write['wr_content'])) {
        $text = get_text(strip_tags($write['wr_content']));
    }
    $text = trim((string) $text);
    if ($text === '') {
        return '';
    }
    if (function_exists('cut_str')) {
        return cut_str($text, (int) $max_len, '…');
    }
    if (strlen($text) > $max_len) {
        return substr($text, 0, $max_len).'…';
    }
    return $text;
}

/**
 * wr_datetime → ISO 8601 (실패 시 빈 문자열)
 */
function g5b_youtube_schema_upload_date($datetime)
{
    $datetime = trim((string) $datetime);
    if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
        return '';
    }
    $ts = strtotime($datetime);
    if (!$ts) {
        return '';
    }
    return date('c', $ts);
}

/**
 * 글보기 VideoObject Schema 출력 (파일·ID 없으면 무시)
 *
 * @param array  $write
 * @param string $video_id 이미 추출된 ID (선택)
 */
function g5b_youtube_print_video_schema($write, $video_id = '')
{
    if ($video_id === '') {
        $video_id = g5b_youtube_id_from_write($write);
    }
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return;
    }

    $title = !empty($write['wr_subject']) ? get_text(strip_tags($write['wr_subject'])) : '';
    $title = trim((string) $title);
    if ($title === '') {
        return;
    }

    $video_schema_title = $title;
    $video_schema_description = g5b_youtube_schema_description($write);
    $video_schema_id = $video_id;
    $video_schema_thumbnail = g5b_youtube_thumb_url($video_id);
    $video_schema_upload_date = g5b_youtube_schema_upload_date(isset($write['wr_datetime']) ? $write['wr_datetime'] : '');
    $video_schema_embed_url = g5b_youtube_schema_embed_url($video_id);
    $video_schema_content_url = g5b_youtube_watch_url($video_id);

    $schema_file = defined('G5_PATH') ? G5_PATH.'/components/schema/video.php' : '';
    if ($schema_file !== '' && is_file($schema_file)) {
        include_once $schema_file;
    }
}

/**
 * wr_2 또는 작성자명 → 채널 표시명
 */
function g5b_youtube_channel_label($write)
{
    $label = '';
    if (!empty($write['wr_2'])) {
        $label = g5b_youtube_normalize_channel_label($write['wr_2']);
    }
    if ($label === '' && !empty($write['wr_name'])) {
        $label = g5b_youtube_normalize_channel_label($write['wr_name']);
    }

    return trim((string) $label);
}

function g5b_youtube_normalize_channel_label($value)
{
    $label = get_text(strip_tags((string) $value));
    $label = preg_replace('/\s+/u', ' ', trim((string) $label));
    $label = preg_replace('/\s*채널\s*$/u', '', $label);

    // Some imported posts stored the video description in wr_2. Keep metadata YouTube-like.
    if ($label === '' || mb_strlen($label, 'UTF-8') > 30 || substr_count($label, ' ') > 5) {
        return '';
    }

    return trim((string) $label);
}

/**
 * 채널 이니셜 아바타
 */
function g5b_youtube_avatar_html($label, $extra_class = '')
{
    $label = trim((string) $label);
    $char = $label !== '' ? mb_substr($label, 0, 1, 'UTF-8') : '?';
    $hue = abs(crc32($label)) % 360;
    $class = 'board-yt-avatar'.($extra_class ? ' '.$extra_class : '');

    return '<span class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'" style="--yt-avatar-hue:'.$hue.'" aria-hidden="true">'
        .htmlspecialchars($char, ENT_QUOTES, 'UTF-8').'</span>';
}

/**
 * 상대 시간 (예: 3시간 전)
 */
function g5b_youtube_relative_time($datetime)
{
    $datetime = trim((string) $datetime);
    if ($datetime === '' || $datetime === '0000-00-00 00:00:00') {
        return '';
    }

    $ts = strtotime($datetime);
    if (!$ts) {
        return '';
    }

    $now = defined('G5_SERVER_TIME') ? G5_SERVER_TIME : time();
    $diff = max(0, $now - $ts);

    if ($diff < 60) {
        return '방금 전';
    }
    if ($diff < 3600) {
        return floor($diff / 60).'분 전';
    }
    if ($diff < 86400) {
        return floor($diff / 3600).'시간 전';
    }
    if ($diff < 86400 * 7) {
        return floor($diff / 86400).'일 전';
    }
    if ($diff < 86400 * 30) {
        return floor($diff / (86400 * 7)).'주 전';
    }
    if ($diff < 86400 * 365) {
        return floor($diff / (86400 * 30)).'개월 전';
    }

    return floor($diff / (86400 * 365)).'년 전';
}

/**
 * 조회수 표기 (예: 조회수 1.2만회)
 */
function g5b_youtube_format_views($hit)
{
    $hit = (int) $hit;
    if ($hit >= 100000000) {
        $val = round($hit / 100000000, 1);

        return '조회수 '.(fmod($val, 1.0) < 0.05 ? (string) (int) $val : number_format($val, 1)).'억회';
    }
    if ($hit >= 10000) {
        $val = $hit / 10000;

        return '조회수 '.(fmod($val, 1.0) < 0.05 ? (string) (int) $val : number_format($val, 1)).'만회';
    }
    if ($hit >= 1000) {
        $val = $hit / 1000;

        return '조회수 '.(fmod($val, 1.0) < 0.05 ? (string) (int) $val : number_format($val, 1)).'천회';
    }

    return '조회수 '.number_format($hit).'회';
}

/**
 * 목록용 썸네일 HTML
 */
function g5b_youtube_thumb_html($video_id, $alt = '', $is_secret = false)
{
    if ($is_secret) {
        return '<span class="board-yt-thumb board-yt-thumb--secret" title="비밀글">'
            .'<i class="fa fa-lock" aria-hidden="true"></i><span class="sound_only">비밀글</span></span>';
    }

    $video_id = g5b_youtube_sanitize_id($video_id);
    if ($video_id) {
        $src = g5b_youtube_thumb_url($video_id);
        $alt_attr = $alt ? htmlspecialchars(get_text(strip_tags($alt)), ENT_QUOTES, 'UTF-8') : '';
        return '<span class="board-yt-thumb board-yt-thumb--has youtube-thumb-wrap">'
            .'<img src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'" alt="'.$alt_attr.'" class="board-yt-thumb__img youtube-thumb" loading="lazy" decoding="async">'
            .'</span>';
    }

    if (g5b_fallback_file_exists('youtube')) {
        return '<span class="board-yt-thumb board-yt-thumb--fallback">'
            .g5b_fallback_img_html('youtube', 'board-yt-thumb__img board-yt-thumb__img--placeholder')
            .'</span>';
    }

    return '<span class="board-yt-thumb board-yt-thumb--empty" aria-hidden="true"></span>';
}

/**
 * 글보기 embed (src는 검증된 ID만 사용)
 */
function g5b_youtube_embed_html($video_id, $title = '')
{
    $video_id = g5b_youtube_sanitize_id($video_id);
    if (!$video_id) {
        return '';
    }

    $src = g5b_youtube_embed_url($video_id);
    $title_attr = $title ? htmlspecialchars(get_text(strip_tags($title)), ENT_QUOTES, 'UTF-8') : 'YouTube video player';

    return '<div class="board-yt-embed youtube-embed-wrap">'
        .'<div class="board-yt-embed__ratio youtube-embed">'
        .'<iframe src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'?rel=0" '
        .'title="'.$title_attr.'" '
        .'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
        .'referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>'
        .'</div></div>';
}

function g5b_youtube_fallback_html($message = '')
{
    if ($message === '') {
        $message = '등록된 유튜브 영상이 없거나 URL 형식이 올바르지 않습니다.';
    }
    $msg = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $icon = '';
    if (g5b_fallback_file_exists('youtube')) {
        $icon = g5b_fallback_img_html('youtube', 'board-yt-fallback__icon');
    }

    return '<div class="board-yt-fallback" role="alert">'
        .$icon
        .'<p class="board-yt-fallback__text">'.$msg.'</p>'
        .'<p class="board-yt-fallback__hint">지원 형식: youtube.com/watch?v= · youtu.be/ · embed/ · shorts/</p>'
        .'</div>';
}
