<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-briefing.lib.php';

if (function_exists('eottae_briefing_load_assets')) {
    eottae_briefing_load_assets();
}

$briefing_data = collect_today_sebu_briefing_data();
$mypage_url = function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php';
$back_url = G5_URL;
$back_label = '홈';

g5_page_start('오늘의 세부 브리핑');
?>

<main class="eottae-guide-page eottae-guide-page--briefing">
    <header class="eottae-guide-page__header">
        <a href="<?php echo $back_url; ?>" class="eottae-guide-page__back">← <?php echo get_text($back_label); ?></a>
        <p class="eottae-guide-page__badge">세부 데일리</p>
        <h1 class="eottae-guide-page__title">오늘의 세부 브리핑</h1>
        <p class="eottae-guide-page__lead">오늘 확인하면 좋은 일정, 세부톡·커뮤니티 활동, 인기글을 모았습니다.</p>
        <?php if ($is_member) { ?>
        <a href="<?php echo $mypage_url; ?>" class="eottae-guide-page__cta">내 맞춤 브리핑 보기</a>
        <?php } ?>
    </header>

    <div class="sebu-briefing-page__body">
        <?php render_today_sebu_briefing($briefing_data); ?>
    </div>
</main>

<?php
g5_page_end();
