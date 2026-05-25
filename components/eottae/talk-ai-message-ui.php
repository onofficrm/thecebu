<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_talkroom_ai_message_ui_bootstrap')) {
    function eottae_talkroom_ai_message_ui_bootstrap()
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;

        if (!function_exists('eottae_talkroom_ai_is_ai_write_row')) {
            include_once G5_LIB_PATH.'/eottae-talkroom-ai.lib.php';
        }
    }
}

if (!function_exists('eottae_talkroom_ai_message_is_ai')) {
    function eottae_talkroom_ai_message_is_ai(array $row)
    {
        eottae_talkroom_ai_message_ui_bootstrap();

        if (!empty($row['is_ai'])) {
            return true;
        }

        return eottae_talkroom_ai_is_ai_write_row($row);
    }
}

if (!function_exists('eottae_talkroom_ai_message_display_name')) {
    function eottae_talkroom_ai_message_display_name(array $row)
    {
        $name = trim(strip_tags((string) ($row['wr_name'] ?? '')));
        if ($name === '' && !empty($row['author'])) {
            $name = trim(strip_tags((string) $row['author']));
        }

        if ($name !== '' && mb_strpos($name, 'AI 도우미', 0, 'UTF-8') !== false) {
            return $name;
        }

        if ($name !== '' && preg_match('/^(.+?)\s·\s*AI\s*도우미/u', $name, $m)) {
            return trim($m[1]).' · AI 도우미';
        }

        if ($name !== '') {
            return $name.' · AI 도우미';
        }

        return '어때봇 · AI 도우미';
    }
}

if (!function_exists('eottae_talkroom_ai_message_row_class')) {
    function eottae_talkroom_ai_message_row_class(array $row, $base = '')
    {
        $classes = array();
        if ($base !== '') {
            $classes[] = $base;
        }
        if (eottae_talkroom_ai_message_is_ai($row)) {
            $classes[] = 'is-talk-ai-message';
        }

        return implode(' ', $classes);
    }
}

if (!function_exists('eottae_talkroom_ai_message_render_badge')) {
    function eottae_talkroom_ai_message_render_badge(array $row = array(), $size = 'md')
    {
        if (!empty($row) && !eottae_talkroom_ai_message_is_ai($row)) {
            return '';
        }

        $size = $size === 'sm' ? 'sm' : 'md';
        $name = !empty($row) ? eottae_talkroom_ai_message_display_name($row) : '어때봇 · AI 도우미';

        ob_start();
        ?>
        <span class="talk-ai-msg__badge talk-ai-msg__badge--<?php echo $size; ?>" aria-label="AI 도우미">
            <span class="talk-ai-msg__icon" aria-hidden="true">🤖</span>
            <span class="talk-ai-msg__badge-label"><?php echo get_text($name); ?></span>
        </span>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_talkroom_ai_message_render_author')) {
    function eottae_talkroom_ai_message_render_author(array $row)
    {
        if (!eottae_talkroom_ai_message_is_ai($row)) {
            return '';
        }

        return eottae_talkroom_ai_message_render_badge($row, 'sm');
    }
}

if (!function_exists('eottae_talkroom_ai_message_enrich_post_row')) {
    /**
     * @return array<string, mixed>
     */
    function eottae_talkroom_ai_message_enrich_post_row(array $row)
    {
        eottae_talkroom_ai_message_ui_bootstrap();

        $is_ai = eottae_talkroom_ai_message_is_ai($row);
        $row['is_ai'] = $is_ai ? 1 : 0;
        if ($is_ai) {
            $row['ai_display_name'] = eottae_talkroom_ai_message_display_name($row);
        }

        return $row;
    }
}
