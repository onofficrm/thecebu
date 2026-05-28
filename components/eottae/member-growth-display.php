<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_member_growth_badge_color_class')) {
    function eottae_member_growth_badge_color_class($color)
    {
        $color = preg_replace('/[^a-z_]/', '', (string) $color);
        $allowed = array('default', 'life', 'food', 'meetup', 'trade', 'official', 'vip');

        return 'sebu-badge--'.(in_array($color, $allowed, true) ? $color : 'default');
    }
}

if (!function_exists('eottae_member_growth_render_badge')) {
    function eottae_member_growth_render_badge(array $badge, $is_main = false)
    {
        if (empty($badge['badge_name'])) {
            return '';
        }

        $class = 'sebu-badge '.eottae_member_growth_badge_color_class($badge['badge_color'] ?? 'default');
        if ($is_main) {
            $class .= ' sebu-badge-main';
        }
        if (($badge['badge_type'] ?? '') === 'official') {
            $class .= ' sebu-badge-official';
        }

        $icon = trim((string) ($badge['badge_icon'] ?? ''));
        $label = get_text($badge['badge_name']);

        return '<span class="'.$class.'" title="'.get_text($badge['badge_description'] ?? $label).'">'
            .($icon !== '' ? '<span class="sebu-badge__icon" aria-hidden="true">'.$icon.'</span> ' : '')
            .'<span class="sebu-badge__label">'.$label.'</span></span>';
    }
}

if (!function_exists('eottae_member_growth_render_level_chip')) {
    function eottae_member_growth_render_level_chip(array $level)
    {
        if (empty($level['level_name'])) {
            return '';
        }

        $icon = trim((string) ($level['icon'] ?? ''));
        $class = 'sebu-level-chip '.eottae_member_growth_badge_color_class($level['color'] ?? 'default');

        return '<span class="'.$class.'">'
            .($icon !== '' ? '<span class="sebu-level-chip__icon" aria-hidden="true">'.$icon.'</span> ' : '')
            .'<span class="sebu-level-chip__label">'.get_text($level['level_name']).'</span></span>';
    }
}

if (!function_exists('eottae_member_growth_render_author_line')) {
    function eottae_member_growth_render_author_line($mb_id, $nick, $options = array())
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        $nick = get_text(strip_tags((string) $nick));
        if ($nick === '') {
            return '';
        }

        if (!empty($options['skip_badge']) || $mb_id === '') {
            return '<span class="sebu-author-line">'.get_text($nick).'</span>';
        }

        if (!function_exists('eottae_member_growth_get_profile')) {
            include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
        }

        $profile = eottae_member_growth_get_profile($mb_id);
        $level_html = '';
        $badge_html = '';

        if (!empty($profile['level']) && empty($options['hide_level'])) {
            $level_html = eottae_member_growth_render_level_chip($profile['level']);
        }
        if (!empty($profile['main_badge']) && empty($options['hide_badge'])) {
            $badge_html = eottae_member_growth_render_badge($profile['main_badge'], true);
        }

        $sep = !empty($options['inline']) ? ' · ' : ' ';

        $profile_url = function_exists('eottae_member_growth_profile_url')
            ? eottae_member_growth_profile_url($mb_id)
            : '';

        ob_start();
        ?>
        <span class="sebu-author-line">
            <span class="sebu-author-line__nick"><?php
            if ($profile_url !== '' && empty($options['no_link'])) {
                echo '<a href="'.get_text($profile_url).'" class="sebu-author-line__link">'.$nick.'</a>';
            } else {
                echo $nick;
            }
            ?></span>
            <?php if ($badge_html !== '') { ?>
            <span class="sebu-author-line__badge"><?php echo $badge_html; ?></span>
            <?php } elseif ($level_html !== '' && empty($options['badge_only'])) { ?>
            <span class="sebu-author-line__level"><?php echo $sep.$level_html; ?></span>
            <?php } ?>
        </span>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_member_growth_render_profile_badge_icon')) {
    /**
     * 사이드바·프로필 카드용 — 뱃지(또는 등급) 아이콘만 표시
     */
    function eottae_member_growth_render_profile_badge_icon(array $profile, array $options = array())
    {
        $href = isset($options['href']) ? (string) $options['href'] : '';
        if ($href === '' && function_exists('eottae_member_growth_mypage_url')) {
            $href = eottae_member_growth_mypage_url();
        }
        if ($href === '' && function_exists('eottae_member_growth_badge_book_url')) {
            $href = eottae_member_growth_badge_book_url();
        }

        $icon = '';
        $label = '';
        $color = 'default';

        if (!empty($profile['main_badge']['badge_name'])) {
            $badge = $profile['main_badge'];
            $icon = trim((string) ($badge['badge_icon'] ?? ''));
            if ($icon === '') {
                $icon = '★';
            }
            $label = get_text($badge['badge_name']);
            $color = (string) ($badge['badge_color'] ?? 'default');
            $title = get_text($badge['badge_description'] ?? $label);
        } elseif (!empty($profile['level']['level_name'])) {
            $level = $profile['level'];
            $icon = trim((string) ($level['icon'] ?? ''));
            if ($icon === '') {
                $icon = '◆';
            }
            $label = get_text($level['level_name']);
            $color = (string) ($level['color'] ?? 'default');
            $title = $label;
        } else {
            return '';
        }

        $class = 'community-login-box__badge-icon '.eottae_member_growth_badge_color_class($color);
        $inner = '<span class="'.$class.'" title="'.get_text($title).'">'
            .'<span class="community-login-box__badge-emoji" aria-hidden="true">'.htmlspecialchars($icon, ENT_QUOTES, 'UTF-8').'</span>'
            .'<span class="sound_only">'.get_text($label).'</span>'
            .'</span>';

        if ($href !== '') {
            return '<a href="'.get_text($href).'" class="community-login-box__badge-link">'.$inner.'</a>';
        }

        return $inner;
    }
}

if (!function_exists('eottae_member_growth_author_badge_text')) {
    function eottae_member_growth_author_badge_text($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return '';
        }

        if (!function_exists('eottae_member_growth_get_profile')) {
            include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
        }

        $profile = eottae_member_growth_get_profile($mb_id);
        if (!empty($profile['main_badge']['badge_name'])) {
            $icon = trim((string) ($profile['main_badge']['badge_icon'] ?? ''));
            return ($icon !== '' ? $icon.' ' : '').get_text($profile['main_badge']['badge_name']);
        }
        if (!empty($profile['level']['level_name'])) {
            $icon = trim((string) ($profile['level']['icon'] ?? ''));
            return ($icon !== '' ? $icon.' ' : '').get_text($profile['level']['level_name']);
        }

        return '';
    }
}
