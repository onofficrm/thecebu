<?php
/**
 * 고객 서브도메인 → onoff-builder-bridge 랜딩 연결
 * 예: yonggungri.thecebu.co.kr → imports/yonggungri
 *
 * 호스팅에서 서브도메인이 메인 public_html 과 같은 루트(alias)일 때 동작합니다.
 * 서브도메인 전용 문서 루트(yonggungri.thecebu.co.kr/)를 쓰는 경우에는
 * 해당 폴더의 정적 파일이 우선됩니다.
 */
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('thecebu_customer_subdomain_map')) {
    function thecebu_customer_subdomain_map()
    {
        return array(
            'yonggungri.thecebu.co.kr' => 'yonggungri',
            'www.yonggungri.thecebu.co.kr' => 'yonggungri',
        );
    }
}

if (!function_exists('thecebu_customer_subdomain_boot')) {
    function thecebu_customer_subdomain_boot()
    {
        if (defined('THECEBU_CUSTOMER_SUBDOMAIN_BOOTED')) {
            return;
        }
        define('THECEBU_CUSTOMER_SUBDOMAIN_BOOTED', true);

        $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';
        $host = preg_replace('/:\d+$/', '', $host);
        if ($host === '') {
            return;
        }

        $map = thecebu_customer_subdomain_map();
        if (!isset($map[$host])) {
            return;
        }

        $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        // 정적·관리·API 경로는 건드리지 않음
        if (preg_match('#^/(adm|bbs|plugin|data|css|js|img|assets|page|proc|api|cron|mobile)(/|$)#i', $path)) {
            return;
        }
        if ($path !== '/' && $path !== '/index.php') {
            return;
        }

        $project_id = preg_replace('/[^a-z0-9_-]/i', '', $map[$host]);
        if ($project_id === '') {
            return;
        }

        if (!defined('ONOFF_BUILDER_LOADED') && defined('G5_PLUGIN_PATH')) {
            $bootstrap = G5_PLUGIN_PATH . '/onoff-builder-bridge/bootstrap.php';
            if (is_file($bootstrap)) {
                include_once $bootstrap;
            }
        }

        if (function_exists('onoff_builder_render_import_page')) {
            onoff_builder_render_import_page($project_id);
            exit;
        }
    }
}

thecebu_customer_subdomain_boot();
