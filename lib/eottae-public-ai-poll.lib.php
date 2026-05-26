<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_public_ai_poll_encode_options')) {
    function eottae_public_ai_poll_encode_options(array $options)
    {
        $clean = array();
        foreach ($options as $opt) {
            $opt = trim(strip_tags((string) $opt));
            if ($opt !== '') {
                $clean[] = $opt;
            }
        }

        if (!$clean) {
            return '';
        }

        return json_encode($clean, JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('eottae_public_ai_poll_decode_options')) {
    function eottae_public_ai_poll_decode_options($json)
    {
        $json = trim((string) $json);
        if ($json === '') {
            return array();
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return array();
        }

        $out = array();
        foreach ($decoded as $item) {
            $item = trim(strip_tags((string) $item));
            if ($item !== '') {
                $out[] = $item;
            }
        }

        return $out;
    }
}

if (!function_exists('eottae_public_ai_poll_append_to_message')) {
    function eottae_public_ai_poll_append_to_message($message, $poll_options)
    {
        $options = is_array($poll_options)
            ? $poll_options
            : eottae_public_ai_poll_decode_options($poll_options);
        if (!$options) {
            return trim((string) $message);
        }

        $lines = array();
        foreach ($options as $i => $opt) {
            $lines[] = ($i + 1).'. '.$opt;
        }

        return trim((string) $message)."\n\n".implode("\n", $lines);
    }
}

if (!function_exists('eottae_public_ai_poll_render_html')) {
    function eottae_public_ai_poll_render_html($poll_options)
    {
        $options = is_array($poll_options)
            ? $poll_options
            : eottae_public_ai_poll_decode_options($poll_options);
        if (!$options) {
            return '';
        }

        ob_start();
        ?>
        <ul class="public-group-chat__poll" aria-label="선택지">
            <?php foreach ($options as $opt) { ?>
            <li class="public-group-chat__poll-item"><?php echo get_text($opt); ?></li>
            <?php } ?>
        </ul>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_public_ai_poll_default_holiday_options')) {
    function eottae_public_ai_poll_default_holiday_options()
    {
        return array('리조트', '맛집', '쇼핑', '집콕', '운동');
    }
}
