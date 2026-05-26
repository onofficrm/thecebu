<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-calendar.lib.php';

$view = isset($_GET['view']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['view']) : 'month';
if (!in_array($view, array('month', 'list'), true)) {
    $view = 'month';
}

$category = isset($_GET['category']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['category']) : '';
if ($category !== '' && !isset(eottae_calendar_category_options()[$category])) {
    $category = '';
}

$range = isset($_GET['range']) ? preg_replace('/[^a-z_]/', '', (string) $_GET['range']) : '';
$today = date('Y-m-d');

$year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
$month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
if ($month < 1 || $month > 12) {
    $month = (int) date('n');
}
if ($year < 1970 || $year > 2100) {
    $year = (int) date('Y');
}

if ($range === 'today') {
    $view = 'list';
    $range_start = $today;
    $range_end = $today;
} elseif ($range === 'week') {
    $view = 'list';
    $range_start = $today;
    $range_end = date('Y-m-d', strtotime($today.' +6 days'));
} else {
    $range_start = sprintf('%04d-%02d-01', $year, $month);
    $range_end = date('Y-m-t', strtotime($range_start));
}

$url_params = array();
if ($category !== '') {
    $url_params['category'] = $category;
}
if ($view !== 'month') {
    $url_params['view'] = $view;
}

$summary = eottae_calendar_summary_days($today, $category);
$month_events = eottae_calendar_events_for_month($year, $month, $category);
$month_grid = eottae_calendar_build_month_grid($year, $month, $month_events);

$list = eottae_calendar_list_events(array(
    'category'    => $category,
    'range_start' => $range_start,
    'range_end'   => $range_end,
    'limit'       => 200,
));

$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}
$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$nav_params = $url_params;
$nav_params['year'] = $prev_year;
$nav_params['month'] = $prev_month;
$prev_href = eottae_calendar_list_url($nav_params);
$nav_params['year'] = $next_year;
$nav_params['month'] = $next_month;
$next_href = eottae_calendar_list_url($nav_params);

$month_params = $url_params;
$month_params['view'] = 'month';
$month_params['year'] = $year;
$month_params['month'] = $month;
unset($month_params['range']);
$list_params = $url_params;
$list_params['view'] = 'list';
$list_params['year'] = $year;
$list_params['month'] = $month;
if ($range !== '') {
    $list_params['range'] = $range;
}

$create_href = $is_member
    ? eottae_calendar_create_url()
    : (function_exists('eottae_login_url') ? eottae_login_url(eottae_calendar_create_url()) : G5_BBS_URL.'/login.php');

g5_page_start('세부어때 캘린더');
?>

<main class="mypage-subpage sebu-cal-page" data-sebu-cal-page>
    <header class="sebu-cal-page__hero">
        <div class="sebu-cal-page__hero-main">
            <h1 class="sebu-cal-page__title">세부어때 캘린더</h1>
            <p class="sebu-cal-page__intro">세부의 공휴일, 세부톡 모임, 축제, 이벤트 일정을 한눈에 확인해보세요.</p>
            <div class="sebu-cal-page__actions">
                <a href="<?php echo $create_href; ?>" class="sebu-cal-btn sebu-cal-btn--register">
                    <span class="sebu-cal-btn__icon" aria-hidden="true"></span>
                    일정 등록하기
                </a>
                <div class="sebu-cal-page__actions-secondary">
                    <a href="<?php echo eottae_calendar_list_url(array_merge($url_params, array('range' => 'today', 'view' => 'list'))); ?>" class="sebu-cal-btn sebu-cal-btn--ghost">오늘 일정 보기</a>
                    <a href="<?php echo eottae_calendar_list_url(array_merge($url_params, array('range' => 'week', 'view' => 'list'))); ?>" class="sebu-cal-btn sebu-cal-btn--ghost">이번 주 일정 보기</a>
                </div>
            </div>
        </div>
        <?php
        if (function_exists('eottae_load_component')) {
            eottae_load_component('calendar-hero-art');
        }
        if (function_exists('eottae_render_calendar_hero_art')) {
            eottae_render_calendar_hero_art();
        }
        ?>
    </header>

    <section class="sebu-cal-summary" aria-label="오늘·내일·모레 일정 요약">
        <div class="sebu-cal-summary__grid">
            <?php foreach ($summary as $block) { ?>
            <article class="sebu-cal-summary__card">
                <header class="sebu-cal-summary__head">
                    <h2 class="sebu-cal-summary__label"><?php echo get_text($block['label']); ?></h2>
                    <span class="sebu-cal-summary__count"><?php echo number_format((int) $block['count']); ?>건</span>
                </header>
                <?php if (!empty($block['events'])) { ?>
                <ul class="sebu-cal-summary__list">
                    <?php foreach (array_slice($block['events'], 0, 3) as $event) { ?>
                    <li><a href="<?php echo $event['detail_href']; ?>"><?php echo get_text($event['title']); ?></a></li>
                    <?php } ?>
                </ul>
                <?php } else { ?>
                <p class="sebu-cal-summary__empty">등록된 일정이 없습니다.</p>
                <?php } ?>
            </article>
            <?php } ?>
        </div>
    </section>

    <?php eottae_calendar_render_filter_chips($category, array('view' => $view, 'year' => $year, 'month' => $month)); ?>

    <div class="sebu-cal-view-tabs" role="tablist" aria-label="캘린더 보기 방식">
        <a href="<?php echo eottae_calendar_list_url($month_params); ?>" class="sebu-cal-view-tabs__btn<?php echo $view === 'month' ? ' is-active' : ''; ?>" role="tab">월간</a>
        <a href="<?php echo eottae_calendar_list_url($list_params); ?>" class="sebu-cal-view-tabs__btn<?php echo $view === 'list' ? ' is-active' : ''; ?>" role="tab">리스트</a>
    </div>

    <?php if ($view === 'month') { ?>
    <section class="sebu-cal-month" aria-label="월간 캘린더">
        <div class="sebu-cal-month__nav">
            <a href="<?php echo $prev_href; ?>" class="sebu-cal-month__nav-btn" aria-label="이전 달">‹</a>
            <h2 class="sebu-cal-month__title"><?php echo $year; ?>년 <?php echo $month; ?>월</h2>
            <a href="<?php echo $next_href; ?>" class="sebu-cal-month__nav-btn" aria-label="다음 달">›</a>
        </div>
        <div class="sebu-cal-month__weekdays">
            <span>일</span><span>월</span><span>화</span><span>수</span><span>목</span><span>금</span><span>토</span>
        </div>
        <div class="sebu-cal-month__grid">
            <?php foreach ($month_grid as $week) { ?>
            <div class="sebu-cal-month__week">
                <?php foreach ($week as $cell) { ?>
                <div class="sebu-cal-month__cell<?php echo $cell ? '' : ' sebu-cal-month__cell--empty'; ?><?php echo ($cell && !empty($cell['is_today'])) ? ' is-today' : ''; ?>">
                    <?php if ($cell) { ?>
                    <div class="sebu-cal-month__day"><?php echo (int) $cell['day']; ?></div>
                    <div class="sebu-cal-month__events">
                        <?php foreach (array_slice($cell['events'], 0, 3) as $event) { ?>
                        <a href="<?php echo $event['detail_href']; ?>" class="sebu-cal-month__event <?php echo get_text($event['category_class']); ?>">
                            <span class="sebu-cal-month__event-badge <?php echo get_text($event['badge_class']); ?>"></span>
                            <span class="sebu-cal-month__event-title"><?php echo get_text($event['title']); ?></span>
                        </a>
                        <?php } ?>
                        <?php if (count($cell['events']) > 3) { ?>
                        <span class="sebu-cal-month__more">+<?php echo count($cell['events']) - 3; ?></span>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </section>
    <?php } else { ?>
    <section class="sebu-cal-list" aria-label="일정 리스트">
        <?php if ($range === 'today') { ?>
        <h2 class="sebu-cal-list__title">오늘 일정</h2>
        <?php } elseif ($range === 'week') { ?>
        <h2 class="sebu-cal-list__title">이번 주 일정</h2>
        <?php } else { ?>
        <h2 class="sebu-cal-list__title"><?php echo $year; ?>년 <?php echo $month; ?>월 일정</h2>
        <?php } ?>

        <?php if (!empty($list['rows'])) { ?>
        <div class="sebu-cal-list__items">
            <?php foreach ($list['rows'] as $event) {
                eottae_calendar_render_event_card($event, 'list', array(
                    'show_report' => true,
                    'member'      => $member ?? array(),
                    'is_admin'    => $is_admin ?? '',
                ));
            } ?>
        </div>
        <?php } else { ?>
        <div class="sebu-cal-empty">
            <p>표시할 일정이 없습니다.</p>
            <?php if ($is_member) { ?>
            <a href="<?php echo eottae_calendar_create_url(); ?>" class="sebu-cal-btn sebu-cal-btn--register">첫 일정 등록하기</a>
            <?php } ?>
        </div>
        <?php } ?>
    </section>
    <?php } ?>
</main>

<?php
include_once G5_PATH.'/components/eottae/calendar-report.php';
eottae_calendar_render_report_script();
g5_page_end();
