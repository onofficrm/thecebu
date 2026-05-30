<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_column_render_avatar_html')) {
    /**
     * @param array  $author  enrich_author 결과
     * @param string $size    sm|md|lg
     */
    function eottae_column_render_avatar_html(array $author, $size = 'md', $extra_class = '')
    {
        $size = in_array($size, array('sm', 'md', 'lg'), true) ? $size : 'md';
        $class = trim('sebu-column-avatar sebu-column-avatar--'.$size.' '.$extra_class);
        $alt = get_text($author['display_name'] ?? '');

        if (!empty($author['has_profile_image']) && !empty($author['profile_image_url'])) {
            $px = $size === 'lg' ? 96 : ($size === 'sm' ? 48 : 64);

            return '<img src="'.get_text($author['profile_image_url']).'" alt="'.$alt.'" class="'.$class.'" width="'.$px.'" height="'.$px.'" loading="lazy">';
        }

        $initials = get_text($author['profile_initials'] ?? '?');

        return '<span class="'.$class.' sebu-column-avatar--initials" aria-hidden="true">'.$initials.'</span>';
    }
}

if (!function_exists('eottae_column_social_link_icon')) {
    function eottae_column_social_link_icon($key)
    {
        $key = preg_replace('/[^a-z_]/', '', (string) $key);
        $icons = array(
            'youtube_url'    => 'YT',
            'facebook_url'   => 'f',
            'instagram_url'  => 'IG',
            'tiktok_url'     => 'TT',
            'naver_blog_url' => 'BLOG',
            'website_url'    => 'WEB',
        );

        return $icons[$key] ?? 'SNS';
    }
}

if (!function_exists('eottae_column_author_profile_badges')) {
    function eottae_column_author_profile_badges(array $author)
    {
        if (!function_exists('eottae_column_social_platform_labels')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        $badges = array();
        $seen = array();

        $push = function ($badge) use (&$badges, &$seen) {
            $label = trim((string) ($badge['label'] ?? ''));
            if ($label === '') {
                return;
            }
            $key = ($badge['type'] ?? 'info').'|'.$label.'|'.($badge['url'] ?? '');
            if (isset($seen[$key])) {
                return;
            }
            $seen[$key] = true;
            $badges[] = $badge;
        };

        if (!empty($author['is_official'])) {
            $push(array(
                'type'  => 'info',
                'label' => '공식',
                'class' => 'official',
            ));
        }

        if (!empty($author['grade_label'])) {
            $push(array(
                'type'  => 'info',
                'label' => get_text($author['grade_label']),
                'class' => 'grade',
            ));
        }

        if (!empty($author['area_label'])) {
            $push(array(
                'type'  => 'info',
                'label' => get_text($author['area_label']),
                'class' => 'area',
            ));
        }

        if (!empty($author['title'])) {
            $push(array(
                'type'  => 'info',
                'label' => get_text($author['title']),
                'class' => 'title',
            ));
        }

        if (!empty($author['specialty'])) {
            foreach (array_filter(array_map('trim', preg_split('/\s*,\s*/u', (string) $author['specialty']))) as $specialty) {
                $push(array(
                    'type'  => 'info',
                    'label' => get_text($specialty),
                    'class' => 'specialty',
                ));
            }
        }

        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) ($author['mb_id'] ?? ''));
        if ($mb_id !== '' && function_exists('eottae_member_business_board_badges')) {
            foreach (eottae_member_business_board_badges($mb_id) as $business_badge) {
                $push($business_badge);
            }
        }

        $website_url = function_exists('eottae_column_normalize_url')
            ? eottae_column_normalize_url($author['website_url'] ?? '')
            : trim((string) ($author['website_url'] ?? ''));
        if ($website_url !== '') {
            $push(array(
                'type'  => 'link',
                'key'   => 'website_url',
                'url'   => $website_url,
                'label' => '홈페이지',
                'icon'  => eottae_column_social_link_icon('website_url'),
                'class' => 'website',
            ));
        }

        $social_links = $author['social_links'] ?? array();
        if (empty($social_links) && function_exists('eottae_column_author_social_links')) {
            $social_links = eottae_column_author_social_links($author);
        }

        foreach ($social_links as $link) {
            $key = preg_replace('/[^a-z_]/', '', (string) ($link['key'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $push(array(
                'type'  => 'link',
                'key'   => $key,
                'url'   => $url,
                'label' => get_text($link['label'] ?? ''),
                'icon'  => eottae_column_social_link_icon($key),
                'class' => $key,
            ));
        }

        return $badges;
    }
}

if (!function_exists('eottae_column_render_profile_badge_html')) {
    function eottae_column_render_profile_badge_html(array $badge)
    {
        $type = ($badge['type'] ?? '') === 'link' ? 'link' : 'info';
        $label = get_text($badge['label'] ?? '');
        if ($label === '') {
            return '';
        }

        $class_parts = array('sebu-column-profile-badge');
        if ($type === 'link') {
            $class_parts[] = 'sebu-column-social__link';
        } else {
            $class_parts[] = 'sebu-column-profile-badge--info';
        }

        $modifier = preg_replace('/[^a-z0-9_-]/', '', (string) ($badge['class'] ?? ''));
        if ($modifier !== '') {
            $class_parts[] = 'sebu-column-profile-badge--'.$modifier;
        }

        $key = preg_replace('/[^a-z_]/', '', (string) ($badge['key'] ?? ''));
        if ($key !== '') {
            $class_parts[] = 'sebu-column-social__link--'.$key;
        }

        $class = implode(' ', $class_parts);
        $icon = trim((string) ($badge['icon'] ?? ''));
        if ($icon === '' && $key !== '') {
            $icon = eottae_column_social_link_icon($key);
        }

        $inner = '';
        if ($icon !== '') {
            $inner .= '<span class="sebu-column-social__icon" aria-hidden="true">'.get_text($icon).'</span>';
        }
        $inner .= '<span class="sebu-column-social__text">'.get_text($label).'</span>';

        if ($type === 'link') {
            $url = get_text($badge['url'] ?? '#');

            return '<a href="'.$url.'" class="'.$class.'" target="_blank" rel="noopener noreferrer" role="listitem">'.$inner.'</a>';
        }

        return '<span class="'.$class.'" role="listitem">'.$inner.'</span>';
    }
}

if (!function_exists('eottae_column_render_author_profile_badges_html')) {
    function eottae_column_render_author_profile_badges_html(array $author, $extra_class = '')
    {
        $badges = eottae_column_author_profile_badges($author);
        if (empty($badges)) {
            return '';
        }

        $class = trim('sebu-column-profile-block__badges sebu-column-social '.$extra_class);
        $html = '';
        foreach ($badges as $badge) {
            $html .= eottae_column_render_profile_badge_html($badge);
        }

        return '<div class="'.$class.'" role="list">'.$html.'</div>';
    }
}

if (!function_exists('eottae_post_view_author_profile_badges_html')) {
    function eottae_post_view_author_profile_badges_html($mb_id)
    {
        $mb_id = preg_replace('/[^a-z0-9_@.-]/i', '', (string) $mb_id);
        if ($mb_id === '') {
            return '';
        }

        if (!function_exists('eottae_column_get_author')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        $author = function_exists('eottae_column_get_author') ? eottae_column_get_author($mb_id) : null;
        if (is_array($author) && !empty($author['is_visible'])) {
            return eottae_column_render_author_profile_badges_html($author, 'sebu-column-profile-block__badges--post-view');
        }

        if (!function_exists('eottae_member_business_board_badges')) {
            return '';
        }

        $badges = eottae_member_business_board_badges($mb_id);
        if (empty($badges)) {
            return '';
        }

        $class = 'sebu-column-profile-block__badges sebu-column-social sebu-column-profile-block__badges--post-view';
        $html = '';
        foreach ($badges as $badge) {
            $html .= eottae_column_render_profile_badge_html($badge);
        }

        return '<div class="'.$class.'" role="list">'.$html.'</div>';
    }
}

if (!function_exists('eottae_column_render_social_links_html')) {
    function eottae_column_render_social_links_html(array $author, $extra_class = '')
    {
        return eottae_column_render_author_profile_badges_html($author, $extra_class);
    }
}

if (!function_exists('eottae_column_render_author_profile_block_html')) {
    function eottae_column_render_author_profile_block_html(array $author, $size = 'md', $show_bio = false)
    {
        if (empty($author['display_name']) && empty($author['mb_id'])) {
            return '';
        }

        $profile_url = get_text($author['profile_url'] ?? '#');
        ob_start();
        ?>
        <div class="sebu-column-profile-block">
            <a href="<?php echo $profile_url; ?>" class="sebu-column-profile-block__main">
                <?php echo eottae_column_render_avatar_html($author, $size, 'sebu-column-profile-block__avatar'); ?>
                <span class="sebu-column-profile-block__text">
                    <span class="sebu-column-profile-block__name"><?php echo get_text($author['display_name'] ?? ''); ?></span>
                    <?php if ($show_bio && !empty($author['bio'])) { ?>
                    <span class="sebu-column-profile-block__bio"><?php echo get_text(cut_str($author['bio'], 100, '…')); ?></span>
                    <?php } ?>
                </span>
            </a>
            <?php echo eottae_column_render_author_profile_badges_html($author); ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_column_render_social_form_fields')) {
    function eottae_column_render_social_form_fields(array $values = array())
    {
        if (!function_exists('eottae_column_social_platform_labels')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        $labels = eottae_column_social_platform_labels();
        $placeholders = array(
            'youtube_url'    => 'https://www.youtube.com/@...',
            'facebook_url'   => 'https://www.facebook.com/...',
            'instagram_url'  => 'https://www.instagram.com/...',
            'tiktok_url'     => 'https://www.tiktok.com/@...',
            'naver_blog_url' => 'https://blog.naver.com/...',
        );
        ob_start();
        foreach (eottae_column_social_field_keys() as $key) {
            ?>
        <label class="sebu-column-form__field">
            <span class="sebu-column-form__label"><?php echo get_text($labels[$key] ?? $key); ?></span>
            <input type="url" name="<?php echo $key; ?>" class="sebu-column-form__input" value="<?php echo get_text($values[$key] ?? ''); ?>" placeholder="<?php echo get_text($placeholders[$key] ?? 'https://'); ?>">
        </label>
            <?php
        }

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_column_render_social_form_fields_compact')) {
    function eottae_column_render_social_form_fields_compact(array $values = array())
    {
        if (!function_exists('eottae_column_social_platform_labels')) {
            include_once G5_LIB_PATH.'/eottae-column.lib.php';
        }

        $labels = eottae_column_social_platform_labels();
        $placeholders = array(
            'youtube_url'    => 'https://www.youtube.com/@...',
            'facebook_url'   => 'https://www.facebook.com/...',
            'instagram_url'  => 'https://www.instagram.com/...',
            'tiktok_url'     => 'https://www.tiktok.com/@...',
            'naver_blog_url' => 'https://blog.naver.com/...',
        );
        ob_start();
        ?>
        <div class="sebu-column-form__sns-grid">
            <?php foreach (eottae_column_social_field_keys() as $key) {
                $label = $labels[$key] ?? $key;
                ?>
            <label class="sebu-column-form__field sebu-column-form__field--sns">
                <span class="sebu-column-form__label"><?php echo get_text($label); ?></span>
                <input type="url" name="<?php echo $key; ?>" class="sebu-column-form__input" value="<?php echo get_text($values[$key] ?? ''); ?>" placeholder="<?php echo get_text($placeholders[$key] ?? 'https://'); ?>" autocomplete="url">
            </label>
                <?php
            } ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
