<?php
include_once(dirname(__FILE__).'/_init.php');

if (!$is_member) {
    alert('로그인 후 이용해 주세요.', eottae_login_url(G5_URL.'/page/eottae-ad-register.php'));
}

include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';
include_once G5_LIB_PATH.'/eottae-shop-owner.lib.php';
eottae_ad_platform_ensure_schema();

if (!eottae_ad_platform_can_manage($member)) {
    alert('사업자회원 또는 최고관리자만 광고를 등록할 수 있습니다.', G5_URL.'/page/eottae-mypage.php');
}

$slots = eottae_ad_platform_get_slots(true);
$selected_slot = isset($_GET['slot']) ? trim((string) $_GET['slot']) : '';
if ($selected_slot === '' && !empty($slots[0]['slot_code'])) {
    $selected_slot = $slots[0]['slot_code'];
}
$current_slot = eottae_ad_platform_get_slot_by_code($selected_slot);
$my_campaigns = eottae_ad_platform_member_campaigns($member['mb_id'], 20);
$my_shops = function_exists('eottae_business_shop_posts') ? eottae_business_shop_posts($member['mb_id'], 20) : array();
$target_regions = eottae_ad_platform_target_region_options();
$target_categories_by_slot = array();
foreach ($slots as $slot_row) {
    $target_categories_by_slot[$slot_row['slot_code']] = eottae_ad_platform_target_category_options($slot_row['slot_code']);
}
$expiring_ads = eottae_ad_platform_expiring_campaigns($member['mb_id'], 2);
$point = isset($member['mb_point']) ? (int) $member['mb_point'] : 0;
$proc_url = G5_URL.'/proc/eottae-ad-platform.php';
$ai_url = G5_URL.'/proc/eottae-ad-platform-ai.php';
$default_start = G5_TIME_YMD;
$js_path = G5_PATH.'/js/eottae-ad-platform.js';
$js_url = G5_JS_URL.'/eottae-ad-platform.js'.(is_file($js_path) ? '?ver='.(int) filemtime($js_path) : '');

g5_page_start('광고 등록');
?>

<main class="mypage-subpage ad-platform-register" data-ai-url="<?php echo htmlspecialchars($ai_url, ENT_QUOTES, 'UTF-8'); ?>" data-ad-category-map="<?php echo htmlspecialchars(json_encode($target_categories_by_slot, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
    <?php eottae_render_mypage_back(); ?>
    <header class="ad-platform-register__header">
        <h1 class="mypage-subpage__title">광고 등록</h1>
        <p class="ad-platform-register__lead">포인트를 사용해 원하는 위치와 기간을 선택해 광고를 집행할 수 있습니다. 자리가 없으면 대기등록됩니다.</p>
        <p class="ad-platform-register__point">보유 포인트 <strong><?php echo number_format($point); ?>P</strong> · <a href="<?php echo G5_URL; ?>/page/eottae-points.php">포인트 내역</a></p>
    </header>

    <?php if (!empty($expiring_ads)) { ?>
    <section class="ad-platform-expiry-banner" aria-label="광고 종료 임박 안내">
        <?php foreach ($expiring_ads as $exp_ad) { ?>
        <div class="ad-platform-expiry-banner__item">
            <p><strong><?php echo get_text($exp_ad['title']); ?></strong> · <?php echo get_text($exp_ad['slot_name']); ?> · 종료 <?php echo get_text($exp_ad['end_date']); ?> (<?php echo (int) $exp_ad['days_left']; ?>일 남음)</p>
            <a href="<?php echo eottae_ad_platform_edit_url((int) $exp_ad['ad_id']); ?>" class="ad-platform-expiry-banner__link">연장하기</a>
        </div>
        <?php } ?>
    </section>
    <?php } ?>

    <section class="promo-admin-panel ad-platform-register__form-panel">
        <form id="adPlatformRegisterForm" class="promo-admin-form ad-platform-register__form" method="post" action="<?php echo $proc_url; ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="apply">

            <div class="promo-admin-form__field">
                <label for="ad_slot_code">광고 위치</label>
                <select id="ad_slot_code" name="slot_code" required data-ad-platform-slot>
                    <?php foreach ($slots as $slot) { ?>
                    <option value="<?php echo get_text($slot['slot_code']); ?>"
                        data-point-per-day="<?php echo (int) $slot['point_per_day']; ?>"
                        data-min-days="<?php echo (int) $slot['min_days']; ?>"
                        data-max-days="<?php echo (int) $slot['max_days']; ?>"
                        data-requires-image="<?php echo !empty($slot['requires_image']) ? '1' : '0'; ?>"
                        <?php echo $current_slot && $current_slot['slot_code'] === $slot['slot_code'] ? 'selected' : ''; ?>>
                        <?php echo get_text($slot['slot_name']); ?> (<?php echo number_format((int) $slot['point_per_day']); ?>P/일)
                    </option>
                    <?php } ?>
                </select>
            </div>

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
                <div class="ad-platform-ai-panel__preview" data-ad-ai-preview hidden></div>
            </div>

            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="ad_start_date">시작일</label>
                    <input type="date" id="ad_start_date" name="start_date" value="<?php echo get_text($default_start); ?>" required>
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_days">집행 일수</label>
                    <input type="number" id="ad_days" name="days" min="3" max="90" value="<?php echo $current_slot ? (int) $current_slot['min_days'] : 3; ?>" required data-ad-platform-days>
                </div>
                <div class="promo-admin-form__field">
                    <label>예상 포인트</label>
                    <p class="ad-platform-register__quote" data-ad-platform-quote>—</p>
                </div>
            </div>

            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="ad_target_category">타깃 카테고리</label>
                    <select id="ad_target_category" name="target_category" data-ad-target-category>
                        <option value="">전체 (타깃 없음)</option>
                    </select>
                    <p class="promo-admin-form__hint">선택 시 해당 분류를 볼 때 우선 노출됩니다.</p>
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_target_region">타깃 지역</label>
                    <select id="ad_target_region" name="target_region">
                        <option value="">전체 지역</option>
                        <?php foreach ($target_regions as $region) { ?>
                        <option value="<?php echo get_text($region); ?>"><?php echo get_text($region); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_bid_bonus">입찰 보너스 (P)</label>
                    <input type="number" id="ad_bid_bonus" name="bid_bonus" min="0" max="100000" step="100" value="0" data-ad-bid-bonus>
                    <p class="promo-admin-form__hint">같은 위치·기간에서 노출 우선순위가 올라갑니다.</p>
                </div>
            </div>

            <div class="promo-admin-form__field">
                <label for="ad_title">광고 제목</label>
                <input type="text" id="ad_title" name="title" maxlength="120" required placeholder="예: IT Park 맛집 주말 할인 프로모션">
            </div>

            <div class="promo-admin-form__field">
                <label for="ad_description">광고 설명</label>
                <textarea id="ad_description" name="description" rows="4" maxlength="1000" required placeholder="노출될 광고 문구를 입력해 주세요."></textarea>
            </div>

            <div class="promo-admin-form__row">
                <div class="promo-admin-form__field">
                    <label for="ad_button_text">버튼 문구</label>
                    <input type="text" id="ad_button_text" name="button_text" maxlength="40" value="자세히 보기">
                </div>
                <div class="promo-admin-form__field">
                    <label for="ad_link_url">연결 URL</label>
                    <input type="url" id="ad_link_url" name="link_url" maxlength="500" placeholder="https://">
                </div>
            </div>

            <?php if (!empty($my_shops)) { ?>
            <div class="promo-admin-form__field">
                <label for="ad_shop_wr_id">연결 업체 (선택)</label>
                <select id="ad_shop_wr_id" name="shop_wr_id">
                    <option value="0">선택 안 함</option>
                    <?php foreach ($my_shops as $shop) {
                        $shop_wr_id = (int) ($shop['wr_id'] ?? 0);
                        ?>
                    <option value="<?php echo $shop_wr_id; ?>" data-shop-url="<?php echo htmlspecialchars(G5_BBS_URL.'/board.php?bo_table='.(defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop').'&wr_id='.$shop_wr_id, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo get_text($shop['wr_subject'] ?? ('#'.$shop_wr_id)); ?>
                    </option>
                    <?php } ?>
                </select>
                <input type="hidden" name="shop_bo_table" value="<?php echo defined('EOTTae_SHOP_TABLE') ? EOTTae_SHOP_TABLE : 'shop'; ?>">
            </div>
            <?php } ?>

            <div class="promo-admin-form__field">
                <label for="ad_image_file">광고 이미지</label>
                <input type="file" id="ad_image_file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif">
                <input type="hidden" name="image_url" id="ad_image_url" value="">
                <p class="promo-admin-form__hint">직접 업로드하거나 AI 이미지 생성을 사용할 수 있습니다.</p>
            </div>

            <p class="promo-admin-form__status" data-ad-platform-status role="status"></p>
            <button type="submit" class="promo-reward-btn promo-reward-btn--primary">광고 신청</button>
        </form>
    </section>

    <section class="promo-admin-panel">
        <h2 class="promo-admin-panel__title">내 광고 현황</h2>
        <?php if (empty($my_campaigns)) { ?>
        <p class="promo-admin-form__hint">등록한 광고가 없습니다.</p>
        <?php } else { ?>
        <div class="ad-platform-register__list">
            <?php foreach ($my_campaigns as $ad) {
                $can_edit = eottae_ad_platform_member_can_edit_campaign($ad);
                $can_extend = eottae_ad_platform_member_can_extend_campaign($ad);
                ?>
            <article class="ad-platform-register__item">
                <div>
                    <strong><?php echo get_text($ad['title']); ?></strong>
                    <p><?php echo get_text($ad['slot_name']); ?> · <?php echo get_text($ad['start_date']); ?> ~ <?php echo get_text($ad['end_date']); ?></p>
                    <?php if ((int) $ad['impressions'] > 0 || (int) $ad['clicks'] > 0) { ?>
                    <p class="ad-platform-register__stats">노출 <?php echo number_format((int) $ad['impressions']); ?> · 클릭 <?php echo number_format((int) $ad['clicks']); ?> · CTR <?php echo number_format((float) $ad['ctr'], 2); ?>%</p>
                    <?php } ?>
                    <?php if ($ad['target_category'] !== '' || $ad['target_region'] !== '') { ?>
                    <p class="ad-platform-register__target">타깃 <?php echo $ad['target_category'] !== '' ? get_text($ad['target_category']) : '전체'; ?><?php echo $ad['target_region'] !== '' ? ' · '.get_text($ad['target_region']) : ''; ?><?php echo (int) $ad['bid_bonus'] > 0 ? ' · 보너스 '.number_format((int) $ad['bid_bonus']).'P' : ''; ?></p>
                    <?php } ?>
                </div>
                <div class="ad-platform-register__meta">
                    <span class="ad-platform-register__status"><?php echo get_text($ad['status_label']); ?></span>
                    <span><?php echo number_format((int) $ad['total_points']); ?>P</span>
                    <?php if ((int) $ad['waitlist_order'] > 0) { ?><span>대기 <?php echo (int) $ad['waitlist_order']; ?>번</span><?php } ?>
                    <?php if ($can_edit || $can_extend) { ?>
                    <a href="<?php echo eottae_ad_platform_edit_url((int) $ad['ad_id']); ?>" class="ad-platform-register__manage-link"><?php echo $can_edit ? '수정' : '연장'; ?></a>
                    <?php } ?>
                    <?php if (in_array($ad['status'], array('active', 'expired', 'scheduled'), true)) { ?>
                    <a href="<?php echo eottae_ad_platform_report_url((int) $ad['ad_id']); ?>" class="ad-platform-register__manage-link">리포트</a>
                    <?php } ?>
                </div>
            </article>
            <?php } ?>
        </div>
        <?php } ?>
    </section>
</main>

<script src="<?php echo htmlspecialchars($js_url, ENT_QUOTES, 'UTF-8'); ?>" defer></script>

<?php
g5_page_end();
