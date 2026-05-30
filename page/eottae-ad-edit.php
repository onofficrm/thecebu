<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-ad-register.php'));
}

include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';
eottae_ad_platform_ensure_schema();

if (!eottae_ad_platform_can_manage($member)) {
    alert('사업자회원 또는 최고관리자만 이용할 수 있습니다.', G5_URL.'/page/eottae-mypage.php');
}

$ad_id = isset($_GET['ad_id']) ? (int) $_GET['ad_id'] : 0;
$campaign = eottae_ad_platform_get_campaign($ad_id);
$is_super = ($is_admin === 'super');

if (!$campaign || !eottae_ad_platform_member_owns_campaign($campaign, $member['mb_id'], $is_super)) {
    alert('광고를 찾을 수 없습니다.', G5_URL.'/page/eottae-ad-register.php');
}

$can_edit = eottae_ad_platform_member_can_edit_campaign($campaign);
$can_extend = eottae_ad_platform_member_can_extend_campaign($campaign);
$can_cancel = eottae_ad_platform_member_can_cancel_campaign($campaign);

if (!$can_edit && !$can_extend) {
    alert('현재 상태에서는 수정·연장할 수 없습니다.', G5_URL.'/page/eottae-ad-register.php');
}

$slot = eottae_ad_platform_get_slot_by_id((int) $campaign['slot_id']);
$target_regions = eottae_ad_platform_target_region_options();
$target_categories = eottae_ad_platform_target_category_options($campaign['slot_code']);
$my_shops = function_exists('eottae_business_shop_posts') ? eottae_business_shop_posts($member['mb_id'], 20) : array();
$point = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
$proc_url = G5_URL.'/proc/eottae-ad-platform.php';
$ai_url = G5_URL.'/proc/eottae-ad-platform-ai.php';
$back_url = G5_URL.'/page/eottae-ad-register.php';
$js_path = G5_PATH.'/js/eottae-ad-platform.js';
$js_url = G5_JS_URL.'/eottae-ad-platform.js'.(is_file($js_path) ? '?ver='.(int) filemtime($js_path) : '');

g5_page_start($can_edit ? '광고 수정' : '광고 연장');
?>

<main class="mypage-subpage ad-platform-edit ad-platform-register" data-ai-url="<?php echo htmlspecialchars($ai_url, ENT_QUOTES, 'UTF-8'); ?>">
    <?php eottae_render_mypage_back(); ?>
    <header class="ad-platform-register__header">
        <h1 class="mypage-subpage__title"><?php echo $can_edit ? '광고 수정' : '광고 연장'; ?></h1>
        <p class="ad-platform-register__lead">
            <?php echo get_text($campaign['slot_name']); ?> · <?php echo get_text($campaign['status_label']); ?>
            · <?php echo get_text($campaign['start_date']); ?> ~ <?php echo get_text($campaign['end_date']); ?>
        </p>
        <p class="ad-platform-register__point">보유 포인트 <strong><?php echo number_format($point); ?>P</strong> · <a href="<?php echo eottae_ad_platform_report_url((int) $campaign['ad_id']); ?>">성과 리포트</a></p>
    </header>

    <?php if ($can_edit) { ?>
    <section class="promo-admin-panel ad-platform-register__form-panel">
        <form id="adPlatformEditForm" class="promo-admin-form ad-platform-register__form" method="post" action="<?php echo $proc_url; ?>" enctype="multipart/form-data" data-back-url="<?php echo htmlspecialchars($back_url, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="ad_id" value="<?php echo (int) $campaign['ad_id']; ?>">
            <input type="hidden" name="slot_code" value="<?php echo get_text($campaign['slot_code']); ?>" data-ad-platform-slot data-point-per-day="<?php echo $slot ? (int) $slot['point_per_day'] : 0; ?>" data-min-days="<?php echo $slot ? (int) $slot['min_days'] : 3; ?>" data-max-days="<?php echo $slot ? (int) $slot['max_days'] : 90; ?>">

            <div class="ad-platform-ai-panel promo-admin-panel">
                <h2 class="promo-admin-panel__title">AI 광고 만들기</h2>
                <div class="promo-admin-form__row">
                    <div class="promo-admin-form__field">
                        <label for="ad_ai_topic">홍보 주제</label>
                        <input type="text" id="ad_ai_topic" data-ad-ai-topic maxlength="120" placeholder="예: 주말 20% 할인, 신메뉴 출시">
                    </div>
                    <div class="promo-admin-form__field">
                        <label for="ad_ai_tone">톤</label>
                        <select id="ad_ai_tone" data-ad-ai-tone>
                            <option value="friendly">친근한</option>
                            <option value="premium">고급스러운</option>
                            <option value="urgent">이벤트·긴급</option>
                            <option value="trust">신뢰감</option>
                        </select>
                    </div>
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_ai_offer">할인·혜택 (선택)</label>
                    <input type="text" id="ad_ai_offer" data-ad-ai-offer maxlength="120" placeholder="예: 첫 방문 10% 할인">
                </div>
                <div class="ad-platform-ai-panel__actions">
                    <button type="button" class="promo-reward-btn" data-ad-ai-copy>AI 문안 생성</button>
                    <button type="button" class="promo-reward-btn promo-reward-btn--primary" data-ad-ai-image>AI 이미지 생성</button>
                </div>
                <p class="promo-admin-form__status" data-ad-ai-status role="status"></p>
                <div class="ad-platform-ai-panel__preview" data-ad-ai-preview<?php echo $campaign['image_url'] !== '' ? '' : ' hidden'; ?>>
                    <?php if ($campaign['image_url'] !== '') { ?>
                    <img src="<?php echo htmlspecialchars($campaign['image_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="광고 이미지 미리보기">
                    <?php } ?>
                </div>
            </div>

            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="ad_start_date">시작일</label>
                    <input type="date" id="ad_start_date" name="start_date" value="<?php echo get_text($campaign['start_date']); ?>" required>
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_days">집행 일수</label>
                    <input type="number" id="ad_days" name="days" value="<?php echo (int) $campaign['days']; ?>" required data-ad-platform-days>
                </div>
                <div class="promo-admin-form__field">
                    <label>예상 포인트</label>
                    <p class="ad-platform-register__quote" data-ad-platform-quote><?php echo number_format((int) $campaign['total_points']); ?>P</p>
                </div>
            </div>

            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="ad_target_category">타깃 카테고리</label>
                    <select id="ad_target_category" name="target_category">
                        <option value="">전체 (타깃 없음)</option>
                        <?php foreach ($target_categories as $cat) { ?>
                        <option value="<?php echo get_text($cat); ?>"<?php echo $campaign['target_category'] === $cat ? ' selected' : ''; ?>><?php echo get_text($cat); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_target_region">타깃 지역</label>
                    <select id="ad_target_region" name="target_region">
                        <option value="">전체 지역</option>
                        <?php foreach ($target_regions as $region) { ?>
                        <option value="<?php echo get_text($region); ?>"<?php echo $campaign['target_region'] === $region ? ' selected' : ''; ?>><?php echo get_text($region); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_bid_bonus">입찰 보너스 (P)</label>
                    <input type="number" id="ad_bid_bonus" name="bid_bonus" min="0" max="100000" step="100" value="<?php echo (int) $campaign['bid_bonus']; ?>" data-ad-bid-bonus>
                </div>
            </div>

            <div class="promo-admin-form__field">
                <label for="ad_title">광고 제목</label>
                <input type="text" id="ad_title" name="title" maxlength="120" required value="<?php echo htmlspecialchars($campaign['title'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="promo-admin-form__field">
                <label for="ad_description">광고 설명</label>
                <textarea id="ad_description" name="description" rows="4" maxlength="1000" required><?php echo htmlspecialchars($campaign['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="ad_button_text">버튼 문구</label>
                    <input type="text" id="ad_button_text" name="button_text" maxlength="40" value="<?php echo htmlspecialchars($campaign['button_text'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_link_url">연결 URL</label>
                    <input type="url" id="ad_link_url" name="link_url" maxlength="500" value="<?php echo htmlspecialchars($campaign['link_url'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <div class="promo-admin-form__field">
                <label for="ad_image_file">광고 이미지</label>
                <input type="file" id="ad_image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
                <input type="hidden" name="image_url" id="ad_image_url" value="<?php echo htmlspecialchars($campaign['image_url'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <p class="promo-admin-form__status" data-ad-platform-status role="status"></p>
            <div class="ad-platform-edit__actions">
                <button type="submit" class="promo-reward-btn promo-reward-btn--primary">수정 저장</button>
                <?php if ($can_cancel) { ?>
                <button type="button" class="promo-reward-btn" data-ad-cancel data-ad-id="<?php echo (int) $campaign['ad_id']; ?>">신청 취소</button>
                <?php } ?>
            </div>
        </form>
    </section>
    <?php } ?>

    <?php if ($can_extend) { ?>
    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">광고 연장</h2>
        <p class="promo-admin-form__hint">현재 종료일 <?php echo get_text($campaign['end_date']); ?> · 연장 시 즉시 포인트가 차감됩니다.</p>
        <form id="adPlatformExtendForm" class="promo-admin-form" method="post" action="<?php echo $proc_url; ?>">
            <input type="hidden" name="action" value="extend">
            <input type="hidden" name="ad_id" value="<?php echo (int) $campaign['ad_id']; ?>">
            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="ad_extend_days">연장 일수</label>
                    <input type="number" id="ad_extend_days" name="extra_days" min="1" max="<?php echo $slot ? max(1, (int) $slot['max_days'] - (int) $campaign['days']) : 7; ?>" value="7" data-ad-extend-days>
                </div>
                <div class="promo-admin-form__field">
                    <label>추가 포인트</label>
                    <p class="ad-platform-register__quote" data-ad-extend-quote>—</p>
                </div>
            </div>
            <p class="promo-admin-form__status" data-ad-extend-status role="status"></p>
            <button type="submit" class="promo-reward-btn promo-reward-btn--primary">연장하기</button>
        </form>
    </section>
    <?php } ?>
</main>

<script src="<?php echo htmlspecialchars($js_url, ENT_QUOTES, 'UTF-8'); ?>" defer></script>

<?php
g5_page_end();
