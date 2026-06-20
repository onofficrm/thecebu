<?php
include_once(dirname(__FILE__).'/_init.php');

if (!function_exists('eottae_column_list') && is_file(G5_LIB_PATH.'/eottae-column.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-column.lib.php';
}
include_once G5_LIB_PATH.'/eottae-app-home.lib.php';
if (function_exists('eottae_column_ensure_schema')) {
    eottae_column_ensure_schema();
}

$g5['body_script'] = ' class="eottae-app-home-shell"';

$shop_table = defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop';
$community_table = defined('EOTTae_COMMUNITY_TABLE') ? EOTTae_COMMUNITY_TABLE : 'community';
$event_table = defined('EOTTae_EVENT_TABLE') ? EOTTae_EVENT_TABLE : 'event';
$estate_table = defined('EOTTae_ESTATE_TABLE') ? EOTTae_ESTATE_TABLE : 'estate';
$job_table = defined('EOTTae_JOB_TABLE') ? EOTTae_JOB_TABLE : 'job';
$market_table = defined('EOTTae_MARKET_TABLE') ? EOTTae_MARKET_TABLE : 'market';

$app_menu = array(
    array('label' => '맛집', 'icon' => '🍽️', 'href' => function_exists('eottae_shop_list_url') ? eottae_shop_list_url(array('sca' => '맛집')) : G5_BBS_URL.'/board.php?bo_table='.$shop_table),
    array('label' => '업체', 'icon' => '🏪', 'href' => function_exists('eottae_shop_list_url') ? eottae_shop_list_url() : G5_BBS_URL.'/board.php?bo_table='.$shop_table),
    array('label' => '병원', 'icon' => '🏥', 'href' => function_exists('eottae_shop_list_url') ? eottae_shop_list_url(array('sca' => '병원')) : G5_BBS_URL.'/board.php?bo_table='.$shop_table),
    array('label' => '부동산', 'icon' => '🏠', 'href' => function_exists('eottae_board_list_url') ? eottae_board_list_url($estate_table) : G5_BBS_URL.'/board.php?bo_table='.$estate_table),
    array('label' => '구인구직', 'icon' => '💼', 'href' => function_exists('eottae_board_list_url') ? eottae_board_list_url($job_table) : G5_BBS_URL.'/board.php?bo_table='.$job_table),
    array('label' => '중고장터', 'icon' => '🛍️', 'href' => function_exists('eottae_market_list_url') ? eottae_market_list_url() : G5_BBS_URL.'/board.php?bo_table='.$market_table),
    array('label' => '세부톡', 'icon' => '💬', 'href' => function_exists('eottae_talkroom_public_url') ? eottae_talkroom_public_url() : G5_URL.'/page/eottae-talk.php'),
    array('label' => '골프조인', 'icon' => '⛳', 'href' => function_exists('eottae_golf_join_list_url') ? eottae_golf_join_list_url() : G5_URL.'/page/eottae-golf-join.php'),
    array('label' => '컬럼', 'icon' => '🎧', 'href' => function_exists('eottae_column_list_url') ? eottae_column_list_url() : G5_URL.'/column/'),
    array('label' => '이벤트', 'icon' => '🎁', 'href' => function_exists('eottae_board_list_url') ? eottae_board_list_url($event_table) : G5_BBS_URL.'/board.php?bo_table='.$event_table),
    array('label' => '생활지도', 'icon' => '🗺️', 'href' => G5_URL.'/page/map-locator.php'),
    array('label' => '쪽지', 'icon' => '✉️', 'href' => function_exists('eottae_message_url') ? eottae_message_url() : G5_URL.'/page/eottae-messages.php'),
);

$latest_columns = function_exists('eottae_column_list') ? eottae_column_list(array('limit' => 3)) : array();
$login_url = function_exists('eottae_login_url') ? eottae_login_url(G5_URL.'/page/eottae-app-home.php') : G5_BBS_URL.'/login.php';
$mypage_url = function_exists('eottae_mypage_url') ? eottae_mypage_url() : G5_URL.'/page/eottae-mypage.php';
$interest_options = eottae_app_interest_options();
$selected_interest = eottae_app_normalize_interest($_GET['interest'] ?? '');
$selected_interest_meta = $selected_interest !== '' ? $interest_options[$selected_interest] : array();
$selected_interest_label = (string) ($selected_interest_meta['label'] ?? '전체');
$selected_shop_category = (string) ($selected_interest_meta['shop_category'] ?? '');
$member_summary = !empty($is_member) ? eottae_app_member_summary($member['mb_id'] ?? '') : array();
$member_cards = isset($member_summary['cards']) && is_array($member_summary['cards']) ? $member_summary['cards'] : array();
$active_coupons = !empty($is_member) ? eottae_app_active_coupons($member['mb_id'] ?? '', 3) : array();
$nearby_shops = eottae_app_latest_shop_cards(4, $selected_shop_category);
if (empty($nearby_shops) && $selected_shop_category !== '') {
    $nearby_shops = eottae_app_latest_shop_cards(4);
}
$sponsor_shops = eottae_app_latest_shop_cards(3, $selected_shop_category);
if (empty($sponsor_shops) && $selected_shop_category !== '') {
    $sponsor_shops = eottae_app_latest_shop_cards(3);
}
$talk_preview = eottae_app_talk_preview(4);
$talk_rooms = isset($talk_preview['rooms']) && is_array($talk_preview['rooms']) ? $talk_preview['rooms'] : array();
$attendance = array('checked' => false, 'streak' => 0);
if (!empty($is_member) && is_file(G5_LIB_PATH.'/eottae-promo-coupon.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-promo-coupon.lib.php';
    $attendance['checked'] = function_exists('eottae_attendance_checked_today') ? eottae_attendance_checked_today($member['mb_id'] ?? '') : false;
    $attendance['streak'] = function_exists('eottae_attendance_get_streak') ? eottae_attendance_get_streak($member['mb_id'] ?? '') : 0;
}
$app_logo_url = function_exists('eottae_site_logo_url') ? eottae_site_logo_url('logo_path') : '';
if ($app_logo_url === '' && is_file(G5_PATH.'/img/logo/cebu-logo-main-reference.png')) {
    $app_logo_url = G5_URL.'/img/logo/cebu-logo-main-reference.png';
}

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-app-home.css">', 24);

g5_page_start('세부어때 앱 홈');
?>
<main class="eottae-app-home" data-app-home data-interest="<?php echo get_text($selected_interest); ?>">
    <header class="eottae-app-top">
        <a href="<?php echo G5_URL; ?>/page/eottae-app-home.php" class="eottae-app-top__logo" aria-label="세부어때 홈">
            <?php if ($app_logo_url !== '') { ?>
            <img src="<?php echo get_text($app_logo_url); ?>" alt="세부어때" class="eottae-app-top__logo-img">
            <?php } else { ?>
            <span>세부어때</span>
            <?php } ?>
        </a>
        <div class="eottae-app-top__actions">
            <a href="<?php echo G5_URL; ?>/page/eottae-notifications.php" class="eottae-app-top__icon" aria-label="알림">🔔</a>
            <?php if (!empty($is_member)) { ?>
            <a href="<?php echo $mypage_url; ?>" class="eottae-app-top__login">MY</a>
            <?php } else { ?>
            <a href="<?php echo $login_url; ?>" class="eottae-app-top__login">로그인</a>
            <?php } ?>
        </div>
    </header>

    <section class="eottae-app-search">
        <form action="<?php echo G5_BBS_URL; ?>/search.php" method="get">
            <input type="search" name="stx" placeholder="세부 맛집, 병원, 부동산 검색" autocomplete="off">
            <button type="submit">검색</button>
        </form>
    </section>

    <section class="eottae-app-menu" aria-label="앱 주요 메뉴">
        <?php foreach ($app_menu as $item) { ?>
        <a href="<?php echo get_text($item['href']); ?>" class="eottae-app-menu__item">
            <span class="eottae-app-menu__icon" aria-hidden="true"><?php echo $item['icon']; ?></span>
            <strong><?php echo get_text($item['label']); ?></strong>
        </a>
        <?php } ?>
    </section>

    <section class="eottae-app-banner">
        <div>
            <span>오늘의 세부</span>
            <strong>내 주변 정보와 세부톡 소식을 한 번에</strong>
            <p>앱 알림을 켜두면 새 메시지와 생활정보를 바로 받을 수 있어요.</p>
        </div>
        <a href="<?php echo G5_URL; ?>/page/eottae-notifications.php" data-app-track="home_notification">알림 설정</a>
    </section>

    <section class="eottae-app-quick">
        <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php" data-app-track="coupon_quick">🛡️ 쿠폰·혜택</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-briefing.php" data-app-track="home_briefing">⏱️ 오늘 브리핑</a>
        <a href="<?php echo function_exists('eottae_column_list_url') ? eottae_column_list_url() : G5_URL.'/column/'; ?>" data-app-track="home_column">🎧 음성 컬럼</a>
    </section>

    <section class="eottae-app-section eottae-app-onboarding" data-app-onboarding aria-labelledby="app-onboarding-title">
        <div class="eottae-app-section__head">
            <h2 id="app-onboarding-title">앱 시작 설정</h2>
            <button type="button" data-app-onboarding-hide>다음에</button>
        </div>
        <p class="eottae-app-onboarding__lead">관심사를 선택하면 앱 홈의 업체, 쿠폰, 세부톡 추천이 더 맞춰집니다.</p>
        <div class="eottae-app-interest-grid">
            <?php foreach ($interest_options as $code => $meta) { ?>
            <button type="button" data-app-interest="<?php echo get_text($code); ?>" class="<?php echo $selected_interest === $code ? 'is-active' : ''; ?>"><?php echo get_text($meta['label'] ?? $code); ?></button>
            <?php } ?>
        </div>
        <div class="eottae-app-onboarding__actions">
            <a href="<?php echo $login_url; ?>">로그인 유지</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-notifications.php">알림 허용</a>
        </div>
    </section>

    <?php if (empty($is_member)) { ?>
    <section class="eottae-app-login-card">
        <div>
            <strong>로그인하고 최대 혜택 받으세요</strong>
            <p>세부톡 알림, 쪽지, 저장한 업체, 쿠폰을 앱에서 바로 확인할 수 있습니다.</p>
        </div>
        <a href="<?php echo $login_url; ?>">로그인 후 혜택 받기</a>
    </section>
    <?php } else { ?>
    <section class="eottae-app-section eottae-app-personal" aria-labelledby="app-personal-title">
        <div class="eottae-app-section__head">
            <h2 id="app-personal-title"><?php echo get_text($member['mb_nick'] ?? $member['mb_id'] ?? '회원'); ?>님을 위한 홈</h2>
            <a href="<?php echo $mypage_url; ?>">MY</a>
        </div>
        <div class="eottae-app-personal-grid">
            <?php foreach ($member_cards as $card) { ?>
            <a href="<?php echo get_text($card['href'] ?? '#'); ?>" class="eottae-app-personal-card<?php echo (int) ($card['value'] ?? 0) > 0 ? ' is-alert' : ''; ?>">
                <span><?php echo get_text($card['label'] ?? ''); ?></span>
                <strong><?php echo number_format((int) ($card['value'] ?? 0)); ?></strong>
                <em><?php echo get_text($card['desc'] ?? ''); ?></em>
            </a>
            <?php } ?>
        </div>
    </section>
    <?php } ?>

    <section class="eottae-app-section" aria-labelledby="app-nearby-title">
        <div class="eottae-app-section__head">
            <h2 id="app-nearby-title"><?php echo $selected_interest !== '' ? get_text($selected_interest_label).' 추천' : '내주변 추천'; ?></h2>
            <a href="<?php echo G5_URL; ?>/page/eottae-app-nearby.php">앱형 보기</a>
        </div>
        <div class="eottae-app-shop-list">
            <?php if (empty($nearby_shops)) { ?>
            <p class="eottae-app-empty">표시할 업체가 없습니다.</p>
            <?php } else { foreach ($nearby_shops as $shop) { ?>
            <article class="eottae-app-shop-card">
                <a href="<?php echo get_text($shop['href'] ?? '#'); ?>" class="eottae-app-shop-card__main">
                    <span class="eottae-app-shop-card__thumb"<?php echo !empty($shop['thumb']) ? ' style="background-image:url('.get_text($shop['thumb']).')"' : ''; ?>></span>
                    <span class="eottae-app-shop-card__body">
                        <em><?php echo get_text($shop['category'] ?: '업체'); ?><?php echo !empty($shop['status']) ? ' · '.get_text($shop['status']) : ''; ?></em>
                        <strong><?php echo get_text($shop['title']); ?></strong>
                        <small><?php echo get_text($shop['region'] ?: $shop['address']); ?></small>
                    </span>
                </a>
                <div class="eottae-app-shop-card__actions">
                    <a href="<?php echo get_text($shop['phone_href'] ?? '#'); ?>" data-app-track="shop_phone" data-track-label="<?php echo get_text($shop['title'] ?? ''); ?>">전화</a>
                    <a href="<?php echo get_text($shop['map_href'] ?? '#'); ?>" target="_blank" rel="noopener noreferrer" data-app-track="shop_map" data-track-label="<?php echo get_text($shop['title'] ?? ''); ?>">길찾기</a>
                </div>
            </article>
            <?php }} ?>
        </div>
    </section>

    <section class="eottae-app-section eottae-app-sponsored" aria-labelledby="app-sponsored-title">
        <div class="eottae-app-section__head">
            <h2 id="app-sponsored-title">추천 업체·쿠폰 노출</h2>
            <a href="<?php echo G5_URL; ?>/page/eottae-ad-register.php">광고 문의</a>
        </div>
        <div class="eottae-app-sponsored-list">
            <?php if (empty($sponsor_shops)) { ?>
            <a href="<?php echo G5_URL; ?>/page/eottae-ad-register.php" class="eottae-app-sponsored-card">
                <span>Sponsored</span>
                <strong>앱 홈 추천 업체 자리를 준비 중입니다</strong>
                <em>쿠폰·전화·길찾기 클릭과 함께 운영 지표로 확인할 수 있습니다.</em>
            </a>
            <?php } else { foreach ($sponsor_shops as $shop) { ?>
            <a href="<?php echo get_text($shop['href'] ?? '#'); ?>" class="eottae-app-sponsored-card" data-app-track="sponsor_click" data-track-label="<?php echo get_text($shop['title'] ?? ''); ?>">
                <span>Sponsored · <?php echo get_text($shop['category'] ?: '추천'); ?></span>
                <strong><?php echo get_text($shop['title']); ?></strong>
                <em><?php echo get_text($shop['status'] ?: '쿠폰/업체 노출 가능'); ?></em>
            </a>
            <?php }} ?>
        </div>
    </section>

    <section class="eottae-app-section" aria-labelledby="app-talk-title">
        <div class="eottae-app-section__head">
            <h2 id="app-talk-title">지금 뜨는 세부톡</h2>
            <a href="<?php echo get_text($talk_preview['list_url'] ?? G5_URL.'/page/eottae-talk.php'); ?>">전체보기</a>
        </div>
        <div class="eottae-app-talk-list">
            <?php if (empty($talk_rooms)) { ?>
            <p class="eottae-app-empty">아직 표시할 톡방이 없습니다.</p>
            <?php } else { foreach ($talk_rooms as $room) {
                $room_title = $room['title'] ?? ($room['room_name'] ?? '세부톡');
                $room_href = !empty($room['enter_url']) ? $room['enter_url'] : (function_exists('eottae_talkroom_enter_url') ? eottae_talkroom_enter_url((int) ($room['room_id'] ?? 0)) : '#');
                ?>
            <a href="<?php echo get_text($room_href); ?>" class="eottae-app-talk-card" data-app-track="talk_click" data-track-label="<?php echo get_text($room_title); ?>">
                <span><?php echo get_text($room['emoji'] ?? '💬'); ?></span>
                <strong><?php echo get_text($room_title); ?></strong>
                <em>멤버 <?php echo number_format((int) ($room['member_count'] ?? 0)); ?> · 글 <?php echo number_format((int) ($room['post_count'] ?? 0)); ?></em>
            </a>
            <?php }} ?>
        </div>
    </section>

    <section class="eottae-app-section" aria-labelledby="app-benefit-title">
        <div class="eottae-app-section__head">
            <h2 id="app-benefit-title">오늘 받을 혜택</h2>
            <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php">쿠폰함</a>
        </div>
        <div class="eottae-app-benefit-list">
            <?php if (!empty($active_coupons)) { foreach ($active_coupons as $coupon) { ?>
            <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php" class="eottae-app-benefit-card" data-app-track="coupon_click" data-track-label="<?php echo get_text($coupon['cp_title'] ?? '쿠폰 혜택'); ?>">
                <span>보유 쿠폰</span>
                <strong><?php echo get_text($coupon['cp_title'] ?? '쿠폰 혜택'); ?></strong>
                <em><?php echo get_text($coupon['cp_desc'] ?? '매장에서 바로 사용해 보세요.'); ?></em>
            </a>
            <?php }} else { ?>
            <a href="<?php echo !empty($is_member) ? G5_URL.'/page/eottae-coupons.php' : $login_url; ?>" class="eottae-app-benefit-card" data-app-track="coupon_empty_click">
                <span>쿠폰·혜택</span>
                <strong><?php echo !empty($is_member) ? '받을 수 있는 쿠폰을 확인하세요' : '로그인하면 웰컴 쿠폰을 받을 수 있어요'; ?></strong>
                <em>업체 혜택과 이벤트를 한곳에서 모아볼 수 있습니다.</em>
            </a>
            <?php } ?>
        </div>
    </section>

    <section class="eottae-app-section eottae-app-reward" aria-labelledby="app-reward-title">
        <div class="eottae-app-section__head">
            <h2 id="app-reward-title">오늘의 앱 미션</h2>
            <a href="<?php echo G5_URL; ?>/page/eottae-points.php">포인트</a>
        </div>
        <div class="eottae-app-mission-list">
            <button type="button" class="eottae-app-mission-card<?php echo !empty($attendance['checked']) ? ' is-done' : ''; ?>" data-app-checkin<?php echo empty($is_member) ? ' data-login-url="'.get_text($login_url).'"' : ''; ?>>
                <span><?php echo !empty($attendance['checked']) ? '완료' : '미션'; ?></span>
                <strong>앱 출석 체크</strong>
                <em data-app-checkin-status><?php echo !empty($is_member) ? '연속 '.number_format((int) $attendance['streak']).'일 · '.(!empty($attendance['checked']) ? '오늘 출석 완료' : '눌러서 출석하기') : '로그인 후 출석 리워드를 받을 수 있어요'; ?></em>
            </button>
            <a href="<?php echo G5_URL; ?>/page/eottae-talk.php" class="eottae-app-mission-card" data-app-track="mission_talk">
                <span>참여</span>
                <strong>세부톡 참여</strong>
                <em>관심 톡방에 들어가 새 소식을 확인하세요.</em>
            </a>
            <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php" class="eottae-app-mission-card" data-app-track="mission_coupon">
                <span>혜택</span>
                <strong>쿠폰 확인</strong>
                <em>오늘 받을 수 있는 쿠폰을 확인하세요.</em>
            </a>
        </div>
    </section>

    <section class="eottae-app-section">
        <div class="eottae-app-section__head">
            <h2>음성으로 듣는 최신 컬럼</h2>
            <a href="<?php echo function_exists('eottae_column_list_url') ? eottae_column_list_url() : G5_URL.'/column/'; ?>">전체보기</a>
        </div>
        <div class="eottae-app-column-list">
            <?php if (empty($latest_columns)) { ?>
            <p class="eottae-app-empty">표시할 컬럼이 없습니다.</p>
            <?php } else { foreach ($latest_columns as $column) { ?>
            <a href="<?php echo get_text($column['view_url'] ?? '#'); ?>" class="eottae-app-column">
                <span><?php echo get_text($column['category_label'] ?? '컬럼'); ?></span>
                <strong><?php echo get_text($column['wr_subject'] ?? ''); ?></strong>
                <em><?php echo get_text($column['author_name'] ?? ''); ?> · <?php echo get_text($column['read_time_label'] ?? '음성읽기 지원'); ?></em>
            </a>
            <?php }} ?>
        </div>
    </section>

    <section class="eottae-app-section">
        <div class="eottae-app-section__head">
            <h2>바로가기</h2>
        </div>
        <div class="eottae-app-shortcuts">
            <a href="<?php echo G5_URL; ?>/page/eottae-talk.php">세부톡 참여</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-calendar.php">세부 일정</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-cost-calculator.php">생활비 계산기</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-ad-register.php">광고 등록</a>
        </div>
    </section>
</main>
<script>
(function () {
  var root = document.querySelector('[data-app-home]');
  if (!root) return;
  var storageKey = 'eottae_app_interest';
  var hiddenKey = 'eottae_app_onboarding_hidden';
  var endpoint = '<?php echo G5_URL; ?>/proc/eottae-app-event.php';
  var interest = root.getAttribute('data-interest') || '';

  function postEvent(eventName, label) {
    try {
      fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          action: 'event',
          event: eventName,
          label: label || '',
          interest: interest || localStorage.getItem(storageKey) || '',
          url: location.href
        })
      }).catch(function () {});
    } catch (e) {}
  }

  if (!interest) {
    try {
      var saved = localStorage.getItem(storageKey) || '';
      if (saved) {
        location.replace('<?php echo G5_URL; ?>/page/eottae-app-home.php?interest=' + encodeURIComponent(saved));
        return;
      }
    } catch (e) {}
  }

  try {
    if (localStorage.getItem(hiddenKey) === '1') {
      var onboarding = document.querySelector('[data-app-onboarding]');
      if (onboarding && interest) onboarding.classList.add('is-collapsed');
    }
  } catch (e) {}

  document.querySelectorAll('[data-app-interest]').forEach(function (button) {
    button.addEventListener('click', function () {
      var code = button.getAttribute('data-app-interest') || '';
      try {
        localStorage.setItem(storageKey, code);
        localStorage.removeItem(hiddenKey);
      } catch (e) {}
      postEvent('onboarding_interest', button.textContent || code);
      location.href = '<?php echo G5_URL; ?>/page/eottae-app-home.php?interest=' + encodeURIComponent(code);
    });
  });

  var hide = document.querySelector('[data-app-onboarding-hide]');
  if (hide) {
    hide.addEventListener('click', function () {
      var onboarding = document.querySelector('[data-app-onboarding]');
      if (onboarding) onboarding.classList.add('is-collapsed');
      try { localStorage.setItem(hiddenKey, '1'); } catch (e) {}
      postEvent('onboarding_skip', '');
    });
  }

  document.querySelectorAll('[data-app-track]').forEach(function (el) {
    el.addEventListener('click', function () {
      postEvent(el.getAttribute('data-app-track') || 'app_click', el.getAttribute('data-track-label') || el.textContent || '');
    });
  });

  var checkin = document.querySelector('[data-app-checkin]');
  if (checkin) {
    checkin.addEventListener('click', function () {
      var loginUrl = checkin.getAttribute('data-login-url') || '';
      if (loginUrl) {
        location.href = loginUrl;
        return;
      }
      checkin.disabled = true;
      fetch(endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'checkin', interest: interest || localStorage.getItem(storageKey) || '', url: location.href})
      }).then(function (res) {
        return res.json();
      }).then(function (data) {
        var status = checkin.querySelector('[data-app-checkin-status]');
        if (status) status.textContent = data.message || '처리되었습니다.';
        checkin.classList.add('is-done');
      }).catch(function () {
        checkin.disabled = false;
      });
    });
  }

  postEvent('home_view', document.title || 'app home');
})();
</script>
<?php
g5_page_end();
