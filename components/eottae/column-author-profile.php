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

if (!function_exists('eottae_column_render_social_links_html')) {
    function eottae_column_render_social_links_html(array $author, $extra_class = '')
    {
        $links = $author['social_links'] ?? array();
        if (empty($links)) {
            return '';
        }

        $class = trim('sebu-column-social '.$extra_class);
        ob_start();
        ?>
        <div class="<?php echo $class; ?>" role="list">
            <?php foreach ($links as $link) {
                $key = preg_replace('/[^a-z_]/', '', (string) ($link['key'] ?? ''));
                ?>
            <a href="<?php echo get_text($link['url'] ?? '#'); ?>" class="sebu-column-social__link sebu-column-social__link--<?php echo $key; ?>" target="_blank" rel="noopener noreferrer" role="listitem"><?php echo get_text($link['label'] ?? ''); ?></a>
            <?php } ?>
        </div>
        <?php

        return (string) ob_get_clean();
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
                    <?php if (!empty($author['title'])) { ?>
                    <span class="sebu-column-profile-block__title"><?php echo get_text($author['title']); ?></span>
                    <?php } ?>
                    <?php if ($show_bio && !empty($author['bio'])) { ?>
                    <span class="sebu-column-profile-block__bio"><?php echo get_text(cut_str($author['bio'], 100, '…')); ?></span>
                    <?php } ?>
                </span>
            </a>
            <?php echo eottae_column_render_social_links_html($author, 'sebu-column-profile-block__social'); ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_column_render_social_form_fields')) {
    function eottae_column_render_social_form_fields(array $values = array())
    {
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
