<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_plaza_ai_message_is_ai')) {
    function eottae_plaza_ai_message_is_ai(array $row)
    {
        if (!function_exists('eottae_plaza_ai_is_ai_write_row')) {
            include_once G5_LIB_PATH.'/eottae-plaza-ai.lib.php';
        }

        return eottae_plaza_ai_is_ai_write_row($row);
    }
}

if (!function_exists('eottae_plaza_ai_message_display_name')) {
    function eottae_plaza_ai_message_display_name(array $row = array())
    {
        $settings = function_exists('eottae_plaza_ai_get_settings')
            ? eottae_plaza_ai_get_settings()
            : array('ai_name' => '어때봇');
        $ai_name = trim((string) ($settings['ai_name'] ?? '어때봇'));
        if ($ai_name === '') {
            $ai_name = '어때봇';
        }

        return $ai_name.' · AI 도우미';
    }
}

if (!function_exists('eottae_plaza_ai_message_render_badge')) {
    function eottae_plaza_ai_message_render_badge(array $row = array(), $size = 'md')
    {
        if (!empty($row) && !eottae_plaza_ai_message_is_ai($row)) {
            return '';
        }

        $size = $size === 'sm' ? 'sm' : 'md';
        $name = eottae_plaza_ai_message_display_name($row);

        ob_start();
        ?>
        <span class="plaza-ai-badge plaza-ai-badge--<?php echo $size; ?>" aria-label="AI 도우미">
            <span class="plaza-ai-badge__icon" aria-hidden="true">🤖</span>
            <span class="plaza-ai-badge__label"><?php echo get_text($name); ?></span>
        </span>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_plaza_ai_message_row_class')) {
    function eottae_plaza_ai_message_row_class(array $row, $base = '')
    {
        $classes = array();
        if ($base !== '') {
            $classes[] = $base;
        }
        if (eottae_plaza_ai_message_is_ai($row)) {
            $classes[] = 'is-plaza-ai-message';
            $classes[] = 'plaza-card--ai';
        }

        return implode(' ', $classes);
    }
}
