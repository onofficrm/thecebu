<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-mypage.php'));
}

include_once G5_LIB_PATH.'/eottae-talkroom.lib.php';
include_once G5_LIB_PATH.'/eottae-talkroom-dashboard.lib.php';
include_once G5_LIB_PATH.'/eottae-coupon.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';
include_once G5_LIB_PATH.'/eottae-briefing.lib.php';
include_once G5_LIB_PATH.'/eottae-member-growth.lib.php';
include_once G5_LIB_PATH.'/eottae-message.lib.php';
include_once G5_LIB_PATH.'/eottae-notification.lib.php';
if (is_file(G5_LIB_PATH.'/eottae-language-meta.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-language-meta.lib.php';
}
include_once G5_PATH.'/components/eottae/member-growth-display.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';
include_once G5_PATH.'/components/eottae/public-ai-admin-nav.php';
include_once G5_PATH.'/components/eottae/column-admin-mypage.php';
if (is_file(G5_LIB_PATH.'/eottae-public-ai.lib.php')) {
    include_once G5_LIB_PATH.'/eottae-public-ai.lib.php';
}

$is_biz = eottae_is_business_member($member);
$point = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
$coupon_count = eottae_coupon_count_active($member['mb_id']);
$pending_replies = $is_biz ? eottae_business_pending_replies_count($member['mb_id']) : 0;
$my_review_count = count(eottae_get_member_reviews($member['mb_id'], 100));
$saved_count = count(eottae_get_saved_shop_ids($member['mb_id'], 100));
$inquiry_count = count(eottae_get_member_inquiries($member['mb_id'], 100));
$my_shop_posts = $is_biz ? eottae_business_shop_posts($member['mb_id'], 20) : array();
$my_talk_hub = function_exists('eottae_talkroom_mypage_hub_summary')
    ? eottae_talkroom_mypage_hub_summary($member['mb_id'])
    : array();
$mypage_talk_url = function_exists('eottae_mypage_talk_url')
    ? eottae_mypage_talk_url()
    : G5_URL.'/mypage/talk.php';
$growth_profile = function_exists('eottae_member_growth_get_profile')
    ? eottae_member_growth_get_profile($member['mb_id'])
    : array();
$badges_url = function_exists('eottae_member_growth_mypage_url')
    ? eottae_member_growth_mypage_url()
    : G5_URL.'/mypage/badges.php';
$featured_members = function_exists('eottae_member_growth_list_featured')
    ? eottae_member_growth_list_featured('', false, 3)
    : array();

$hub_room_count = (int) ($my_talk_hub['room_count'] ?? 0);
$hub_new_posts = (int) ($my_talk_hub['new_posts'] ?? 0);
$hub_new_comments = (int) ($my_talk_hub['new_comments'] ?? 0);
$hub_notifications = (int) ($my_talk_hub['notifications'] ?? 0);
$hub_owner_tasks = (int) ($my_talk_hub['owner_tasks'] ?? 0);
$hub_activity_total = $hub_new_posts + $hub_new_comments + $hub_notifications + $hub_owner_tasks;
$my_comment_summary = function_exists('eottae_mypage_my_comment_summary') ? eottae_mypage_my_comment_summary($member['mb_id']) : array('count' => 0, 'latest' => null);
$my_comment_count = (int) ($my_comment_summary['count'] ?? 0);

$challenge_summary = function_exists('eottae_challenge_my_summary') ? eottae_challenge_my_summary($member['mb_id']) : array();
$challenge_label = '챌린지';
if ((int) ($challenge_summary['entry_count'] ?? 0) > 0) {
    $challenge_label .= ' ('.number_format((int) $challenge_summary['entry_count']).')';
}

$badges_label = '내 등급/뱃지';
if (!empty($growth_profile['total_score'])) {
    $badges_label .= ' ('.number_format((int) $growth_profile['total_score']).'점)';
}

$mypage_menu_groups = array();

$coupon_menu_items = array(
    array('label' => '쿠폰함', 'href' => G5_URL.'/page/eottae-coupons.php', 'tone' => 'coupon-wallet'),
);
if ($is_biz) {
    $coupon_menu_items[] = array('label' => '쿠폰 발행', 'href' => G5_URL.'/page/eottae-business-coupons.php', 'tone' => 'coupon-issue');
}
$coupon_menu_items[] = array('label' => '쿠폰 안내', 'href' => G5_URL.'/page/eottae-coupon-guide.php', 'tone' => 'coupon-guide');
$mypage_menu_groups[] = array('title' => '쿠폰', 'items' => $coupon_menu_items);

$review_label = '내 리뷰';
if (!$is_biz && $my_review_count > 0) {
    $review_label .= ' ('.$my_review_count.')';
}
$saved_label = '찜·최근';
if ($saved_count > 0) {
    $saved_label .= ' ('.$saved_count.')';
}
$inquiry_label = '문의';
if ($inquiry_count > 0) {
    $inquiry_label .= ' ('.$inquiry_count.')';
}
$message_unread_count = function_exists('eottae_message_unread_count') ? eottae_message_unread_count($member['mb_id']) : 0;
$message_summary = function_exists('eottae_message_mypage_summary')
    ? eottae_message_mypage_summary($member['mb_id'])
    : array(
        'unread_count' => $message_unread_count,
        'thread_count' => 0,
        'latest' => null,
        'summary_line' => $message_unread_count > 0 ? '읽지 않은 쪽지가 있습니다.' : '쪽지를 확인해 보세요.',
    );
$message_label = '쪽지';
if ($message_unread_count > 0) {
    $message_label .= ' ('.number_format($message_unread_count).')';
}
$notification_summary = function_exists('eottae_mypage_notification_summary')
    ? eottae_mypage_notification_summary($member['mb_id'])
    : array(
        'total' => $message_unread_count + $my_comment_count + $hub_activity_total,
        'message_unread' => $message_unread_count,
        'comment_count' => $my_comment_count,
        'talk_activity' => $hub_activity_total,
        'comment_summary' => $my_comment_summary,
        'talk_hub' => $my_talk_hub,
    );
$notification_total = (int) ($notification_summary['total'] ?? 0);
$notification_items = function_exists('eottae_notification_hub_items')
    ? eottae_notification_hub_items($member['mb_id'], $notification_summary)
    : array();
$notification_hub_url = function_exists('eottae_notification_hub_url')
    ? eottae_notification_hub_url()
    : G5_URL.'/page/eottae-notifications.php';
$talk_label = '내 세부톡';
if ($hub_activity_total > 0) {
    $talk_label .= ' ('.number_format($hub_activity_total).')';
}

$mypage_menu_groups[] = array(
    'title' => '활동 & 저장',
    'items' => array(
        array('label' => '포인트', 'href' => G5_URL.'/page/eottae-points.php', 'tone' => 'point'),
        array('label' => $review_label, 'href' => G5_URL.'/page/eottae-my-reviews.php', 'tone' => 'default'),
        array('label' => $saved_label, 'href' => G5_URL.'/page/eottae-saved-shops.php', 'tone' => 'default'),
        array('label' => $inquiry_label, 'href' => G5_URL.'/page/eottae-inquiries.php', 'tone' => 'default'),
        array('label' => $message_label, 'href' => eottae_message_url(), 'tone' => $message_unread_count > 0 ? 'talk' : 'default'),
        array('label' => '이벤트', 'href' => G5_URL.'/page/eottae-events.php', 'tone' => 'default'),
        array('label' => '내 활동', 'href' => G5_BBS_URL.'/board.php?bo_table='.EOTTae_COMMUNITY_TABLE, 'tone' => 'default'),
    ),
);

$mypage_menu_groups[] = array(
    'title' => '세부톡 & 성장',
    'items' => array(
        array('label' => $talk_label, 'href' => $mypage_talk_url, 'tone' => 'talk'),
        array('label' => $badges_label, 'href' => $badges_url, 'tone' => 'growth'),
        array('label' => $challenge_label, 'href' => function_exists('eottae_challenge_mypage_url') ? eottae_challenge_mypage_url() : G5_URL.'/mypage/challenges.php', 'tone' => 'growth'),
    ),
);

$content_menu_items = array(
    array('label' => function_exists('eottae_column_menu_label') ? eottae_column_menu_label() : '컬럼', 'href' => function_exists('eottae_column_list_url') ? eottae_column_list_url() : G5_URL.'/column/', 'tone' => 'content'),
);
if (function_exists('eottae_column_is_columnist') && eottae_column_is_columnist($member['mb_id'])) {
    $content_menu_items[] = array(
        'label' => '내 컬럼',
        'href' => function_exists('eottae_column_mypage_url') ? eottae_column_mypage_url() : G5_URL.'/mypage/column.php',
        'tone' => 'content',
    );
    $content_menu_items[] = array(
        'label' => '프로필 설정',
        'href' => function_exists('eottae_column_profile_edit_url') ? eottae_column_profile_edit_url() : G5_URL.'/column/profile.php',
        'tone' => 'content',
    );
}
$mypage_menu_groups[] = array('title' => '콘텐츠', 'items' => $content_menu_items);

$mypage_menu_groups[] = array(
    'title' => '계정',
    'items' => array(
        array('label' => '정보수정', 'href' => G5_BBS_URL.'/member_confirm.php?url='.urlencode(G5_BBS_URL.'/register_form.php'), 'tone' => 'account'),
        array('label' => '로그아웃', 'href' => G5_BBS_URL.'/logout.php', 'tone' => 'account-muted'),
    ),
);

if ($is_admin === 'super') {
    include_once G5_LIB_PATH.'/eottae-column.lib.php';
    include_once G5_LIB_PATH.'/eottae-column-admin-authors.lib.php';
    $talk_kicked_count = function_exists('eottae_talkroom_admin_kicked_count') ? eottae_talkroom_admin_kicked_count() : 0;
    $talk_report_pending = function_exists('eottae_talkroom_admin_pending_report_count') ? eottae_talkroom_admin_pending_report_count() : 0;
    $public_ai_pending = function_exists('eottae_public_ai_pending_count') ? eottae_public_ai_pending_count() : 0;
    $column_pending = function_exists('eottae_column_pending_application_count')
        ? eottae_column_pending_application_count()
        : 0;
    $admin_menu_items = array(
        array('label' => '톡방 목록', 'href' => function_exists('eottae_talkroom_admin_rooms_url') ? eottae_talkroom_admin_rooms_url() : G5_URL.'/page/eottae-admin-talk-rooms.php', 'tone' => 'admin'),
        array(
            'label' => '강퇴 회원'.($talk_kicked_count > 0 ? ' ('.number_format($talk_kicked_count).')' : ''),
            'href' => function_exists('eottae_talkroom_admin_kicked_url') ? eottae_talkroom_admin_kicked_url() : G5_URL.'/page/eottae-admin-talk-kicked.php',
            'tone' => 'admin',
        ),
        array(
            'label' => '신고 관리'.($talk_report_pending > 0 ? ' ('.number_format($talk_report_pending).')' : ''),
            'href' => function_exists('eottae_talkroom_admin_reports_url') ? eottae_talkroom_admin_reports_url('pending') : G5_URL.'/page/eottae-admin-talk-reports.php?status=pending',
            'tone' => 'admin',
        ),
        array(
            'label' => '공개단톡 AI'.($public_ai_pending > 0 ? ' ('.number_format($public_ai_pending).')' : ''),
            'href' => function_exists('eottae_public_ai_mypage_admin_url') ? eottae_public_ai_mypage_admin_url() : G5_URL.'/page/eottae-admin-public-ai.php',
            'tone' => 'admin',
        ),
        array(
            'label' => '칼럼니스트 신청'.($column_pending > 0 ? ' ('.number_format($column_pending).')' : ''),
            'href' => eottae_column_admin_url(array('tab' => 'applications')),
            'tone' => 'admin-highlight',
        ),
        array(
            'label' => '컬럼 관리',
            'href' => eottae_column_admin_url(),
            'tone' => 'admin',
        ),
        array(
            'label' => '칼럼니스트 관리',
            'href' => function_exists('eottae_column_admin_authors_url')
                ? eottae_column_admin_authors_url()
                : G5_URL.'/page/eottae-admin-column-authors.php',
            'tone' => 'admin',
        ),
    );
    $mypage_menu_groups[] = array('title' => '관리', 'items' => $admin_menu_items);
}

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-my-talk.css">', 22);
if (function_exists('eottae_briefing_load_assets')) {
    eottae_briefing_load_assets();
}

$mypage_menu_html = '';
ob_start();
?>
    <nav class="mypage-menu-groups mypage-menu-groups--top" aria-label="마이페이지 메뉴">
        <?php foreach ($mypage_menu_groups as $menu_group) {
            if (empty($menu_group['items'])) {
                continue;
            }
            ?>
        <section class="mypage-menu-group">
            <?php if (!empty($menu_group['title'])) { ?>
            <h2 class="mypage-menu-group__title"><?php echo get_text($menu_group['title']); ?></h2>
            <?php } ?>
            <div class="mypage-menu-group__grid">
                <?php foreach ($menu_group['items'] as $menu_item) {
                    $menu_tone = isset($menu_item['tone']) ? preg_replace('/[^a-z0-9-]/', '', (string) $menu_item['tone']) : 'default';
                    if ($menu_tone === '') {
                        $menu_tone = 'default';
                    }
                    ?>
                <a href="<?php echo $menu_item['href']; ?>" class="mypage-menu-group__item mypage-menu-group__item--<?php echo $menu_tone; ?>"><?php echo get_text($menu_item['label']); ?></a>
                <?php } ?>
            </div>
        </section>
        <?php } ?>
    </nav>
<?php
$mypage_menu_html = ob_get_clean();

g5_page_start('마이페이지');
?>

<main class="mypage-dashboard">
    <section class="mypage-profile-card">
        <p class="mypage-profile-card__type"><?php echo function_exists('eottae_member_profile_type_label') ? get_text(eottae_member_profile_type_label($member)) : ($is_biz ? '사업자회원' : '일반회원'); ?></p>
        <h1 class="mypage-profile-card__name"><?php echo get_text($member['mb_nick']); ?>님</h1>
        <?php if (!empty($growth_profile['level'])) { ?>
        <p class="mypage-profile-card__level"><?php echo eottae_member_growth_render_level_chip($growth_profile['level']); ?>
            <?php if (!empty($growth_profile['main_badge'])) { ?>
            <?php echo eottae_member_growth_render_badge($growth_profile['main_badge'], true); ?>
            <?php } ?>
        </p>
        <?php } ?>
        <p><?php echo get_text($member['mb_email']); ?></p>
    </section>

    <?php echo $mypage_menu_html; ?>

    <section class="mypage-language-card" id="mypage-language-card" aria-labelledby="mypage-language-title">
        <h2 class="mypage-language-card__title" id="mypage-language-title" data-i18n="member.mypage_language_title">언어 설정</h2>
        <form id="mypage-language-form" class="mypage-language-card__form" method="post" action="<?php echo G5_URL; ?>/proc/eottae-member-language.php">
            <?php
            if (function_exists('eottae_render_member_preferred_language_field')) {
                echo eottae_render_member_preferred_language_field($member, 'u');
            }
            ?>
            <p class="mypage-language-card__status" data-mypage-language-status hidden></p>
            <button type="submit" class="mypage-language-card__save" data-i18n="member.save_language">저장</button>
        </form>
    </section>

    <section class="mypage-point-summary">
        <a href="<?php echo G5_URL; ?>/page/eottae-points.php" class="mypage-point-summary__box" style="text-decoration:none;color:inherit">
            <p class="mypage-point-summary__label">포인트</p>
            <p class="mypage-point-summary__value"><?php echo number_format($point); ?>P</p>
        </a>
        <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php" class="mypage-coupon-summary__box" style="text-decoration:none;color:inherit">
            <p class="mypage-point-summary__label">쿠폰</p>
            <p class="mypage-point-summary__value"><?php echo number_format($coupon_count); ?></p>
        </a>
    </section>

    <section class="my-notification-hub<?php echo $notification_total > 0 ? ' my-notification-hub--active' : ''; ?>" aria-labelledby="my-notification-hub-title">
        <div class="my-notification-hub__head">
            <div>
                <p class="my-notification-hub__eyebrow">알림 허브</p>
                <h2 class="my-notification-hub__title" id="my-notification-hub-title">지금 확인할 활동</h2>
            </div>
            <?php if ($notification_total > 0) { ?>
            <span class="my-notification-hub__badge"><?php echo number_format($notification_total); ?></span>
            <?php } ?>
        </div>
        <div class="my-notification-hub__grid">
            <?php foreach ($notification_items as $notification_item) { ?>
            <a href="<?php echo get_text($notification_item['href'] ?? '#'); ?>" class="my-notification-hub__item<?php echo (int) ($notification_item['count'] ?? 0) > 0 ? ' is-alert' : ''; ?>">
                <span class="my-notification-hub__item-label"><?php echo get_text($notification_item['label'] ?? '알림'); ?></span>
                <strong class="my-notification-hub__item-value"><?php echo number_format((int) ($notification_item['count'] ?? 0)); ?></strong>
                <span class="my-notification-hub__item-desc"><?php echo get_text($notification_item['description'] ?? ''); ?></span>
            </a>
            <?php } ?>
        </div>
        <?php if (!empty($my_comment_summary['latest'])) {
            $latest_comment = $my_comment_summary['latest'];
            ?>
        <a href="<?php echo get_text($latest_comment['href']); ?>" class="my-notification-hub__latest">
            <span class="my-notification-hub__latest-board"><?php echo get_text($latest_comment['board']); ?></span>
            <strong class="my-notification-hub__latest-title"><?php echo get_text($latest_comment['title']); ?></strong>
            <span class="my-notification-hub__latest-preview"><?php echo get_text($latest_comment['author']); ?>: <?php echo get_text($latest_comment['preview']); ?></span>
        </a>
        <?php } ?>
        <a href="<?php echo get_text($notification_hub_url); ?>" class="my-notification-hub__more">알림 허브 전체보기</a>
    </section>

    <?php if ($is_biz) { ?>
    <section class="business-dashboard business-dashboard--top">
        <h2 class="business-dashboard__title">사업자 대시보드</h2>
        <p class="business-dashboard__status">
            <?php if ($pending_replies > 0) { ?>
            <strong><?php echo number_format($pending_replies); ?>건</strong>의 리뷰에 답변이 필요합니다.
            <?php } else { ?>
            새로운 리뷰 답변 요청이 없습니다.
            <?php } ?>
        </p>
        <div class="business-dashboard__actions" aria-label="사업자 빠른 메뉴">
            <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>" class="business-dashboard__btn">업체 등록</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-business-snippets.php" class="business-dashboard__btn business-dashboard__btn--secondary">홍보 문구 관리</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-business-coupons.php" class="business-dashboard__btn business-dashboard__btn--coupon">쿠폰 발행 관리</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-ad-register.php" class="business-dashboard__btn business-dashboard__btn--secondary">광고 등록</a>
            <a href="<?php echo G5_URL; ?>/page/eottae-business-coupon-guide.php" class="business-dashboard__btn business-dashboard__btn--guide">쿠폰 발행 안내</a>
            <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=<?php echo EOTTae_COMMUNITY_TABLE; ?>" class="business-dashboard__btn business-dashboard__btn--secondary">커뮤니티 글쓰기</a>
        </div>
        <?php eottae_render_inquiry_buttons('business', array()); ?>

        <?php if (!empty($my_shop_posts)) { ?>
        <div class="business-dashboard__shops">
            <h3 class="business-dashboard__shops-title">내 업체</h3>
            <ul class="business-dashboard__shop-list">
                <?php foreach ($my_shop_posts as $shop_row) { ?>
                <li class="business-dashboard__shop-item">
                    <a href="<?php echo $shop_row['view_url']; ?>" class="business-dashboard__shop-name"><?php echo $shop_row['subject']; ?></a>
                    <a href="<?php echo $shop_row['update_url']; ?>" class="business-dashboard__shop-edit">수정</a>
                    <?php if (!empty($shop_row['delete_url'])) { ?>
                    <a href="<?php echo $shop_row['delete_url']; ?>" class="business-dashboard__shop-delete" onclick="del(this.href); return false;">삭제</a>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </section>
    <?php } ?>

    <?php render_my_sebu_briefing(collect_my_sebu_briefing_data($member['mb_id'])); ?>

    <section class="my-message-card<?php echo $message_unread_count > 0 ? ' my-message-card--active' : ''; ?>" aria-labelledby="my-message-card-title">
        <div class="my-message-card__head">
            <div>
                <p class="my-message-card__eyebrow">쪽지함</p>
                <h2 class="my-message-card__title" id="my-message-card-title">새 메시지 확인</h2>
            </div>
            <?php if ($message_unread_count > 0) { ?>
            <span class="my-message-card__badge"><?php echo number_format($message_unread_count); ?></span>
            <?php } ?>
        </div>
        <p class="my-message-card__desc"><?php echo get_text($message_summary['summary_line'] ?? '쪽지를 확인해 보세요.'); ?></p>
        <?php if (!empty($message_summary['latest'])) {
            $latest_message = $message_summary['latest'];
            ?>
        <a href="<?php echo get_text($latest_message['href'] ?? eottae_message_url()); ?>" class="my-message-card__latest">
            <span class="my-message-card__latest-name"><?php echo get_text($latest_message['other_label'] ?? '회원'); ?></span>
            <?php if (!empty($latest_message['context_label'])) { ?>
            <span class="my-message-card__latest-context"><?php echo get_text($latest_message['context_label']); ?></span>
            <?php } ?>
            <span class="my-message-card__latest-preview"><?php echo $latest_message['last_body_preview'] ?? ''; ?></span>
        </a>
        <?php } ?>
        <a href="<?php echo eottae_message_url(); ?>" class="my-message-card__link">쪽지함 열기</a>
    </section>

    <section class="my-talk-hub-card<?php echo !empty($my_talk_hub['has_activity']) ? ' my-talk-hub-card--active' : ''; ?>" aria-labelledby="my-talk-hub-title">
        <div class="my-talk-hub-card__head">
            <h2 class="my-talk-hub-card__title" id="my-talk-hub-title">내 세부톡</h2>
            <?php if ($hub_activity_total > 0) { ?>
            <span class="my-talk-hub-card__badge"><?php echo number_format($hub_activity_total); ?></span>
            <?php } ?>
        </div>
        <p class="my-talk-hub-card__desc"><?php echo get_text($my_talk_hub['summary_line'] ?? '가입한 세부톡방의 새 글, 댓글, 공지, 모임을 한 번에 확인하세요.'); ?></p>
        <?php if ($hub_room_count > 0) { ?>
        <ul class="my-talk-hub-card__stats">
            <li class="my-talk-hub-card__stat">
                <span class="my-talk-hub-card__stat-label">참여 톡방</span>
                <span class="my-talk-hub-card__stat-value"><?php echo number_format($hub_room_count); ?></span>
            </li>
            <li class="my-talk-hub-card__stat">
                <span class="my-talk-hub-card__stat-label">새 글</span>
                <span class="my-talk-hub-card__stat-value<?php echo $hub_new_posts > 0 ? ' is-alert' : ''; ?>"><?php echo number_format($hub_new_posts); ?></span>
            </li>
            <li class="my-talk-hub-card__stat">
                <span class="my-talk-hub-card__stat-label">새 댓글</span>
                <span class="my-talk-hub-card__stat-value<?php echo $hub_new_comments > 0 ? ' is-alert' : ''; ?>"><?php echo number_format($hub_new_comments); ?></span>
            </li>
            <li class="my-talk-hub-card__stat">
                <span class="my-talk-hub-card__stat-label">알림</span>
                <span class="my-talk-hub-card__stat-value<?php echo $hub_notifications > 0 ? ' is-alert' : ''; ?>"><?php echo number_format($hub_notifications); ?></span>
            </li>
        </ul>
        <?php } ?>
        <a href="<?php echo $mypage_talk_url; ?>" class="my-talk-hub-card__link">내 세부톡 대시보드 열기</a>
    </section>

    <?php if ($is_admin === 'super') {
        if (function_exists('eottae_column_render_mypage_super_admin_section')) {
            eottae_column_render_mypage_super_admin_section(5);
        }
        if (function_exists('eottae_public_ai_render_mypage_admin_section')) {
            eottae_public_ai_render_mypage_admin_section();
        }
        eottae_talkroom_render_mypage_super_admin_talk_tools(8);
    } ?>

    <?php
    if (is_file(G5_PATH.'/components/eottae/column-mypage.php')) {
        include_once G5_PATH.'/components/eottae/column-mypage.php';
        include_once G5_LIB_PATH.'/eottae-column.lib.php';
        add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-column.css">', 24);
        echo eottae_column_mypage_section_html($member);
    }
    ?>

</main>

<script>
(function () {
  'use strict';

  var form = document.getElementById('mypage-language-form');
  if (!form) {
    return;
  }

  function t(key, fallback) {
    if (window.EottaeI18N && typeof window.EottaeI18N.t === 'function') {
      var value = window.EottaeI18N.t(key);
      if (value) {
        return value;
      }
    }
    return fallback;
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    var cfg = window.__EOTTaeI18N__ || {};
    var url = cfg.memberLanguageSaveUrl || form.getAttribute('action') || '';
    var select = form.querySelector('#reg_preferred_language');
    var status = form.querySelector('[data-mypage-language-status]');
    var submitBtn = form.querySelector('[type="submit"]');
    var language = select ? String(select.value || '').trim() : '';

    if (!url || !language) {
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
    }
    if (status) {
      status.hidden = true;
      status.classList.remove('is-error');
    }

    var body = new FormData();
    body.append('preferred_language', language);

    window.fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      body: body,
      headers: { Accept: 'application/json' },
    })
      .then(function (res) {
        return res.json().catch(function () {
          return { success: false };
        });
      })
      .then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.message) || 'save_failed');
        }
        if (window.EottaeI18N && typeof window.EottaeI18N.setLanguage === 'function') {
          window.EottaeI18N.setLanguage(language);
        }
        if (status) {
          status.textContent = t('member.language_saved', '언어 설정을 저장했습니다.');
          status.hidden = false;
        }
      })
      .catch(function () {
        if (status) {
          status.textContent = t('member.language_save_failed', '언어 설정 저장에 실패했습니다.');
          status.classList.add('is-error');
          status.hidden = false;
        }
      })
      .then(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
        }
      });
  });
})();
</script>

<?php
g5_page_end();
