<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_adroom_render_coupon_picker')) {
    /**
     * @param array<int, array<string, mixed>> $coupons
     * @param int $selected_cp_id
     */
    function eottae_adroom_render_coupon_picker(array $coupons, $selected_cp_id = 0)
    {
        $selected_cp_id = (int) $selected_cp_id;
        $manage_url = G5_URL.'/page/eottae-business-coupons.php';

        ob_start();
        ?>
        <section class="adroom-coupon-picker" id="adroom-coupon-picker">
            <h3 class="adroom-coupon-picker__title">연동 쿠폰 (선택)</h3>
            <p class="adroom-coupon-picker__desc">광고와 함께 노출할 쿠폰을 선택하면 방문 회원이 <strong>쿠폰 받기</strong>로 다운로드할 수 있습니다.</p>

            <input type="hidden" name="eottae_adroom_cp_id" id="eottae_adroom_cp_id" value="<?php echo $selected_cp_id > 0 ? (int) $selected_cp_id : ''; ?>">
            <input type="hidden" name="wr_4" value="<?php echo $selected_cp_id > 0 ? (int) $selected_cp_id : ''; ?>">

            <div class="adroom-coupon-picker__choices">
                <label class="adroom-coupon-picker__choice">
                    <input type="radio" name="adroom_coupon_pick" value=""<?php echo $selected_cp_id < 1 ? ' checked' : ''; ?>>
                    <span>쿠폰 없음</span>
                </label>

                <?php foreach ($coupons as $coupon) {
                    $cp_id = (int) ($coupon['cp_id'] ?? 0);
                    if ($cp_id < 1) {
                        continue;
                    }
                    $remain = '';
                    $max = (int) ($coupon['max_issue'] ?? 0);
                    $issued = (int) ($coupon['issued_count'] ?? 0);
                    if ($max > 0) {
                        $remain = ' · 남은 '.number_format(max(0, $max - $issued)).'장';
                    }
                    ?>
                <label class="adroom-coupon-picker__choice">
                    <input type="radio" name="adroom_coupon_pick" value="<?php echo $cp_id; ?>"<?php echo $selected_cp_id === $cp_id ? ' checked' : ''; ?>>
                    <span class="adroom-coupon-picker__choice-body">
                        <strong><?php echo get_text($coupon['title'] ?? ''); ?></strong>
                        <span class="adroom-coupon-picker__benefit"><?php echo get_text($coupon['benefit'] ?? ''); ?><?php echo $remain; ?></span>
                    </span>
                </label>
                <?php } ?>
            </div>

            <?php if (empty($coupons)) { ?>
            <p class="adroom-coupon-picker__empty">등록된 쿠폰이 없습니다. 쿠폰을 만든 뒤 이 광고에 연결하세요.</p>
            <?php } ?>

            <p class="adroom-coupon-picker__manage">
                <a href="<?php echo get_text($manage_url); ?>" class="adroom-btn adroom-btn--outline" target="_blank" rel="noopener noreferrer">쿠폰 만들기 / 관리</a>
            </p>
        </section>
        <?php

        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_adroom_render_coupon_claim_block')) {
    function eottae_adroom_render_coupon_claim_block($wr_id, $member_mb_id = '')
    {
        $wr_id = (int) $wr_id;
        if ($wr_id < 1 || !function_exists('eottae_adroom_get_linked_coupon')) {
            return '';
        }

        $coupon = eottae_adroom_get_linked_coupon($wr_id);
        if (empty($coupon['cp_id'])) {
            return '';
        }

        $cp_id = (int) $coupon['cp_id'];
        $has_coupon = $member_mb_id !== '' && eottae_adroom_member_has_active_coupon($cp_id, $member_mb_id);
        $login_url = function_exists('eottae_login_url')
            ? eottae_login_url(function_exists('eottae_current_url') ? eottae_current_url() : '')
            : G5_BBS_URL.'/login.php';
        $coupons_url = G5_URL.'/page/eottae-coupons.php';
        $proc_url = G5_URL.'/proc/eottae-adroom-coupon.php';

        ob_start();
        ?>
        <aside class="adroom-view__coupon" id="adroom-coupon-claim" aria-label="광고 쿠폰">
            <div class="adroom-view__coupon-inner">
                <p class="adroom-view__coupon-eyebrow">Coupon</p>
                <h2 class="adroom-view__coupon-title"><?php echo get_text($coupon['cp_title'] ?? ''); ?></h2>
                <?php if (!empty($coupon['benefit_line'])) { ?>
                <p class="adroom-view__coupon-benefit"><?php echo get_text($coupon['benefit_line']); ?></p>
                <?php } ?>
                <?php if (!empty($coupon['cp_desc'])) { ?>
                <p class="adroom-view__coupon-desc"><?php echo get_text($coupon['cp_desc']); ?></p>
                <?php } ?>

                <?php if ($member_mb_id === '') { ?>
                <a href="<?php echo get_text($login_url); ?>" class="adroom-btn adroom-btn--primary adroom-view__coupon-btn">로그인 후 쿠폰 받기</a>
                <?php } elseif ($has_coupon) { ?>
                <a href="<?php echo get_text($coupons_url); ?>" class="adroom-btn adroom-btn--primary adroom-view__coupon-btn">내 쿠폰함에서 보기</a>
                <p class="adroom-view__coupon-note">이미 받은 쿠폰입니다.</p>
                <?php } else { ?>
                <button type="button"
                    class="adroom-btn adroom-btn--primary adroom-view__coupon-btn"
                    id="adroom-coupon-claim-btn"
                    data-adroom-coupon-claim
                    data-wr-id="<?php echo $wr_id; ?>"
                    data-proc-url="<?php echo get_text($proc_url); ?>">쿠폰 받기</button>
                <p class="adroom-view__coupon-note" id="adroom-coupon-claim-status" aria-live="polite"></p>
                <?php } ?>
            </div>
        </aside>
        <?php

        return (string) ob_get_clean();
    }
}
