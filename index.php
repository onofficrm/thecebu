<?php
include_once('./_common.php');

define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 빌더 bridge 메인 (/_site.config.php → home_builder_bridge_id)
if (is_file(G5_PATH . '/_site.config.php')) {
    include_once(G5_PATH . '/_site.config.php');
}
if (function_exists('g5site_cfg')) {
    $thecebu_home_builder_id = g5site_cfg('home_builder_bridge_id', '');
    if ($thecebu_home_builder_id === '') {
        $thecebu_import_dir = G5_PLUGIN_PATH . '/onoff-builder-bridge/imports/thecebu-main';
        if (is_dir($thecebu_import_dir) && is_file($thecebu_import_dir . '/index.html')) {
            $thecebu_home_builder_id = 'thecebu-main';
        }
    }
    if ($thecebu_home_builder_id !== '') {
        $thecebu_home_builder_id = preg_replace('/[^a-z0-9_-]/i', '', $thecebu_home_builder_id);
        if ($thecebu_home_builder_id !== '') {
            if (!defined('ONOFF_BUILDER_LOADED') && defined('G5_PLUGIN_PATH')) {
                $thecebu_builder_bootstrap = G5_PLUGIN_PATH . '/onoff-builder-bridge/bootstrap.php';
                if (is_file($thecebu_builder_bootstrap)) {
                    include_once $thecebu_builder_bootstrap;
                }
            }
            if (function_exists('onoff_builder_render_import_page')) {
                onoff_builder_render_import_page($thecebu_home_builder_id);
                exit;
            }
        }
    }
}

// 테마 사용 시 테마 index로 위임 (기존 동작 유지)
if (defined('G5_THEME_PATH')) {
    require_once(G5_THEME_PATH.'/index.php');
    return;
}

// 모바일은 mobile/index.php 사용 (기존 동작 유지)
if (G5_IS_MOBILE) {
    include_once(G5_MOBILE_PATH.'/index.php');
    return;
}

include_once(G5_PATH.'/head.php');

/**
 * 메인 섹션 목록
 * - 순서 변경·추가·삭제는 이 배열만 수정
 * - 파일 경로: /section/{이름}.php
 */
$g5_main_sections = array(
    'hero',
    'service',
    'advantage',
    'portfolio',
    'latest',   // 게시판 최신글 (story, news, sample) — section/latest.php
    'talk-today', // 세부톡방 최신글 — section/talk-today.php
    'review',
    'faq',
    'contact',
);
?>

<h2 class="sound_only">메인</h2>

<main id="siteMain" class="site-main">
<?php
foreach ($g5_main_sections as $section_name) {
    $section_file = G5_PATH.'/section/'.$section_name.'.php';
    if (is_file($section_file)) {
        include_once($section_file);
    }
}
?>
</main>

<?php
/*
 * [참고] 이전 메인 최신글 출력 (tail.php·게시판·관리자 기능과 무관)
 * - tail.php: latest('notice'), visit(), outlogin(), poll() 유지
 * - 게시판·로그인·관리자: bbs/, head.php, tail.php 경로 그대로
 * - 메인 최신글 블록이 필요하면 section/latest.php 를 만들어 $g5_main_sections 에 추가
 *
 * echo latest('pic_list', 'free', 4, 23);
 * echo latest('pic_list', 'qa', 4, 23);
 * echo latest('pic_list', 'notice', 4, 23);
 * echo latest('pic_block', 'gallery', 4, 23);
 * // + 게시판 루프 latest('basic', $bo_table, 6, 24);
 */

include_once(G5_PATH.'/tail.php');
