<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';
include_once G5_PATH.'/components/eottae/golf-join-card.php';

$filters = eottae_golf_join_parse_filters();
$posts = eottae_golf_join_list_posts($filters);
$venue_types = eottae_golf_join_venue_type_options();
$time_zones = eottae_golf_join_list_time_zone_options();
if (($filters['venue_type'] ?? '') === 'screen_golf') {
    $time_zones['evening'] = '야간';
}
$list_url = eottae_golf_join_list_url();
$create_url = eottae_golf_join_create_url();
$login_url = function_exists('eottae_login_url') ? eottae_login_url($list_url) : G5_BBS_URL.'/login.php';

$sort_labels = array(
    'seats'      => '빈자리순',
    'latest'     => '최신순',
    'round_date' => '라운드일 가까운순',
);

g5_page_start('골프조인');
?>

<main class="golf-join-page" id="golf-join-list">
    <header class="golf-join-topbar">
        <a href="<?php echo G5_URL; ?>/" class="golf-join-topbar__back" aria-label="뒤로가기">
            <span aria-hidden="true">←</span>
        </a>
        <h1 class="golf-join-topbar__title">골프조인</h1>
        <button type="button" class="golf-join-topbar__search" id="golf-join-search-open" aria-label="검색" aria-controls="golf-join-search-panel" aria-expanded="false">
            <span aria-hidden="true">⌕</span>
        </button>
    </header>

    <div class="golf-join-search-panel" id="golf-join-search-panel" hidden>
        <form method="get" action="<?php echo $list_url; ?>" class="golf-join-search-panel__form" role="search">
            <?php
            foreach ($filters as $key => $val) {
                if (in_array($key, array('q'), true) || $val === '' || $val === false) {
                    continue;
                }
                if ($key === 'exclude_full' && $val) {
                    echo '<input type="hidden" name="exclude_full" value="1">';
                    continue;
                }
                if (is_string($val) && $val !== '') {
                    echo '<input type="hidden" name="'.get_text($key).'" value="'.get_text($val).'">';
                }
            }
            ?>
            <input type="search" name="q" value="<?php echo get_text($filters['q'] ?? ''); ?>" class="golf-join-search-panel__input" placeholder="골프장, 제목, 소개글 검색" autocomplete="off">
            <button type="submit" class="golf-join-search-panel__submit">검색</button>
        </form>
    </div>

    <section class="golf-join-filters" aria-label="필터">
        <form method="get" action="<?php echo $list_url; ?>" class="golf-join-filters__form" id="golf-join-filter-form">
            <input type="hidden" name="q" value="<?php echo get_text($filters['q'] ?? ''); ?>">

            <div class="golf-join-filter-row golf-join-filter-row--scroll">
                <span class="golf-join-filter-row__label">유형</span>
                <div class="golf-join-chips">
                    <label class="golf-join-chip<?php echo ($filters['venue_type'] ?? '') === '' ? ' is-active' : ''; ?>">
                        <input type="radio" name="venue_type" value=""<?php echo ($filters['venue_type'] ?? '') === '' ? ' checked' : ''; ?>>전체
                    </label>
                    <?php foreach ($venue_types as $code => $meta) { ?>
                    <label class="golf-join-chip<?php echo ($filters['venue_type'] ?? '') === $code ? ' is-active' : ''; ?>">
                        <input type="radio" name="venue_type" value="<?php echo get_text($code); ?>"<?php echo ($filters['venue_type'] ?? '') === $code ? ' checked' : ''; ?>>
                        <?php echo get_text($meta['label']); ?>
                    </label>
                    <?php } ?>
                </div>
            </div>

            <div class="golf-join-filter-row golf-join-filter-row--scroll">
                <span class="golf-join-filter-row__label">날짜</span>
                <div class="golf-join-chips">
                    <?php
                    $date_presets = array(
                        ''         => '전체',
                        'today'    => '오늘',
                        'tomorrow' => '내일',
                        'week'     => '이번주',
                    );
                    foreach ($date_presets as $code => $label) {
                        $active = ($filters['date_preset'] ?? '') === $code;
                        ?>
                    <label class="golf-join-chip<?php echo $active ? ' is-active' : ''; ?>">
                        <input type="radio" name="date_preset" value="<?php echo get_text($code); ?>"<?php echo $active ? ' checked' : ''; ?>>
                        <?php echo get_text($label); ?>
                    </label>
                    <?php } ?>
                    <label class="golf-join-chip golf-join-chip--date<?php echo ($filters['date_preset'] ?? '') === 'custom' ? ' is-active' : ''; ?>">
                        <input type="radio" name="date_preset" value="custom"<?php echo ($filters['date_preset'] ?? '') === 'custom' ? ' checked' : ''; ?>>
                        날짜선택
                        <input type="date" name="date" class="golf-join-chip__date" value="<?php echo get_text($filters['date'] ?? ''); ?>" aria-label="라운드 날짜 선택">
                    </label>
                </div>
            </div>

            <div class="golf-join-filter-row golf-join-filter-row--scroll">
                <span class="golf-join-filter-row__label">시간대</span>
                <div class="golf-join-chips">
                    <label class="golf-join-chip<?php echo ($filters['time_zone'] ?? '') === '' ? ' is-active' : ''; ?>">
                        <input type="radio" name="time_zone" value=""<?php echo ($filters['time_zone'] ?? '') === '' ? ' checked' : ''; ?>>전체
                    </label>
                    <?php foreach ($time_zones as $code => $label) { ?>
                    <label class="golf-join-chip<?php echo ($filters['time_zone'] ?? '') === $code ? ' is-active' : ''; ?>">
                        <input type="radio" name="time_zone" value="<?php echo get_text($code); ?>"<?php echo ($filters['time_zone'] ?? '') === $code ? ' checked' : ''; ?>>
                        <?php echo get_text($label); ?>
                    </label>
                    <?php } ?>
                </div>
            </div>

            <div class="golf-join-filter-row golf-join-filter-row--tools">
                <label class="golf-join-toggle">
                    <input type="checkbox" name="exclude_full" value="1"<?php echo !empty($filters['exclude_full']) ? ' checked' : ''; ?>>
                    <span>모집완료 제외</span>
                </label>

                <label class="golf-join-sort">
                    <span class="golf-join-sort__label">정렬</span>
                    <select name="sort" class="golf-join-sort__select" aria-label="정렬">
                        <?php foreach ($sort_labels as $code => $label) { ?>
                        <option value="<?php echo get_text($code); ?>"<?php echo ($filters['sort'] ?? '') === $code ? ' selected' : ''; ?>>
                            <?php echo get_text($label); ?>
                        </option>
                        <?php } ?>
                    </select>
                </label>
            </div>
        </form>
    </section>

    <section class="golf-join-list-wrap" aria-label="골프조인 목록">
        <?php if (eottae_golf_join_use_mock_data()) { ?>
        <p class="golf-join-demo-note" role="status">샘플 데이터로 표시 중입니다. DB에 모집글이 등록되면 자동으로 실데이터로 전환됩니다.</p>
        <?php } ?>

        <?php if (empty($posts)) { ?>
        <div class="golf-join-empty">
            <p class="golf-join-empty__title">조건에 맞는 조인이 없습니다</p>
            <p class="golf-join-empty__desc">필터를 바꾸거나 직접 골프조인을 만들어 보세요.</p>
        </div>
        <?php } else { ?>
        <ul class="golf-join-list">
            <?php foreach ($posts as $post) {
                echo eottae_golf_join_card_html($post);
            } ?>
        </ul>
        <?php } ?>
    </section>

    <div class="golf-join-fab-wrap">
        <?php if ($is_member) { ?>
        <a href="<?php echo $create_url; ?>" class="golf-join-fab">골프조인 만들기</a>
        <?php } else { ?>
        <a href="<?php echo $login_url; ?>" class="golf-join-fab">로그인 후 만들기</a>
        <?php } ?>
    </div>
</main>

<?php
add_javascript('<script src="'.G5_JS_URL.'/eottae-golf-join-list.js" defer></script>', 25);
g5_page_end();
