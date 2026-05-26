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
include_once G5_PATH.'/components/eottae/member-growth-display.php';
include_once G5_PATH.'/components/eottae/talk-admin-nav.php';
include_once G5_PATH.'/components/eottae/public-ai-admin-nav.php';
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

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/eottae-my-talk.css">', 22);

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

    <?php render_my_sebu_briefing(collect_my_sebu_briefing_data($member['mb_id'])); ?>

    <?php
    $hub_room_count = (int) ($my_talk_hub['room_count'] ?? 0);
    $hub_new_posts = (int) ($my_talk_hub['new_posts'] ?? 0);
    $hub_new_comments = (int) ($my_talk_hub['new_comments'] ?? 0);
    $hub_notifications = (int) ($my_talk_hub['notifications'] ?? 0);
    $hub_owner_tasks = (int) ($my_talk_hub['owner_tasks'] ?? 0);
    $hub_activity_total = $hub_new_posts + $hub_new_comments + $hub_notifications + $hub_owner_tasks;
    ?>
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

    <nav class="mypage-quick-menu" aria-label="마이페이지 메뉴">
        <a href="<?php echo G5_URL; ?>/page/eottae-points.php" class="mypage-quick-menu__item">포인트</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-coupons.php" class="mypage-quick-menu__item">쿠폰함</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-my-reviews.php" class="mypage-quick-menu__item">내 리뷰<?php if (!$is_biz && $my_review_count > 0) { ?> (<?php echo $my_review_count; ?>)<?php } ?></a>
        <a href="<?php echo G5_URL; ?>/page/eottae-saved-shops.php" class="mypage-quick-menu__item">찜·최근<?php if ($saved_count > 0) { ?> (<?php echo $saved_count; ?>)<?php } ?></a>
        <a href="<?php echo G5_URL; ?>/page/eottae-inquiries.php" class="mypage-quick-menu__item">문의<?php if ($inquiry_count > 0) { ?> (<?php echo $inquiry_count; ?>)<?php } ?></a>
        <a href="<?php echo G5_URL; ?>/page/eottae-events.php" class="mypage-quick-menu__item">이벤트</a>
        <a href="<?php echo $mypage_talk_url; ?>" class="mypage-quick-menu__item">내 세부톡<?php if ($hub_activity_total > 0) { ?> (<?php echo number_format($hub_activity_total); ?>)<?php } ?></a>
        <a href="<?php echo $badges_url; ?>" class="mypage-quick-menu__item">내 등급/뱃지<?php if (!empty($growth_profile['total_score'])) { ?> (<?php echo number_format((int) $growth_profile['total_score']); ?>점)<?php } ?></a>
        <a href="<?php echo function_exists('eottae_member_growth_badge_book_url') ? eottae_member_growth_badge_book_url() : G5_URL.'/badges/'; ?>" class="mypage-quick-menu__item">뱃지 도감</a>
        <a href="<?php echo function_exists('eottae_member_growth_ranking_url') ? eottae_member_growth_ranking_url('week') : G5_URL.'/ranking/'; ?>" class="mypage-quick-menu__item">활동 랭킹</a>
        <a href="<?php echo function_exists('eottae_challenge_mypage_url') ? eottae_challenge_mypage_url() : G5_URL.'/mypage/challenges.php'; ?>" class="mypage-quick-menu__item">챌린지<?php
            $challenge_summary = function_exists('eottae_challenge_my_summary') ? eottae_challenge_my_summary($member['mb_id']) : array();
            if ((int) ($challenge_summary['entry_count'] ?? 0) > 0) {
                echo ' ('.number_format((int) $challenge_summary['entry_count']).')';
            }
        ?></a>
        <?php if ($is_admin === 'super') {
            $talk_kicked_count = function_exists('eottae_talkroom_admin_kicked_count') ? eottae_talkroom_admin_kicked_count() : 0;
            $talk_report_pending = function_exists('eottae_talkroom_admin_pending_report_count') ? eottae_talkroom_admin_pending_report_count() : 0;
            ?>
        <a href="<?php echo function_exists('eottae_talkroom_admin_rooms_url') ? eottae_talkroom_admin_rooms_url() : G5_URL.'/page/eottae-admin-talk-rooms.php'; ?>" class="mypage-quick-menu__item">톡방 목록</a>
        <a href="<?php echo function_exists('eottae_talkroom_admin_kicked_url') ? eottae_talkroom_admin_kicked_url() : G5_URL.'/page/eottae-admin-talk-kicked.php'; ?>" class="mypage-quick-menu__item">강퇴 회원<?php if ($talk_kicked_count > 0) { ?> (<?php echo number_format($talk_kicked_count); ?>)<?php } ?></a>
        <a href="<?php echo function_exists('eottae_talkroom_admin_reports_url') ? eottae_talkroom_admin_reports_url('pending') : G5_URL.'/page/eottae-admin-talk-reports.php?status=pending'; ?>" class="mypage-quick-menu__item">신고 관리<?php if ($talk_report_pending > 0) { ?> (<?php echo number_format($talk_report_pending); ?>)<?php } ?></a>
            <?php $public_ai_pending = function_exists('eottae_public_ai_pending_count') ? eottae_public_ai_pending_count() : 0; ?>
        <a href="<?php echo function_exists('eottae_public_ai_mypage_admin_url') ? eottae_public_ai_mypage_admin_url() : G5_URL.'/page/eottae-admin-public-ai.php'; ?>" class="mypage-quick-menu__item">공개단톡 AI<?php if ($public_ai_pending > 0) { ?> (<?php echo number_format($public_ai_pending); ?>)<?php } ?></a>
        <?php } ?>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-snippets.php" class="mypage-quick-menu__item">홍보 문구</a>
        <?php if ($is_biz) { ?><a href="<?php echo G5_URL; ?>/page/eottae-business-coupons.php" class="mypage-quick-menu__item">쿠폰 발행</a><?php } ?>
        <a href="<?php echo G5_URL; ?>/page/eottae-coupon-guide.php" class="mypage-quick-menu__item">쿠폰 안내</a>
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo EOTTae_COMMUNITY_TABLE; ?>" class="mypage-quick-menu__item">내 활동</a>
        <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/register_form.php'); ?>" class="mypage-quick-menu__item">정보수정</a>
        <a href="<?php echo G5_BBS_URL; ?>/logout.php" class="mypage-quick-menu__item">로그아웃</a>
    </nav>

    <?php if ($is_admin === 'super') {
        if (function_exists('eottae_public_ai_render_mypage_admin_section')) {
            eottae_public_ai_render_mypage_admin_section();
        }
        eottae_talkroom_render_mypage_super_admin_talk_tools(8);
    } ?>

    <?php if ($is_biz) { ?>
    <section class="business-dashboard">
        <h2 class="business-dashboard__title">사업자 대시보드</h2>
        <?php if ($pending_replies > 0) { ?>
        <p><strong><?php echo number_format($pending_replies); ?>건</strong>의 리뷰에 답변이 필요합니다.</p>
        <?php } else { ?>
        <p>새로운 리뷰 답변 요청이 없습니다.</p>
        <?php } ?>
        <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=<?php echo EOTTae_SHOP_TABLE; ?>" class="eottae-btn-write" style="margin-top:12px;display:inline-flex">업체 등록</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-snippets.php" class="eottae-btn-write" style="margin-top:8px;display:inline-flex">홍보 문구 관리</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-coupons.php" class="eottae-btn-write" style="margin-top:8px;display:inline-flex">쿠폰 발행 관리</a>
        <a href="<?php echo G5_URL; ?>/page/eottae-business-coupon-guide.php" class="eottae-btn-write" style="margin-top:8px;display:inline-flex">쿠폰 발행 안내</a>
        <a href="<?php echo G5_BBS_URL; ?>/write.php?bo_table=<?php echo EOTTae_COMMUNITY_TABLE; ?>" class="eottae-btn-write" style="margin-top:8px;display:inline-flex">커뮤니티 글쓰기</a>
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
</main>

<?php
g5_page_end();
