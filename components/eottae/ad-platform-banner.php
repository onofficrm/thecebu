<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('eottae_ad_platform_render_banner')) {
    function eottae_ad_platform_render_banner($slot_code, array $opts = array())
    {
        if (!function_exists('eottae_ad_platform_get_active')) {
            include_once G5_LIB_PATH.'/eottae-ad-platform.lib.php';
        }

        eottae_ad_platform_ensure_schema();
        $slot = eottae_ad_platform_get_slot_by_code($slot_code);
        if (!$slot || empty($slot['is_active'])) {
            return '';
        }

        $limit = !empty($slot['is_premium']) ? (int) $slot['max_active_ads'] : 1;
        $context = isset($opts['context']) && is_array($opts['context']) ? $opts['context'] : array();
        $ads = eottae_ad_platform_get_active($slot_code, $limit, $context);
        $show_placeholder = !isset($opts['show_placeholder']) || !empty($opts['show_placeholder']);
        $register_url = eottae_ad_platform_register_url($slot_code);

        ob_start();
        ?>
        <div class="eottae-ad-platform" data-ad-slot="<?php echo htmlspecialchars($slot_code, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if (!empty($ads)) {
                foreach ($ads as $ad) {
                    eottae_ad_platform_record_impression((int) $ad['ad_id']);
                    $click_url = eottae_ad_platform_click_url((int) $ad['ad_id']);
                    $tone = !empty($slot['is_premium']) ? 'premium' : 'default';
                    ?>
            <article class="eottae-ad-platform__card eottae-ad-platform__card--<?php echo $tone; ?><?php echo $ad['image_url'] !== '' ? ' eottae-ad-platform__card--image' : ''; ?>"<?php echo $ad['image_url'] !== '' ? ' style="background-image:url('.htmlspecialchars($ad['image_url'], ENT_QUOTES, 'UTF-8').')"' : ''; ?>>
                <span class="eottae-ad-platform__badge">AD</span>
                <div class="eottae-ad-platform__body">
                    <strong class="eottae-ad-platform__title"><?php echo htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                    <p class="eottae-ad-platform__desc"><?php echo nl2br(htmlspecialchars($ad['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php if ($click_url !== '' || $ad['link_url'] !== '') { ?>
                    <a href="<?php echo htmlspecialchars($click_url, ENT_QUOTES, 'UTF-8'); ?>" class="eottae-ad-platform__btn" target="_blank" rel="noopener noreferrer sponsored">
                        <?php echo htmlspecialchars($ad['button_text'] !== '' ? $ad['button_text'] : '자세히 보기', ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <?php } ?>
                </div>
            </article>
                    <?php
                }
            } elseif ($show_placeholder) { ?>
            <aside class="eottae-ad-platform__placeholder" aria-label="광고 안내">
                <span class="eottae-ad-platform__badge">AD</span>
                <div class="eottae-ad-platform__body">
                    <strong class="eottae-ad-platform__title">사장님, 가게 홍보가 필요하신가요?</strong>
                    <p class="eottae-ad-platform__desc">업체 등록 후 관리자 승인 시 이 자리에 광고가 노출됩니다.</p>
                    <a href="<?php echo htmlspecialchars($register_url, ENT_QUOTES, 'UTF-8'); ?>" class="eottae-ad-platform__btn eottae-ad-platform__btn--ghost">무료 업소등록</a>
                </div>
            </aside>
            <?php } ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}

if (!function_exists('eottae_ad_platform_render_for_board')) {
    function eottae_ad_platform_render_for_board($bo_table, array $opts = array())
    {
        $slot_code = eottae_ad_platform_slot_code_for_board($bo_table);
        if ($slot_code === '') {
            return '';
        }

        if (!isset($opts['context'])) {
            global $sca;
            $region = isset($_GET['region']) ? trim((string) $_GET['region']) : '';
            if ($region === '' && isset($GLOBALS['stx']) && isset($GLOBALS['sfl']) && $GLOBALS['sfl'] === 'wr_1' && trim((string) $GLOBALS['stx']) !== '') {
                $region = trim((string) $GLOBALS['stx']);
            }
            $opts['context'] = array(
                'category' => isset($sca) ? trim((string) $sca) : '',
                'region'   => $region,
            );
        }

        return eottae_ad_platform_render_banner($slot_code, $opts);
    }
}
