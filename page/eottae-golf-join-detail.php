<?php
include_once(dirname(__FILE__).'/_init.php');
include_once G5_LIB_PATH.'/eottae-golf-join.lib.php';
include_once G5_PATH.'/components/eottae/golf-join-card.php';
include_once G5_PATH.'/components/eottae/golf-join-applicant.php';

$join_id = isset($_GET['join_id']) ? (int) $_GET['join_id'] : 0;
$viewer_mb_id = !empty($is_member) && !empty($member['mb_id']) ? (string) $member['mb_id'] : '';
$post = eottae_golf_join_get_post($join_id, $viewer_mb_id);

if (!$post) {
    alert('조인방을 찾을 수 없습니다.', eottae_golf_join_list_url());
}

$viewer = $post['viewer'] ?? array();
$approved = eottae_golf_join_approved_members($post);
$pending_applicants = array();
if (!empty($viewer['is_host']) && !eottae_golf_join_use_mock_data()) {
    $pending_applicants = eottae_golf_join_list_pending_members($join_id);
}

$member_token = $is_member ? eottae_golf_join_member_token() : '';
$owner_token = !empty($viewer['is_host']) ? eottae_golf_join_owner_token() : '';

$list_url = eottae_golf_join_list_url();
$chat_url = eottae_golf_join_chat_url($join_id);
$login_url = function_exists('eottae_login_url')
    ? eottae_login_url(eottae_golf_join_detail_url($join_id))
    : G5_BBS_URL.'/login.php';
$share_url = eottae_golf_join_detail_url($join_id);
$share_title = get_text($post['golf_course_name'] ?? '골프조인');

$gender_labels = eottae_golf_join_gender_preference_detail_labels();
$age_options = eottae_golf_join_age_preference_options();
$score_options = eottae_golf_join_score_preference_options();
$gender_code = preg_replace('/[^a-z]/', '', (string) ($post['gender_preference'] ?? 'any'));
$age_codes = eottae_golf_join_csv_to_list($post['age_preferences'] ?? '');
$score_codes = eottae_golf_join_csv_to_list($post['score_preferences'] ?? '');
if (!$age_codes) {
    $age_codes = array('any');
}
if (!$score_codes) {
    $score_codes = array('any');
}

g5_page_start('조인');
?>

<main class="golf-join-page golf-join-page--detail" id="golf-join-detail"
      data-join-id="<?php echo (int) $join_id; ?>"
      data-login-url="<?php echo get_text($login_url); ?>">

    <header class="golf-join-topbar golf-join-topbar--detail">
        <a href="<?php echo $list_url; ?>" class="golf-join-topbar__back" aria-label="뒤로가기">
            <span aria-hidden="true">←</span>
        </a>
        <h1 class="golf-join-topbar__title">조인</h1>
        <button type="button" class="golf-join-topbar__share" id="golf-join-share" data-share-url="<?php echo get_text($share_url); ?>" data-share-title="<?php echo $share_title; ?>" aria-label="공유">
            <span aria-hidden="true">⎘</span>
        </button>
    </header>

    <?php if (!empty($_GET['created'])) { ?>
    <div class="golf-join-banner golf-join-banner--success" role="status">
        골프조인이 등록되었습니다. 함께할 멤버를 기다려 보세요!
    </div>
    <?php } elseif (!empty($_GET['applied'])) { ?>
    <div class="golf-join-banner golf-join-banner--success" role="status">
        조인 신청이 완료되었습니다. 방장의 승인을 기다려 주세요.
    </div>
    <?php } else { ?>
    <div class="golf-join-banner golf-join-banner--<?php echo get_text($post['banner_tone'] ?? 'info'); ?>" role="status">
        <?php echo get_text($post['banner_message'] ?? ''); ?>
    </div>
    <?php } ?>

    <section class="golf-join-detail-hero">
        <p class="golf-join-detail-hero__status golf-join-detail-hero__status--<?php echo get_text($post['status_class'] ?? ''); ?>">
            <?php echo get_text($post['status_label'] ?? ''); ?>
        </p>
        <?php if (!empty($post['venue_type']) && ($post['venue_type'] ?? '') === 'screen_golf') { ?>
        <p class="golf-join-detail-hero__venue">스크린골프</p>
        <?php } ?>
        <h2 class="golf-join-detail-hero__course">
            <?php if (!empty($post['shop_detail_url'])) { ?>
            <a href="<?php echo get_text($post['shop_detail_url']); ?>" class="golf-join-detail-hero__course-link"><?php echo get_text($post['golf_course_name'] ?? ''); ?></a>
            <?php } else { ?>
            <?php echo get_text($post['golf_course_name'] ?? ''); ?>
            <?php } ?>
        </h2>
        <?php if (!empty($post['title'])) { ?>
        <p class="golf-join-detail-hero__title"><?php echo get_text($post['title']); ?></p>
        <?php } ?>

        <dl class="golf-join-detail-facts">
            <div class="golf-join-detail-facts__row">
                <dt>지역</dt>
                <dd><?php echo get_text($post['region_label'] ?? ''); ?></dd>
            </div>
            <div class="golf-join-detail-facts__row">
                <dt>라운드</dt>
                <dd><?php echo get_text($post['round_date_label'] ?? ''); ?></dd>
            </div>
            <div class="golf-join-detail-facts__row">
                <dt>티타임</dt>
                <dd>
                    <?php echo get_text($post['tee_time_label'] ?? ''); ?>
                    <?php if (!empty($post['time_zone_label'])) { ?>
                    <span class="golf-join-detail-facts__sub">(<?php echo get_text($post['time_zone_label']); ?>)</span>
                    <?php } ?>
                </dd>
            </div>
            <div class="golf-join-detail-facts__row">
                <dt>인원</dt>
                <dd>
                    <span class="golf-join-detail-facts__count">
                        <strong><?php echo (int) ($post['current_count'] ?? 0); ?></strong>
                        / <?php echo (int) ($post['recruit_count'] ?? 0); ?>명
                    </span>
                </dd>
            </div>
            <div class="golf-join-detail-facts__row">
                <dt>가격</dt>
                <dd><?php echo get_text($post['price_label'] ?? ''); ?></dd>
            </div>
        </dl>
    </section>

    <section class="golf-join-detail-section" aria-labelledby="golf-join-pref-title">
        <h3 class="golf-join-detail-section__title" id="golf-join-pref-title">원하는 멤버 조건</h3>
        <div class="golf-join-pref-group">
            <p class="golf-join-pref-group__label">성별</p>
            <ul class="golf-join-pref-list">
                <?php foreach ($gender_labels as $code => $label) { ?>
                <li class="<?php echo $gender_code === $code ? 'is-on' : ''; ?>"><?php echo get_text($label); ?></li>
                <?php } ?>
            </ul>
        </div>
        <div class="golf-join-pref-group">
            <p class="golf-join-pref-group__label">연령</p>
            <ul class="golf-join-pref-list">
                <?php foreach ($age_options as $code => $label) { ?>
                <li class="<?php echo in_array($code, $age_codes, true) ? 'is-on' : ''; ?>"><?php echo get_text($label); ?></li>
                <?php } ?>
            </ul>
        </div>
        <div class="golf-join-pref-group">
            <p class="golf-join-pref-group__label">타수</p>
            <ul class="golf-join-pref-list">
                <?php foreach ($score_options as $code => $label) { ?>
                <li class="<?php echo in_array($code, $score_codes, true) ? 'is-on' : ''; ?>"><?php echo get_text($label); ?></li>
                <?php } ?>
            </ul>
        </div>
    </section>

    <section class="golf-join-detail-section" aria-labelledby="golf-join-intro-title">
        <h3 class="golf-join-detail-section__title" id="golf-join-intro-title">우리 방 소개</h3>
        <?php if (!empty($post['description'])) { ?>
        <div class="golf-join-detail-intro"><?php echo nl2br(get_text($post['description'])); ?></div>
        <?php } else { ?>
        <p class="golf-join-detail-intro golf-join-detail-intro--empty">소개글이 없습니다.</p>
        <?php } ?>
        <?php if (!empty($post['mood_tags'])) { ?>
        <ul class="golf-join-mood-tags">
            <?php foreach ((array) $post['mood_tags'] as $tag) { ?>
            <li>#<?php echo get_text($tag); ?></li>
            <?php } ?>
        </ul>
        <?php } ?>
    </section>

    <?php if (!empty($viewer['is_host'])) { ?>
    <section class="golf-join-detail-section golf-join-detail-section--host" id="golf-join-applicants" aria-labelledby="golf-join-applicants-title">
        <div class="golf-join-detail-section__head">
            <h3 class="golf-join-detail-section__title" id="golf-join-applicants-title">신청자 목록</h3>
            <span class="golf-join-detail-section__meta"><?php echo count($pending_applicants); ?>명 대기</span>
        </div>
        <?php if (eottae_golf_join_use_mock_data()) { ?>
        <p class="golf-join-detail-intro golf-join-detail-intro--empty">샘플 데이터 모드에서는 신청자 관리를 사용할 수 없습니다. DB에 등록된 조인에서 이용해 주세요.</p>
        <?php } elseif (empty($pending_applicants)) { ?>
        <p class="golf-join-detail-intro golf-join-detail-intro--empty">아직 신청자가 없습니다.</p>
        <?php } else { ?>
        <ul class="golf-join-applicant-list">
            <?php foreach ($pending_applicants as $applicant) {
                echo eottae_golf_join_applicant_html($applicant);
            } ?>
        </ul>
        <?php } ?>
    </section>
    <?php } ?>

    <section class="golf-join-detail-section" aria-labelledby="golf-join-members-title">
        <div class="golf-join-detail-section__head">
            <h3 class="golf-join-detail-section__title" id="golf-join-members-title">함께할 멤버</h3>
            <span class="golf-join-detail-section__meta">
                <?php echo (int) ($post['current_count'] ?? 0); ?> / <?php echo (int) ($post['recruit_count'] ?? 0); ?>명
            </span>
        </div>
        <ul class="golf-join-member-list">
            <?php foreach ($approved as $m) {
                $is_host_chip = ($m['role'] ?? '') === 'host' || ($m['user_id'] ?? '') === ($post['user_id'] ?? '');
                echo eottae_golf_join_member_chip_html($m, $is_host_chip);
            } ?>
        </ul>
        <?php if (empty($approved)) { ?>
        <p class="golf-join-detail-intro golf-join-detail-intro--empty">아직 확정된 멤버가 없습니다.</p>
        <?php } ?>
    </section>

    <div class="golf-join-detail-spacer" aria-hidden="true"></div>

    <footer class="golf-join-detail-bar" id="golf-join-detail-bar">
        <?php if (!empty($viewer['is_host'])) { ?>
            <?php if (!empty($viewer['show_close'])) { ?>
            <button type="button" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--ghost" id="golf-join-close-btn">모집 마감</button>
            <?php } ?>
            <?php if (count($pending_applicants) > 0) { ?>
            <a href="#golf-join-applicants" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--ghost">신청 <?php echo count($pending_applicants); ?>건</a>
            <?php } ?>
            <?php if (!empty($viewer['show_chat'])) { ?>
            <a href="<?php echo $chat_url; ?>" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--primary">채팅하기</a>
            <?php } else { ?>
            <span class="golf-join-detail-bar__btn golf-join-detail-bar__btn--done">방장 관리 중</span>
            <?php } ?>
        <?php } elseif (!empty($viewer['show_chat'])) { ?>
            <a href="<?php echo $chat_url; ?>" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--primary">채팅하기</a>
        <?php } elseif (!empty($viewer['show_cancel_apply'])) { ?>
            <button type="button" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--ghost" id="golf-join-cancel-btn">신청 취소</button>
            <button type="button" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--done" disabled>신청 완료</button>
        <?php } elseif (!empty($viewer['can_apply'])) { ?>
            <button type="button" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--primary" id="golf-join-apply-btn">조인 신청하기</button>
        <?php } elseif (empty($viewer['is_logged_in'])) { ?>
            <button type="button" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--primary" id="golf-join-apply-guest-btn">조인 신청하기</button>
        <?php } else { ?>
            <button type="button" class="golf-join-detail-bar__btn" disabled>
                <?php echo ($post['status'] ?? '') === 'recruiting' ? '신청할 수 없습니다' : '모집이 마감되었습니다'; ?>
            </button>
        <?php } ?>
    </footer>
</main>

<div class="golf-join-sheet" id="golf-join-login-sheet" role="dialog" aria-modal="true" aria-labelledby="golf-join-login-sheet-title" hidden>
    <div class="golf-join-sheet__backdrop" data-sheet-close></div>
    <div class="golf-join-sheet__panel golf-join-sheet__panel--compact">
        <h2 class="golf-join-sheet__title" id="golf-join-login-sheet-title">로그인이 필요합니다</h2>
        <p class="golf-join-sheet__desc">조인 신청은 로그인 후 이용할 수 있습니다.</p>
        <a href="<?php echo get_text($login_url); ?>" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--primary golf-join-sheet__submit">로그인하기</a>
        <button type="button" class="golf-join-sheet__cancel" data-sheet-close>닫기</button>
    </div>
</div>

<div class="golf-join-sheet" id="golf-join-apply-sheet" role="dialog" aria-modal="true" aria-labelledby="golf-join-apply-sheet-title" hidden>
    <div class="golf-join-sheet__backdrop" data-sheet-close></div>
    <div class="golf-join-sheet__panel">
        <h2 class="golf-join-sheet__title" id="golf-join-apply-sheet-title">조인 신청</h2>
        <p class="golf-join-sheet__desc">방장에게 전달할 메시지를 입력해 주세요. (선택)</p>
        <textarea class="golf-join-create-textarea" id="golf-join-apply-message" rows="4" maxlength="500" placeholder="예: 80타대 남성입니다. 즐겁게 라운드하고 싶어요!"></textarea>
        <button type="button" class="golf-join-detail-bar__btn golf-join-detail-bar__btn--primary golf-join-sheet__submit" id="golf-join-apply-confirm">신청하기</button>
        <button type="button" class="golf-join-sheet__cancel" data-sheet-close>취소</button>
    </div>
</div>

<script>
window.EOTTaeGolfJoinDetail = {
    joinId: <?php echo (int) $join_id; ?>,
    memberToken: <?php echo json_encode($member_token, JSON_UNESCAPED_UNICODE); ?>,
    ownerToken: <?php echo json_encode($owner_token, JSON_UNESCAPED_UNICODE); ?>,
    memberProcUrl: <?php echo json_encode(G5_URL.'/proc/eottae-golf-join-member.php', JSON_UNESCAPED_UNICODE); ?>,
    ownerProcUrl: <?php echo json_encode(G5_URL.'/proc/eottae-golf-join-owner.php', JSON_UNESCAPED_UNICODE); ?>,
    isHost: <?php echo !empty($viewer['is_host']) ? 'true' : 'false'; ?>
};
</script>

<?php
add_javascript('<script src="'.G5_JS_URL.'/eottae-golf-join-detail.js" defer></script>', 25);
g5_page_end();
