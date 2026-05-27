<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_render_shop_spot_apply')) {
    /**
     * 업체 최우수 노출(포인트 신청) UI
     *
     * @param array<string, mixed> $write
     * @param string             $bo_table
     */
    function eottae_render_shop_spot_apply($write, $bo_table = '')
    {
        global $is_member, $member;

        if (!function_exists('eottae_shop_spot_ensure_schema')) {
            include_once G5_LIB_PATH.'/eottae-shop-spot.lib.php';
        }
        eottae_shop_spot_ensure_schema();

        $bo_table = preg_replace('/[^a-z0-9_]/', '', (string) $bo_table);
        if ($bo_table === '' || !function_exists('eottae_is_shop_board') || !eottae_is_shop_board($bo_table)) {
            return;
        }

        $wr_id = is_array($write) && !empty($write['wr_id']) ? (int) $write['wr_id'] : 0;
        if ($wr_id < 1) {
            return;
        }

        $can_manage = function_exists('eottae_shop_user_can_manage') && eottae_shop_user_can_manage($write, $bo_table);
        if (!$can_manage) {
            return;
        }

        $configs = eottae_shop_spot_get_all_config();
        $active = eottae_shop_spot_active_bookings($bo_table);
        $my_booking = eottae_shop_spot_shop_booking($bo_table, $wr_id);
        $point = ($is_member && !empty($member['mb_point'])) ? (int) $member['mb_point'] : 0;
        $proc_url = G5_URL.'/proc/eottae-shop-spot.php';
        ?>
        <section class="shop-spot-apply" id="shopSpotApply" aria-labelledby="shop-spot-apply-title"
            data-shop-spot-apply
            data-proc-url="<?php echo htmlspecialchars($proc_url, ENT_QUOTES, 'UTF-8'); ?>"
            data-bo-table="<?php echo htmlspecialchars($bo_table, ENT_QUOTES, 'UTF-8'); ?>"
            data-shop-bo-table="<?php echo htmlspecialchars($bo_table, ENT_QUOTES, 'UTF-8'); ?>"
            data-wr-id="<?php echo (int) $wr_id; ?>">
            <h3 class="shop-spot-apply__title" id="shop-spot-apply-title">최우수 업체 노출 신청</h3>
            <p class="shop-spot-apply__desc">포인트로 목록 <strong>최상단 3개 자리</strong>에 노출할 수 있습니다. 신청한 업체에는 <strong>「최우수업체」</strong> 배지가 표시됩니다.</p>
            <?php if (!$is_member) { ?>
            <p class="shop-spot-apply__notice">로그인 후 신청할 수 있습니다.</p>
            <?php } elseif (!empty($my_booking['booking_id'])) { ?>
            <p class="shop-spot-apply__active">
                현재 <strong><?php echo (int) $my_booking['spot_slot']; ?>번 자리</strong>에 노출 중입니다.
                종료: <?php echo get_text(substr((string) $my_booking['ends_at'], 0, 16)); ?>
            </p>
            <?php } else { ?>
            <p class="shop-spot-apply__point">보유 포인트: <strong><?php echo number_format($point); ?>P</strong></p>
            <ul class="shop-spot-apply__slots">
                <?php for ($slot = 1; $slot <= eottae_shop_spot_slot_count(); $slot++) {
                    $cfg = isset($configs[$slot]) ? $configs[$slot] : array();
                    $enabled = !empty($cfg['is_enabled']);
                    $pts = (int) ($cfg['points_required'] ?? 0);
                    $days = (int) ($cfg['days_duration'] ?? 0);
                    $occupied = isset($active[$slot]);
                    $occ_name = '';
                    if ($occupied) {
                        global $g5;
                        $b = $active[$slot];
                        $storage = function_exists('eottae_shop_storage_bo_table')
                            ? eottae_shop_storage_bo_table($bo_table)
                            : $bo_table;
                        $wt = $g5['write_prefix'].$storage;
                        $occ_row = sql_fetch(" select wr_subject from `{$wt}` where wr_id = '".(int) $b['shop_wr_id']."' limit 1 ");
                        $occ_name = !empty($occ_row['wr_subject']) ? get_text($occ_row['wr_subject']) : '';
                    }
                    $can_apply = $is_member && $enabled && $pts > 0 && $days > 0 && !$occupied && $point >= $pts;
                    ?>
                <li class="shop-spot-apply__slot<?php echo $occupied ? ' is-occupied' : ''; ?><?php echo !$enabled ? ' is-disabled' : ''; ?>">
                    <div class="shop-spot-apply__slot-head">
                        <strong class="shop-spot-apply__slot-num"><?php echo (int) $slot; ?>번 자리</strong>
                        <?php if ($slot === 1) { ?><span class="shop-spot-apply__slot-tag">관리자 설정</span><?php } ?>
                    </div>
                    <p class="shop-spot-apply__slot-meta">
                        필요 포인트 <strong><?php echo number_format($pts); ?>P</strong>
                        · 노출 기간 <strong><?php echo (int) $days; ?>일</strong>
                    </p>
                    <?php if ($occupied) { ?>
                    <p class="shop-spot-apply__slot-status">노출 중<?php echo $occ_name !== '' ? ': '.htmlspecialchars($occ_name, ENT_QUOTES, 'UTF-8') : ''; ?></p>
                    <?php } elseif (!$enabled) { ?>
                    <p class="shop-spot-apply__slot-status">신청 중지</p>
                    <?php } else { ?>
                    <button type="button" class="btn btn--primary shop-spot-apply__btn"
                        data-shop-spot-apply-btn="<?php echo (int) $slot; ?>"
                        <?php echo $can_apply ? '' : 'disabled'; ?>>
                        <?php echo (int) $slot; ?>번 자리 신청
                    </button>
                    <?php if ($is_member && $point < $pts) { ?>
                    <p class="shop-spot-apply__slot-hint">포인트가 <?php echo number_format($pts - $point); ?>P 부족합니다.</p>
                    <?php } ?>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
            <p class="shop-spot-apply__status" data-shop-spot-status role="status" aria-live="polite"></p>
            <?php } ?>
        </section>
        <?php
    }
}
