<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_pwa_enabled')) {
    function eottae_pwa_enabled()
    {
        if (!function_exists('g5site_cfg')) {
            return false;
        }

        $enabled = g5site_cfg('pwa_enabled', true);

        return $enabled === true || $enabled === 1 || $enabled === '1';
    }
}

if (!function_exists('eottae_pwa_manifest_url')) {
    function eottae_pwa_manifest_url()
    {
        $path = function_exists('g5site_cfg') ? trim((string) g5site_cfg('pwa_manifest_path', '/proc/eottae-pwa-manifest.php')) : '/proc/eottae-pwa-manifest.php';
        if ($path === '') {
            $path = '/proc/eottae-pwa-manifest.php';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return rtrim(G5_URL, '/').'/'.ltrim($path, '/');
    }
}

if (!function_exists('eottae_pwa_theme_color')) {
    function eottae_pwa_theme_color()
    {
        $color = function_exists('g5site_cfg') ? trim((string) g5site_cfg('pwa_theme_color', '')) : '';
        if ($color === '' && function_exists('g5site_cfg')) {
            $color = trim((string) g5site_cfg('primary_color', '#0ea5e9'));
        }
        if ($color === '') {
            $color = '#0ea5e9';
        }
        if ($color[0] !== '#') {
            $color = '#'.$color;
        }

        return $color;
    }
}

if (!function_exists('eottae_pwa_background_color')) {
    function eottae_pwa_background_color()
    {
        $color = function_exists('g5site_cfg') ? trim((string) g5site_cfg('pwa_background_color', '#ffffff')) : '#ffffff';
        if ($color === '') {
            $color = '#ffffff';
        }
        if ($color[0] !== '#') {
            $color = '#'.$color;
        }

        return $color;
    }
}

if (!function_exists('eottae_pwa_icon_entries')) {
    /**
     * @return array<int, array<string, mixed>>
     */
    function eottae_pwa_icon_entries()
    {
        $icons = array();

        $candidates = array(
            array('key' => 'pwa_icon_512_path', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'),
            array('key' => 'pwa_icon_maskable_path', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'),
            array('key' => 'pwa_icon_192_path', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any', 'fallback' => '/img/logo/android-chrome-192x192.png'),
            array('key' => 'apple_touch_icon_path', 'sizes' => '180x180', 'type' => 'image/png', 'purpose' => 'any'),
            array('key' => 'favicon_png_path', 'sizes' => '32x32', 'type' => 'image/png', 'purpose' => 'any'),
        );

        $seen = array();
        foreach ($candidates as $candidate) {
            $url = function_exists('g5site_cfg_url') ? g5site_cfg_url($candidate['key'], isset($candidate['fallback']) ? $candidate['fallback'] : '') : '';
            if ($url === '' && !empty($candidate['fallback']) && defined('G5_URL')) {
                $url = rtrim(G5_URL, '/').$candidate['fallback'];
            }
            if ($url === '' || isset($seen[$url])) {
                continue;
            }
            $seen[$url] = true;
            $icons[] = array(
                'src' => $url,
                'sizes' => $candidate['sizes'],
                'type' => $candidate['type'],
                'purpose' => $candidate['purpose'],
            );
        }

        return $icons;
    }
}

if (!function_exists('eottae_pwa_manifest_data')) {
    function eottae_pwa_manifest_data()
    {
        $site_name = function_exists('g5site_cfg') ? trim((string) g5site_cfg('site_name', '세부어때')) : '세부어때';
        $short_name = function_exists('g5site_cfg') ? trim((string) g5site_cfg('pwa_short_name', '')) : '';
        if ($short_name === '') {
            $short_name = $site_name;
        }

        $description = function_exists('g5site_cfg') ? trim((string) g5site_cfg('site_desc', '')) : '';
        $start_url = function_exists('g5site_cfg') ? trim((string) g5site_cfg('pwa_start_url', '/')) : '/';
        if ($start_url === '') {
            $start_url = '/';
        }
        if ($start_url[0] !== '/') {
            $start_url = '/'.$start_url;
        }

        $display = function_exists('g5site_cfg') ? trim((string) g5site_cfg('pwa_display', 'standalone')) : 'standalone';
        if ($display === '') {
            $display = 'standalone';
        }

        return array(
            'name'             => $site_name,
            'short_name'       => $short_name,
            'description'      => $description,
            'start_url'        => $start_url,
            'scope'            => '/',
            'display'          => $display,
            'orientation'      => 'portrait-primary',
            'lang'             => 'ko',
            'dir'              => 'ltr',
            'theme_color'      => eottae_pwa_theme_color(),
            'background_color' => eottae_pwa_background_color(),
            'icons'            => eottae_pwa_icon_entries(),
            'categories'       => array('social', 'lifestyle', 'travel'),
        );
    }
}

if (!function_exists('eottae_pwa_render_manifest_json')) {
    function eottae_pwa_render_manifest_json()
    {
        return json_encode(eottae_pwa_manifest_data(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}

if (!function_exists('eottae_pwa_head_lines')) {
    /**
     * @return array<int, string>
     */
    function eottae_pwa_head_lines()
    {
        if (!eottae_pwa_enabled()) {
            return array();
        }

        $lines = array();
        $manifest_url = eottae_pwa_manifest_url();
        $theme_color = eottae_pwa_theme_color();

        $lines[] = '<link rel="manifest" href="'.htmlspecialchars($manifest_url, ENT_QUOTES, 'UTF-8').'">';
        $lines[] = '<meta name="theme-color" content="'.htmlspecialchars($theme_color, ENT_QUOTES, 'UTF-8').'">';
        $lines[] = '<meta name="mobile-web-app-capable" content="yes">';
        $lines[] = '<meta name="apple-mobile-web-app-capable" content="yes">';
        $lines[] = '<meta name="apple-mobile-web-app-status-bar-style" content="default">';
        $lines[] = '<meta name="application-name" content="'.htmlspecialchars(function_exists('g5site_cfg') ? g5site_cfg('site_name', '세부어때') : '세부어때', ENT_QUOTES, 'UTF-8').'">';
        $lines[] = '<script src="'.htmlspecialchars(G5_JS_URL.'/eottae-app.js', ENT_QUOTES, 'UTF-8').'" defer></script>';

        return $lines;
    }
}

if (!function_exists('eottae_pwa_head_html')) {
    function eottae_pwa_head_html()
    {
        $lines = eottae_pwa_head_lines();

        return $lines ? implode(PHP_EOL, $lines) : '';
    }
}

if (!function_exists('eottae_android_app_package')) {
    function eottae_android_app_package()
    {
        $package = function_exists('g5site_cfg') ? trim((string) g5site_cfg('android_app_package', 'kr.co.thecebu.app')) : 'kr.co.thecebu.app';

        return $package !== '' ? $package : 'kr.co.thecebu.app';
    }
}
